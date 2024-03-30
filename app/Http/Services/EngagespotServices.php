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
                'X-ENGAGESPOT-API-KEY' => env('ENGAGESPOT_API_KEY', 'n76ud46qb5yq8zhlg0l9'),
                'X-ENGAGESPOT-API-SECRET' => env('ENGAGESPOT_API_SECRET', '14lola3s59f6tmnfc6ll2166bgceicjfc10h50278dcg7ej6b'),
            ])->put('https://api.engagespot.co/v3/users/'.$identifier, $spec);
        } catch (Exception $e) {

        } catch (ErrorException $e) {

        }
    }

    private function engagespotApi($notifcationSpec)
    {
        $response = Http::withHeaders([
            'X-ENGAGESPOT-API-KEY' => env('ENGAGESPOT_API_KEY', 'n76ud46qb5yq8zhlg0l9'),
            'X-ENGAGESPOT-API-SECRET' => env('ENGAGESPOT_API_SECRET', '14lola3s59f6tmnfc6ll2166bgceicjfc10h50278dcg7ej6b'),
        ])->post('https://api.engagespot.co/v3/notifications', $notifcationSpec);

        return $response;
    }
}
