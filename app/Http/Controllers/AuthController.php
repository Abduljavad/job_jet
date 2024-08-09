<?php

namespace App\Http\Controllers;

use App\Http\Enums\TokenAbility;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\VerifyOtpRequest;
use App\Http\Services\OtpServices;
use App\Http\Services\TwilioService;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\UnauthorizedException;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

class AuthController extends Controller
{
    public $otpServices;

    public $twilioServices;

    public function __construct(OtpServices $otpServices, TwilioService $twilioService)
    {
        $this->middleware(['auth:sanctum', 'ability:'.TokenAbility::ACCESS_API->value])->except(['superAdminlogin', 'sendOtp', 'refreshToken', 'verify', 'forgotPassword']);
        $this->middleware(['auth:sanctum', 'ability:'.TokenAbility::ISSUE_ACCESS_TOKEN->value])->only('refreshToken');
        $this->otpServices = $otpServices;
        $this->twilioServices = $twilioService;
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

        $user = $this->_getOrCreateUser($mobileNumber);

        //checking whether the user is admin
        if ($user->is_admin) {
            $this->otpServices->updateAdminExpireTime($user);
        } else {
            $this->twilioServices->sendVerificationToken($mobileNumber);
        }

        return $this->successResponse('otp send successfully ');
    }

    private function _getOrCreateUser($mobileNumber)
    {
        $defaultPassword = uniqid();
        $user = User::firstOrCreate(['mobile' => $mobileNumber], ['password' => $defaultPassword]);

        if (! $user->hasRole('user')) {
            $userRole = Role::findByName('user');
            $user->assignRole($userRole);
        }

        return $user;
    }

    public function verify(VerifyOtpRequest $verifyOtpRequest)
    {
        $mobileNumber = $verifyOtpRequest->mobile;
        $otp = $verifyOtpRequest->otp;

        $user = User::where('mobile', $mobileNumber)->first();
        if (! $user) {
            return $this->errorResponse('Invalid User', 404);
        }

        if ($user->is_admin) {
            $isVerified = $this->otpServices->verify($user, $otp);
        } else {
            $isVerified = $this->twilioServices->checkVerificationToken($mobileNumber, $otp);
        }

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

    public function forgotPassword(ForgotPasswordRequest $forgotPasswordRequest)
    {
        $mobileNumber = $forgotPasswordRequest->mobile;
        $code = $forgotPasswordRequest->otp;
        $newPassword = $forgotPasswordRequest->password;
        $user = User::where('mobile', $mobileNumber)->first();

        if (! $user || ! $user->hasRole('super_admin')) {
            return $this->errorResponse('Invalid User', 404);
        }

        $isVerified = $this->twilioServices->checkVerificationToken($mobileNumber, $code);

        if (! $isVerified) {
            return $this->errorResponse('Invalid OTP', 400);
        }

        $user->update(['password' => $newPassword]);

        return $this->successResponse('password succesfully changed');
    }
}
