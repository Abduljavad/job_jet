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
        $user = User::with('profile')->find($recipientId);
        $mobileNumber = $user->profile ? $user->profile->contact : null;

        $notifcationContent = [
            'notification' => [
                'templateId' => $templateID,
                "icon" => $icon,
            ],
            "data" => $data ?? null,
            "recipients" => [strval($recipientId)],
        ];


        $this->createOrUpdateUser($recipientId, $user->name, $user->email, $mobileNumber);
        return $this->engagespotApi($notifcationContent);
    }

    public function createOrUpdateUser($identifier, $name, $email, $mobileNumber)
    {
        try {
            $spec = [
                'name' => $name ??  null,
                'email' => $email ?? null,
                'phoneNumber' => $mobileNumber ?? null,
            ];
            Http::withHeaders([
                'X-ENGAGESPOT-API-KEY' => env('ENGAGESPOT_API_KEY', 'xruaybldq3lsq2x2x9ctz'),
                'X-ENGAGESPOT-API-SECRET' => env('ENGAGESPOT_API_SECRET', 'ko0eq3g41i90k3o0tid0bohcdbejaag256500ddd3i63f6ga'),
            ])->put('https://api.engagespot.co/v3/users/' . $identifier, $spec);
        } catch (Exception $e) {
            true;
        } catch (ErrorException $e) {
            true;
        }
    }
    private function engagespotApi($notifcationSpec)
    {
        $response = Http::withHeaders([
            'X-ENGAGESPOT-API-KEY' => env('ENGAGESPOT_API_KEY', 'xruaybldq3lsq2x2x9ctz'),
            'X-ENGAGESPOT-API-SECRET' => env('ENGAGESPOT_API_SECRET', 'ko0eq3g41i90k3o0tid0bohcdbejaag256500ddd3i63f6ga'),
        ])->post('https://api.engagespot.co/v3/notifications', $notifcationSpec);

        return $response;
    }
}
