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

    /**
     * The below sms or otp storing code is not used  for admins or  for regualar users in current version
     * Reason : In Old version we used to verify the user own our own but in newer version we use twilio verify feature
     */
    public function Store($mobileNumber, $otp)
    {
        $expiryTime = now()->addMinutes(10);

        $user = User::firstOrCreate(['mobile' => $mobileNumber], ['password' => $otp]);

        //bypassing admin users from creating otp
        if ($user->is_admin) {
            $user->update([
                'otp_expire_at' => $expiryTime,
            ]);
        } else {
            $user->update([
                'password' => $otp,
                'otp' => $otp,
                'otp_expire_at' => $expiryTime,
            ]);
        }

        if (! $user->hasRole('user')) {
            $userRole = Role::findByName('user');
            $user->assignRole($userRole);
        }

        return $user;
    }

    public function updateAdminExpireTime(User $user)
    {
        $expiryTime = now()->addMinutes(10);

        $user->update([
            'otp_expire_at' => $expiryTime,
        ]);
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

    /**
     * The below sms or otp sending code is deprecated or not using in current version
     * Reason : In Old version we used to verify the user own our own but in newer version we use twilio verify feature
     */
    public function send(User $user, $otp)
    {
        if (! $user->is_admin) {
            $user->notify(new SendSmsNotification($otp));
        } else {
            $adminOtp = $user->otp;
            $user->notify(new SendSmsNotification($adminOtp));
        }
    }
}
