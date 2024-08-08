<?php

namespace App\Http\Services;

use Twilio\Rest\Client;

class TwilioService
{
    private $sid;

    private $token;

    public $twilio;

    public $serviceSid;

    public function __construct()
    {
        $this->sid = config('twilio.account_sid');
        $this->token = config('twilio.auth_token');
        $this->serviceSid = config('twilio.service_sid');

        $this->twilio = new Client($this->sid, $this->token);
    }

    public function sendVerificationToken(string $mobileNumber)
    {
        $verification = $this->twilio->verify->v2
            ->services($this->serviceSid)
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
                ->services($this->serviceSid)
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
