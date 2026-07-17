<?php

namespace App\Notifications;

use App\Helpers\BloodCompatibility;
use App\Models\BloodRequest;
use App\Services\Notifications\WhatsAppNotificationChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Notification sent to eligible donors when a matching blood request is posted.
 *
 * Channels: mail (email) + database (in-app).
 */
class BloodRequestNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected BloodRequest $bloodRequest,
    ) {}

    /**
     * Deliver via mail and database channels.
     *
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Build the mail representation.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $r = $this->bloodRequest;
        $compatibleGroups = implode(', ', BloodCompatibility::compatibleDonors($r->blood_group));

        return (new MailMessage)
            ->subject("🩸 Urgent: {$r->blood_group} Blood Needed in {$r->district}")
            ->greeting("Hello {$notifiable->name},")
            ->line("A patient needs **{$r->blood_group}** blood urgently.")
            ->line("**Patient:** {$r->patient_name}")
            ->line("**Hospital:** {$r->hospital}, {$r->district}")
            ->line("**Urgency:** {$r->urgency_label}")
            ->line("**Compatible blood groups:** {$compatibleGroups}")
            ->line("**Expires:** {$r->expires_at->diffForHumans()}")
            ->action('View Request Details', url("/blood-requests/{$r->id}"))
            ->line('Thank you for being a lifesaver! 🙏');
    }

    /**
     * Data stored in the `notifications` table for in-app display.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $r = $this->bloodRequest;

        return [
            'type'            => 'blood_request',
            'blood_request_id' => $r->id,
            'blood_group'     => $r->blood_group,
            'district'        => $r->district,
            'hospital'        => $r->hospital,
            'urgency'         => $r->urgency,
            'patient_name'    => $r->patient_name,
            'message'         => "🩸 {$r->blood_group} blood needed at {$r->hospital}, {$r->district} ({$r->urgency_label})",
            'whatsapp_link'   => WhatsAppNotificationChannel::whatsappLink(
                $r->requester_phone,
                "Hi, I saw your blood request for {$r->blood_group} on BloodLinkBD. I'm available to donate."
            ),
        ];
    }
}
