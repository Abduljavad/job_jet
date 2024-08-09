<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\FileUploadController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\RazorPayPaymentController;
use App\Http\Controllers\StripePaymentController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::prefix('auth')->controller(AuthController::class)->group(function () {
    Route::post('/login', 'superAdminlogin');
    Route::post('/register', 'register');
    Route::get('/profile', 'userProfile');
    Route::post('refresh-token', 'refreshToken');
    Route::post('logout', 'logout');
    Route::post('send-otp', 'sendOtp');
    Route::post('verify-otp', 'verify');
    Route::post('change-password', 'changePassword');
    Route::post('admin/forgot-password', 'forgotPassword');
});

Route::apiResource('subscriptions', SubscriptionController::class);
Route::apiResource('categories', CategoryController::class);

Route::get('jobs/subscribed', [JobController::class, 'subscriptionBasedJobList']);
Route::apiResource('jobs', JobController::class);

Route::apiResource('locations', LocationController::class);

Route::controller(StripePaymentController::class)
    ->prefix('checkout')->group(function () {
        Route::post('stripe/create-payment-intent', 'createPaymentIntent');
        Route::post('stripe/subscribe', 'subscribe');
        Route::post('subscribe/trial', 'applyTrialSubscription');
    });

Route::controller(RazorPayPaymentController::class)
    ->prefix('checkout/razorpay')->group(function () {
        Route::post('order/generate', 'createOrderId');
        Route::post('order/verify', 'verify');
    });

Route::controller(UserController::class)->prefix('users')
    ->group(function () {
        Route::post('/favourites/attach', 'attachFav');
        Route::post('/favourites/detach', 'detachFav');
        Route::post('profile/update', 'profile');
    });

Route::post('file-upload', [FileUploadController::class, 'store']);
