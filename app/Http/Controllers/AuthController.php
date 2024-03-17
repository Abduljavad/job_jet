<?php

namespace App\Http\Controllers;

use App\Http\Enums\TokenAbility;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\VerifyOtpRequest;
use App\Http\Services\OtpServices;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\UnauthorizedException;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public $otpServices;

    public function __construct(OtpServices $otpServices)
    {
        $this->middleware(['auth:sanctum', 'ability:'.TokenAbility::ACCESS_API->value])->except(['superAdminlogin', 'sendOtp', 'refreshToken', 'verify']);
        $this->middleware(['auth:sanctum', 'ability:'.TokenAbility::ISSUE_ACCESS_TOKEN->value])->only('refreshToken');
        $this->otpServices = $otpServices;
    }

    public function superAdminlogin(LoginRequest $request)
    {
        $user = User::where('email', $request->email)->whereRelation('roles', 'name', 'super_admin')->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        return $this->getAccessTokenAndRefreshToken($user);
    }

    public function sendOtp(RegisterRequest $registerRequest)
    {
        $mobileNumber = $registerRequest->mobile;
        $otp = $this->otpServices->generate();
        $user = $this->otpServices->Store($mobileNumber, $otp);
        $this->otpServices->send($user, $otp);

        return $this->successResponse('otp send successfully ');
    }

    public function verify(VerifyOtpRequest $verifyOtpRequest)
    {
        $user = User::where('mobile', $verifyOtpRequest->mobile)->first();
        if (! $user) {
            return $this->errorResponse('Invalid User', 404);
        }

        $isVerified = $this->otpServices->verify($user, $verifyOtpRequest->otp);
        if (! $isVerified) {
            return $this->errorResponse('Invalid OTP', 400);
        }

        return $this->getAccessTokenAndRefreshToken($user);
    }

    public function userProfile()
    {
        return User::with(['favourites', 'profile', 'user_subscriptions.subscription'])->withCount('user_subscriptions')->findOrFail(auth()->user()->id);
    }

    public function refreshToken()
    {
        return $this->getAccessTokenAndRefreshToken(auth()->user());
    }

    public function logout()
    {
        auth()->user()->currentAccessToken()->delete();

        return $this->successResponse('user logged out successfully');
    }

    public function getAccessTokenAndRefreshToken($user)
    {
        $accessToken = $user->createToken('access_token',
            [TokenAbility::ACCESS_API->value],
            Carbon::now()->addMinutes(config('sanctum.ac_expiration')));

        $refreshToken = $user->createToken('refresh_token',
            [TokenAbility::ISSUE_ACCESS_TOKEN->value],
            Carbon::now()->addMinutes(config('sanctum.rt_expiration')));

        return [
            'type' => 'Bearer',
            'token' => $accessToken->plainTextToken,
            'refresh_token' => $refreshToken->plainTextToken,
            'expire_at' => config('sanctum.ac_expiration'),
        ];
    }

    public function changePassword(ChangePasswordRequest $request)
    {
        $user = User::with('roles')->findOrFail(auth()->user()->id);

        if (! $user->hasRole('super_admin')) {
            throw new UnauthorizedException('Permission Denied');
        }
        if (! Hash::check($request->old_password, $user->password)) {
            throw ValidationException::withMessages([
                'old_password' => ['old passwod is incorrect.'],
            ]);
        }

        $user->update(['password' => $request->new_password]);

        return $this->successResponse('password changed successfully');
    }
}
