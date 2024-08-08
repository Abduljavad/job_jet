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
        $this->sid = env('TWILIO_ACCOUNT_SID', 'ACb8a37a9bef044c9a546ab5ae880ff6bf');
        $this->token = env('TWILIO_AUTH_TOKEN', '18c80de45fe2ec9b0c761d80699cbe80');
        $this->serviceSid = env('TWILIO_VERIFY_SERVICE_SID', 'VA73caf35b9933d1319bcffe2a07daec4d');

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
