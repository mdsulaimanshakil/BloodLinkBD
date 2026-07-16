<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Generic donor notification stored in the database `notifications` table.
 */
class DonorNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected string $message,
    ) {}

    /**
     * Deliver via the database channel.
     *
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Data stored in the `notifications` table.
     *
     * @return array<string, string>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'message' => $this->message,
        ];
    }
}
