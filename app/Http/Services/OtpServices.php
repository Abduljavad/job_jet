<?php

namespace App\Http\Services;

use App\Http\Traits\ResponseTraits;
use App\Models\User;
use App\Notifications\SendSmsNotification;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class OtpServices
{
    use ResponseTraits;

    public function generate()
    {
        $otp = rand(1000, 9999);

        return $otp;
    }

    public function Store($mobileNumber, $otp)
    {
        $expiryTime = now()->addMinutes(10);

        $user = User::updateOrCreate(
            [
                'mobile' => $mobileNumber,
                'is_admin' => false,
            ],
            [
                'password' => $otp,
                'otp' => $otp,
                'otp_expire_at' => $expiryTime,
            ]);

        if (! $user->hasRole('user')) {
            $userRole = Role::findByName('user');
            $user->assignRole($userRole);
        }

        return $user;
    }

    public function verify(User $user, $otp)
    {
        if (! $user || ! $otp) {
            return false;
        }
        if (! Hash::check($otp, $user->password) || (now() > $user->otp_expire_at)) {
            return false;
        }

        return true;
    }

    public function send(User $user, $otp)
    {
        $user->notify(new SendSmsNotification($otp));
    }
}
