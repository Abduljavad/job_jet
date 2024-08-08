<?php

namespace App\Http\Services;

use Twilio\Rest\Client;

class TwilioService
{
    private $sid;

    private $token;

    public $twilio;


    public function __construct()
    {
        $this->sid = env('TWILIO_ACCOUNT_SID', '');
        $this->token = env('TWILIO_AUTH_TOKEN', '');

        $this->twilio = new Client($this->sid, $this->token);
    }

    public function sendVerificationToken(string $mobileNumber)
    {
        $verification = $this->twilio->verify->v2
            ->services('VA73caf35b9933d1319bcffe2a07daec4d')
            ->verifications->create(
                $mobileNumber,
                'sms'
            );

        return $verification->status;
    }

    public function checkVerificationToken(string $mobileNumber, string $code)
    {
        try {
            $verification_check = $this->twilio->verify->v2
                ->services('VA73caf35b9933d1319bcffe2a07daec4d')
                ->verificationChecks->create([
                    'to' => $mobileNumber,
                    'code' => $code,
                ]);
        } catch (\Exception $e) {
            return false;
        }

        return $verification_check->status == 'approved' ? true : false;
    }
}
