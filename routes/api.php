<?php

use App\Http\Controllers\AuthController;
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
});
