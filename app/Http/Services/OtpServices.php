<?php

namespace App\Http\Services;

use App\Http\Traits\ResponseTraits;
use App\Models\User;
use App\Notifications\SendOtpNotification;
use App\Notifications\SendSmsNotification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
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
            ['mobile' => $mobileNumber],
            [
                'password' => $otp,
                'otp' => $otp,
                'otp_expire_at' => $expiryTime,
            ]);

        if(!$user->hasRole('user')){
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
        Log::info(['otp' => Hash::check($otp, $user->password) , $otp]);
        Log::info(['expire' => (now() > $user->otp_expire_at),'expire_at' => $user->otp_expire_at, 'current_time' => now()->toTimeString() ]);
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
