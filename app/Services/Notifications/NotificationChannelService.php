<?php

namespace App\Services\Notifications;

use App\Contracts\NotificationChannelInterface;
use App\Models\DonorProfile;
use App\Models\User;
use App\Notifications\DonorNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

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
     * Send a notification through all registered push channels (targets a donor profile).
     */
    public function sendAll(DonorProfile $donor, string $message): void
    {
        foreach ($this->channels as $channel) {
            $channel->send($donor, $message);
        }
    }

    /**
     * Notify a User directly (used for requester notifications when a donor responds).
     * Sends both an in-app database notification and an email.
     */
    public function notifyUser(User $user, string $subject, string $message): void
    {
        // Database (in-app) notification
        try {
            $user->notify(new DonorNotification($message));
        } catch (\Throwable $e) {
            Log::error("NotificationChannelService: Failed DB notification for user #{$user->id}: " . $e->getMessage());
        }

        // Email notification (only if user has an email)
        if ($user->email) {
            try {
                Mail::raw($message, function ($mail) use ($user, $subject) {
                    $mail->to($user->email)->subject($subject);
                });
            } catch (\Throwable $e) {
                Log::error("NotificationChannelService: Failed email for user #{$user->id}: " . $e->getMessage());
            }
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

