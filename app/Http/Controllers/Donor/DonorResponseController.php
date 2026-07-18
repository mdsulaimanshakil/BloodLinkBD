<?php

namespace App\Http\Controllers\Donor;

use App\Http\Controllers\Controller;
use App\Models\BloodRequest;
use App\Models\DonorResponse;
use App\Services\Notifications\NotificationChannelService;
use App\Services\Notifications\WhatsAppNotificationChannel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class DonorResponseController extends Controller
{
    public function __construct(
        protected NotificationChannelService $notifier
    ) {}

    /**
     * Prompt 13: Handle "I Can Help" — donor responds to a blood request.
     *
     * - Only verified, available donors can reach this (enforced by donor.verified middleware).
     * - Creates a DonorResponse record.
     * - Notifies the requester via NotificationChannelService (email + DB notification).
     * - Prevents duplicate responses from the same donor.
     */
    public function store(BloodRequest $bloodRequest): RedirectResponse
    {
        $donor = Auth::user();
        $donorProfile = $donor->donorProfile;

        // Guard: must have a verified, available donor profile
        if (! $donorProfile || ! $donorProfile->is_verified || ! $donorProfile->is_available) {
            return redirect()
                ->route('blood-requests.show', $bloodRequest)
                ->with('error', 'You must have a verified donor profile to respond.');
        }

        // Guard: request must be active
        if ($bloodRequest->status !== 'active') {
            return redirect()
                ->route('blood-requests.show', $bloodRequest)
                ->with('error', 'This blood request is no longer active.');
        }

        // Prevent duplicate responses
        $alreadyResponded = DonorResponse::where('blood_request_id', $bloodRequest->id)
            ->where('donor_id', $donor->id)
            ->exists();

        if ($alreadyResponded) {
            return redirect()
                ->route('blood-requests.show', $bloodRequest)
                ->with('info', 'You have already responded to this request.');
        }

        // Create the donor response record
        DonorResponse::create([
            'blood_request_id' => $bloodRequest->id,
            'donor_id'         => $donor->id,
            'status'           => 'pending',
            'responded_at'     => now(),
        ]);

        // Build donor's WhatsApp link to include in notification
        $whatsappLink = WhatsAppNotificationChannel::whatsappLink(
            $donorProfile->phone,
            "Hi, I responded to your blood request on BloodLinkBD. I have {$donorProfile->blood_group} blood and I'm available to donate."
        );

        // Build notification message for the requester
        $subject = "🩸 A donor has responded to your blood request!";
        $message = "Good news! {$donor->name} has responded to your {$bloodRequest->blood_group} blood request "
                 . "for {$bloodRequest->patient_name} at {$bloodRequest->hospital}, {$bloodRequest->district}.\n\n"
                 . "Contact them directly on WhatsApp: {$whatsappLink}";

        // Notify the requester (if request was posted by a registered user)
        if ($bloodRequest->requester_id && $bloodRequest->requester) {
            $this->notifier->notifyUser($bloodRequest->requester, $subject, $message);
        }

        return redirect()
            ->route('blood-requests.show', $bloodRequest)
            ->with('success', "Thank you, {$donor->name}! Your response has been recorded. The requester has been notified.");
    }
}
