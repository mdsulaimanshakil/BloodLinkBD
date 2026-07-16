<?php

namespace App\Services\Notifications;

use App\Contracts\NotificationChannelInterface;
use App\Models\DonorProfile;

/**
 * Aggregator service that dispatches notifications to all registered channels.
 *
 * To add a new channel (e.g. paid SMS), simply:
 * 1. Create a class implementing NotificationChannelInterface.
 * 2. Add it to the $channels array in the constructor below (or register via the service provider).
 */
class NotificationChannelService
{
    /**
     * @var list<NotificationChannelInterface>
     */
    protected array $channels;

    public function __construct(
        EmailNotificationChannel $email,
        DatabaseNotificationChannel $database,
    ) {
        $this->channels = [$email, $database];
    }

    /**
     * Send a notification through all registered push channels.
     */
    public function sendAll(DonorProfile $donor, string $message): void
    {
        foreach ($this->channels as $channel) {
            $channel->send($donor, $message);
        }
    }

    /**
     * Generate a WhatsApp click-to-chat link for a phone number.
     *
     * @param  string $phone   Phone number (e.g. "01712345678").
     * @param  string $message Pre-filled message text.
     * @return string          The wa.me URL.
     */
    public function whatsappLink(string $phone, string $message): string
    {
        return WhatsAppNotificationChannel::whatsappLink($phone, $message);
    }
}
