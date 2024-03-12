<?php

namespace App\Listeners;

use App\Events\JobCreatedEvent;
use App\Models\Job;
use App\Models\User;
use App\Notifications\InAppNotitification;
use Illuminate\Support\Facades\Notification;

class SendInAppCategoryWiseNotification
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(JobCreatedEvent $event): void
    {
        $job = $event->job->load('categories');
        $category = $job->categories()->first();
        $jobCount = Job::whereRelation('categories', 'id', $category->id)->count();
        if ($category && $jobCount % 5 == 0) {
            $users = User::whereDoesntHave('roles', function ($query) {
                $query->where('name', 'like', 'super_admin');
            })->get();
            Notification::send($users, new InAppNotitification($category, 5));
        }
    }
}
