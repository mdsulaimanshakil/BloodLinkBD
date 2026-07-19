<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\DonorResponseResource;
use App\Models\BloodRequest;
use App\Models\DonorResponse;
use App\Services\Notifications\NotificationChannelService;
use App\Services\Notifications\WhatsAppNotificationChannel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Prompt 18: API – Donor Response ("I Can Help").
 *
 * POST /api/v1/requests/{bloodRequest}/respond
 *
 * Requires auth:sanctum + the caller must have a verified, available donor profile.
 */
class DonorResponseApiController extends Controller
{
    public function __construct(
        protected NotificationChannelService $notifier
    ) {}

    /**
     * Record a donor's response to a blood request.
     */
    public function store(Request $request, BloodRequest $bloodRequest): JsonResponse
    {
        $donor        = $request->user();
        $donorProfile = $donor->donorProfile;

        // Guard: must have a verified, available donor profile
        if (! $donorProfile || ! $donorProfile->is_verified || ! $donorProfile->is_available) {
            return response()->json([
                'message' => 'You must have a verified and available donor profile to respond to requests.',
            ], 403);
        }

        // Guard: request must be active
        if ($bloodRequest->status !== 'active') {
            return response()->json([
                'message' => 'This blood request is no longer active.',
            ], 422);
        }

        // Guard: prevent duplicate responses
        $alreadyResponded = DonorResponse::where('blood_request_id', $bloodRequest->id)
            ->where('donor_id', $donor->id)
            ->exists();

        if ($alreadyResponded) {
            return response()->json([
                'message' => 'You have already responded to this request.',
            ], 422);
        }

        // Create the response record
        $response = DonorResponse::create([
            'blood_request_id' => $bloodRequest->id,
            'donor_id'         => $donor->id,
            'status'           => 'pending',
            'responded_at'     => now(),
        ]);

        // Notify the requester (same logic as web flow)
        $whatsappLink = WhatsAppNotificationChannel::whatsappLink(
            $donorProfile->phone,
            "Hi, I responded to your blood request on BloodLinkBD. I have {$donorProfile->blood_group} blood and I'm available to donate."
        );

        $subject = '🩸 A donor has responded to your blood request!';
        $message = "{$donor->name} has responded to your {$bloodRequest->blood_group} blood request "
                 . "for {$bloodRequest->patient_name} at {$bloodRequest->hospital}, {$bloodRequest->district}.\n\n"
                 . "Contact them on WhatsApp: {$whatsappLink}";

        if ($bloodRequest->requester_id && $bloodRequest->requester) {
            $this->notifier->notifyUser($bloodRequest->requester, $subject, $message);
        }

        return response()->json(new DonorResponseResource($response->load('donor')), 201);
    }
}
