<?php

namespace App\Http\Services;

use App\Http\Traits\ResponseTraits;
use App\Models\User;
use App\Notifications\SendOtpNotification;
use Illuminate\Support\Facades\Hash;

class OtpServices
{
    use ResponseTraits;

    public function generate()
    {
        $otp = rand(1000, 9999);

        return $otp;
    }

    public function Store(User $user, $otp)
    {
        $hashedOtp = bcrypt($otp);
        $expiryTime = now()->addMinutes(10);

        $user->update([
            'otp' => $hashedOtp,
            'otp_expire_at' => $expiryTime,
        ]);

        return true;
    }

    public function verify(User $user, $otp)
    {
        if (! $user || ! $otp) {
            return false;
        }

        if (! Hash::check($otp, $user->otp) || (now() > $user->otp_expire_at)) {
            return false;
        }

        return true;
    }

    public function send(User $user, $otp)
    {
        $user->notify(new SendOtpNotification($otp));
    }
}
