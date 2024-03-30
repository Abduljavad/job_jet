<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreatePaymentIntentRequest;
use App\Http\Requests\SubscribeRequest;
use App\Models\StripeTransaction;
use App\Models\Subscription;
use App\Models\User;
use App\Models\UserSubscription;
use Carbon\Carbon;

use function PHPUnit\Framework\isNull;

class StripePaymentController extends Controller
{
    public $stripe;

    public function __construct()
    {
        $this->stripe = $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET', 'default'));
        $this->middleware(['auth:sanctum']);
    }

    public function createPaymentIntent(CreatePaymentIntentRequest $createPaymentIntentRequest)
    {
        $subscription = Subscription::where('is_trial', false)->findOrFail($createPaymentIntentRequest->subscription_id);

        $paymentIntent = $this->stripe->paymentIntents->create([
            'amount' => $this->convertToStripeAmount($subscription->price),
            'currency' => 'inr',
            'customer' => $this->getstripeCustomerId(auth()->user()),
            'automatic_payment_methods' => [
                'enabled' => 'true',
            ],
        ]);

        StripeTransaction::create([
            'user_id' => auth()->user()->id,
            'payment_intent_id' => $paymentIntent->id,
            'subscription_id' => $subscription->id,
            'total_amount' => $subscription->price,
            'status' => 'created',
        ]);

        return $this->successResponse([
            'payment_intent' => $paymentIntent->id,
            'client_secret' => $paymentIntent->client_secret,
        ]);
    }

    public function getPaymentIntent($paymentIntentId)
    {
        return $this->stripe->paymentIntents->retrieve(
            $paymentIntentId,
            []
        );
    }

    public function subscribe(SubscribeRequest $request)
    {
        $paymentIntent = $this->getPaymentIntent($request->payment_intent_id);
        $paymentIntent->status = 'succeeded';
        switch ($paymentIntent->status) {
            case 'requires_payment_method':
                return response()->json([
                    'message' => 'required_payment_method',
                ], 400);
            case 'processing':
                return response()->json(['error' => 'Order Processing'], 400);
            case 'canceled':
                return response()->json(['error' => 'Order canceled'], 400);
            case 'succeeded':

                $stripeTransaction = StripeTransaction::with(['subscription', 'user'])
                    ->where('payment_intent_id', $paymentIntent->id)->firstOrFail();
                if ($stripeTransaction->status != 'created') {
                    return $this->errorResponse('subscription already created');
                }

                $startDate = $this->createStartDate($stripeTransaction->user);
                $endDate = $this->createEndDate($startDate, $stripeTransaction->subscription);

                UserSubscription::create([
                    'user_id' => $stripeTransaction->user_id,
                    'subscription_id' => $stripeTransaction->subscription_id,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'is_expired' => null,
                ]);

                $stripeTransaction->update([
                    'status' => $paymentIntent->status,
                ]);

                return $this->successResponse('subscription created');

            default:
                return response()->json([
                    'error' => 'Invalid PaymentIntent',
                ], 400);
        }
    }

    public function createStartDate(User $user)
    {
        $activeSubscription = UserSubscription::where('user_id', $user->id)->where('end_date', '>', now())->latest()->first();
        if ($activeSubscription) {
            return Carbon::parse($activeSubscription->end_date)->addDay()->startOfDay();
        }

        return now();
    }

    public function createEndDate($startDate, Subscription $subscription)
    {
        return Carbon::parse($startDate)->addMonths($subscription->interval_count)->endOfDay();
    }

    public function getstripeCustomerId($user)
    {
        return isNull($user->stripe_customer_id) ? $this->createCustomer($user) : $user->stripe_customer_id;
    }

    public function createCustomer($user)
    {
        $customer = $this->stripe->customers->create([
            'name' => $user->full_name,
            'phone' => $user->mobile,
        ]);

        return $customer->id;
    }

    public function convertToStripeAmount($price)
    {
        return $price * 100;
    }

    public function applyTrialSubscription()
    {
        $subscriptionsExist = auth()->user()->user_subscriptions()->exists();
        if ($subscriptionsExist) {
            return $this->errorResponse('Trial Subscription already applied');
        }
        $trialSubscription = Subscription::where('is_trial', true)->firstOrFail();

        $startDate = now();
        $endDate = Carbon::parse($startDate)->addDays(2)->endOfDay();

        UserSubscription::create([
            'user_id' => auth()->user()->id,
            'subscription_id' => $trialSubscription->id,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'is_expired' => null,
        ]);

        return $this->successResponse('Trial Subscription created');

    }
}
