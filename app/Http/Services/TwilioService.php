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

    }

    public function sendVerificationToken(string $mobileNumber)
    {
        $twilio = new Client($this->sid, $this->token);

        $serviceSid = env('TWILIO_SERVICE_SID', '');
        $verification = $twilio->verify->v2
            ->services($serviceSid)
            ->verifications->create(
                $mobileNumber,
                'sms'
            );

        return $verification->status;
    }

    public function checkVerificationToken(string $mobileNumber, string $code)
    {
        try {
            $twilio = new Client($this->sid, $this->token);
            $serviceSid = env('TWILIO_SERVICE_SID', '');
            
            $verification_check = $twilio->verify->v2
                ->services($serviceSid)
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
