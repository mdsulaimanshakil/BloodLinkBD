<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Prompt 18: API Resource for BloodRequest.
 *
 * Stable shape for the request feed and request detail endpoints.
 * Requester phone is masked in public context.
 */
class BloodRequestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var \App\Models\BloodRequest $this */

        $canRevealPhone = $request->user()?->isAdmin()
            || $request->user()?->donorProfile?->is_verified;

        return [
            'id'               => $this->id,
            'patient_name'     => $this->patient_name,
            'blood_group'      => $this->blood_group,
            'district'         => $this->district,
            'hospital'         => $this->hospital,
            'urgency'          => $this->urgency,
            'urgency_label'    => $this->urgency_label,
            'status'           => $this->status,
            'additional_notes' => $this->additional_notes,
            'requester_phone'  => $canRevealPhone
                                        ? $this->requester_phone
                                        : $this->masked_phone,
            'expires_at'       => $this->expires_at?->toIso8601String(),
            'is_expired'       => $this->is_expired,
            'created_at'       => $this->created_at->toIso8601String(),
            'responses_count'  => $this->whenCounted('donorResponses'),
            'links'            => [
                'self'   => route('blood-requests.show', $this->id),
                'respond'=> route('blood-requests.respond', $this->id),
            ],
        ];
    }
}
