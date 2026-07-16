<?php

namespace App\Services\Notifications;

use App\Contracts\NotificationChannelInterface;
use App\Models\DonorProfile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Sends notifications via Laravel Mail (free channel).
 */
class EmailNotificationChannel implements NotificationChannelInterface
{
    /**
     * Send a plain-text email to the donor's registered email address.
     */
    public function send(DonorProfile $donor, string $message): void
    {
        $email = $donor->user->email ?? null;

        if (! $email) {
            Log::warning("EmailNotificationChannel: No email found for donor #{$donor->id}");
            return;
        }

        Mail::raw($message, function ($mail) use ($email, $donor) {
            $mail->to($email)
                 ->subject('BloodLinkBD Notification for ' . $donor->user->name);
        });
    }
}
