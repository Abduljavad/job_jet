<?php

namespace App\Notifications;

use App\Broadcasting\EngagespotChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InAppNotitification extends Notification implements ShouldQueue
{
    use Queueable;

    public $category;

    public $count;

    /**
     * Create a new notification instance.
     */
    public function __construct($category, $count)
    {
        $this->category = $category;
        $this->count = $count;
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
        $templateId = env('SEND_IN_APP_NOTIFICATION_TEMPLATE_ID', '9040');
        $data = [
            'category_name' => $this->category->name,
            'count' => $this->count,
        ];
        $response = ($engagespotServices->sendNotificationWithTemplate($templateId, $recipientId, $data));
    }
}
