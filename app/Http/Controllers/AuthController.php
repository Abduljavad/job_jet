<?php

namespace App\Http\Controllers;

use App\Http\Enums\TokenAbility;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:sanctum', 'ability:'.TokenAbility::ACCESS_API->value])->except(['superAdminlogin', 'register', 'refreshToken']);
        $this->middleware(['auth:sanctum', 'ability:'.TokenAbility::ISSUE_ACCESS_TOKEN->value])->only('refreshToken');
    }

    public function superAdminlogin(LoginRequest $request)
    {
        $user = User::where('email', $request->email)->whereRelation('roles', 'name', 'user')->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        return $this->getAccessTokenAndRefreshToken($user);
    }

    public function register(RegisterRequest $registerRequest)
    {
        User::create($registerRequest->validated());

        return $this->successResponse('user registered successfully');
    }

    public function userProfile()
    {
        return auth()->user();
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
}
