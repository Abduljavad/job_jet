<?php

namespace App\Notifications;

use App\Broadcasting\EngagespotChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SendSmsNotification extends Notification
{
    use Queueable;

    public $otp;

    /**
     * Create a new notification instance.
     */
    public function __construct($otp)
    {
        $this->otp = $otp;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return [EngagespotChannel::class];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->line('The introduction to the notification.')
            ->action('Notification Action', url('/'))
            ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }

    public function toEngageSpot($notifiable, $engagespotServices)
    {
        $recipientId = $notifiable->id;
        $templateId = env('SEND_SMS_TEMPLATE_ID', '8890');
        $data = [
            'otp' => $this->otp,
        ];
        $response = ($engagespotServices->sendNotificationWithTemplate($templateId, $recipientId, $data));
    }
}
