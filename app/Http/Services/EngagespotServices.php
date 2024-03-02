<?php

namespace App\Http\Services;

use App\Models\User;
use ErrorException;
use Exception;
use Illuminate\Support\Facades\Http;

class EngagespotServices
{
    public function sendNotificationWithTemplate($templateID, $recipientId, $data = null, $icon = null)
    {
        $user = User::find($recipientId);
        $mobileNumber = $user->mobile ? $user->mobile : null;

        $notifcationContent = [
            'notification' => [
                'templateId' => $templateID,
                'icon' => $icon,
            ],
            'data' => $data ?? null,
            'recipients' => [strval($recipientId)],
        ];

        $this->createOrUpdateUser($recipientId, $user->name, $user->email, $mobileNumber);

        return $this->engagespotApi($notifcationContent);
    }

    public function createOrUpdateUser($identifier, $name, $email, $mobileNumber)
    {
        try {
            $spec = [
                'name' => $name ?? null,
                'email' => $email ?? null,
                'phoneNumber' => $mobileNumber ?? null,
            ];
            Http::withHeaders([
                'X-ENGAGESPOT-API-KEY' => env('ENGAGESPOT_API_KEY', 'grl24pcq96vvwn5wx7gp5o'),
                'X-ENGAGESPOT-API-SECRET' => env('ENGAGESPOT_API_SECRET', '3am9m1gs62sa91nhamqdbami0d8jdd2e752fe40g8a81434'),
            ])->put('https://api.engagespot.co/v3/users/'.$identifier, $spec);
        } catch (Exception $e) {

        } catch (ErrorException $e) {

        }
    }

    private function engagespotApi($notifcationSpec)
    {
        $response = Http::withHeaders([
            'X-ENGAGESPOT-API-KEY' => env('ENGAGESPOT_API_KEY', 'grl24pcq96vvwn5wx7gp5o'),
            'X-ENGAGESPOT-API-SECRET' => env('ENGAGESPOT_API_SECRET', '3am9m1gs62sa91nhamqdbami0d8jdd2e752fe40g8a81434'),
        ])->post('https://api.engagespot.co/v3/notifications', $notifcationSpec);

        return $response;
    }
}
