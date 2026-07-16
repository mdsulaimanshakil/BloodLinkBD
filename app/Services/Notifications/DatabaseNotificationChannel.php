<?php

namespace App\Services\Notifications;

use App\Contracts\NotificationChannelInterface;
use App\Models\DonorProfile;
use App\Notifications\DonorNotification;

/**
 * Sends notifications via Laravel's database notification system (in-app, free channel).
 */
class DatabaseNotificationChannel implements NotificationChannelInterface
{
    /**
     * Store a notification in the database for in-app display.
     */
    public function send(DonorProfile $donor, string $message): void
    {
        $donor->user->notify(new DonorNotification($message));
    }
}
