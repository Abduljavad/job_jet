<?php

namespace App\Http\Controllers;

use App\Http\Requests\RazorPayOrderCreateRequest;
use App\Http\Requests\RazorPayVerifyRequest;
use App\Models\RazorPayTransaction;
use App\Models\Subscription;
use App\Models\User;
use App\Models\UserSubscription;
use Carbon\Carbon;
use Razorpay\Api\Api;

class RazorPayPaymentController extends Controller
{
    public $razorPayService;

    public $razorPayKey;

    public $razorPayKeySecret;

    public $currency;

    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->razorPayKey = env('RAZORPAY_KEY_ID', 'rzp_live_Krw5ZVvhAlE3Hs');
        $this->razorPayKeySecret = env('RAZORPAY_KEY_SECRET', 'RCGjItvkR5bibdnFY46w6AzT');
        $this->razorPayService = new Api($this->razorPayKey, $this->razorPayKeySecret);
        $this->currency = env('RAZOR_PAY_CURRENCY', 'INR');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function createOrderId(RazorPayOrderCreateRequest $request)
    {
        $subscription = Subscription::where('is_trial', false)->findOrFail($request->subscription_id);

        $response = $this->razorPayService->order->create([
            'amount' => $this->convertIntoPaisa($subscription->price),
            'currency' => $subscription->currency,
        ]);

        if (isset($response['error'])) {
            return response()->json($response, 400);
        }

        RazorPayTransaction::create([
            'user_id' => auth()->user()->id,
            'payment_order_id' => $response->id,
            'subscription_id' => $subscription->id,
            'total_amount' => $subscription->price,
            'status' => 'created',
        ]);

        return response()->json([
            'status' => 'SUCCESS',
            'msg' => 'Order generated successfully.',
            'payment_gateway_order_id' => $response->id,
        ], 200);
    }

    public function convertIntoPaisa($amount)
    {
        return $amount * 100;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function verify(RazorPayVerifyRequest $request)
    {
        $razorPayTransaction = RazorPayTransaction::where('payment_order_id', $request->payment_order_id)
            ->where('user_id', auth()->user()->id)->firstOrFail();

        if ($razorPayTransaction->status == 'completed') {
            return response()->json(['error' => 'Payment Already Completed']);
        }

        if ($razorPayTransaction->status != 'created') {
            return response()->json(['message' => 'Payment Failed/Invalid']);
        }
        $isVerified = $this->verifySignature($request->payment_order_id, $request->payment_id, $request->payment_signature, $this->razorPayKeySecret);

        if (! $isVerified) {
            return $this->errorResponse('Payment Failed');
        }

        $startDate = $this->createStartDate($razorPayTransaction->user);
        $endDate = $this->createEndDate($startDate, $razorPayTransaction->subscription);

        UserSubscription::create([
            'user_id' => $razorPayTransaction->user_id,
            'subscription_id' => $razorPayTransaction->subscription_id,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'is_expired' => null,
        ]);

        $razorPayTransaction->update([
            'payment_id' => $request->payment_id,
            'payment_signature' => $request->payment_signature,
            'status' => 'completed',
        ]);

        return $this->successResponse('subscription created successfully');
    }

    private function createStartDate(User $user)
    {
        $activeSubscription = UserSubscription::where('user_id', $user->id)->where('end_date', '>', now())->latest()->first();
        if ($activeSubscription) {
            return Carbon::parse($activeSubscription->end_date)->addDay()->startOfDay();
        }

        return now();
    }

    private function createEndDate($startDate, Subscription $subscription)
    {
        return Carbon::parse($startDate)->addMonths($subscription->interval_count)->endOfDay();
    }

    private function verifySignature($paymentOrderId, $paymentId, $paymentSignature, $secret)
    {
        $generated_signature = hash_hmac('sha256', "$paymentOrderId|$paymentId", $secret);

        if ($generated_signature == $paymentSignature) {
            return true;
        }

        return false;
    }
}
