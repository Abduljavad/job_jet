<?php

namespace App\Broadcasting;

use App\Http\Services\EngagespotServices;
use Illuminate\Notifications\Notification;

class EngagespotChannel
{
    public $engagespotServices;

    public function __construct(EngagespotServices $engagespotServices)
    {
        $this->engagespotServices = $engagespotServices;
    }

    /**
     * Send the given notification.
     *
     * @param  mixed  $notifiable
     * @return void
     */
    public function send($notifiable, Notification $notification)
    {
        $message = $notification->toEngageSpot($notifiable, $this->engagespotServices);

        // Send notification to the $notifiable instance...
    }
}
