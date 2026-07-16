<?php

namespace App\Services\Notifications;

use App\Contracts\NotificationChannelInterface;
use App\Models\DonorProfile;

/**
 * WhatsApp notification channel — generates one-tap wa.me links (free channel).
 *
 * Since WhatsApp links are user-initiated (click-to-chat), the send() method
 * is intentionally a no-op. Use the static whatsappLink() helper to generate
 * shareable links in views or API responses.
 */
class WhatsAppNotificationChannel implements NotificationChannelInterface
{
    /**
     * No-op: WhatsApp links are user-initiated, not server-push.
     */
    public function send(DonorProfile $donor, string $message): void
    {
        // WhatsApp links are generated on-demand via whatsappLink().
        // This channel cannot push notifications — it's user-initiated only.
    }

    /**
     * Generate a WhatsApp click-to-chat link.
     *
     * @param  string $phone   Phone number with country code (e.g. "8801712345678").
     * @param  string $message Pre-filled message text.
     * @return string          The wa.me URL.
     */
    public static function whatsappLink(string $phone, string $message): string
    {
        // Strip any non-numeric characters from the phone number
        $cleanPhone = preg_replace('/\D/', '', $phone);

        // If number starts with '0', assume Bangladesh (+880) and prepend country code
        if (str_starts_with($cleanPhone, '0')) {
            $cleanPhone = '880' . substr($cleanPhone, 1);
        }

        return 'https://wa.me/' . $cleanPhone . '?text=' . urlencode($message);
    }
}
