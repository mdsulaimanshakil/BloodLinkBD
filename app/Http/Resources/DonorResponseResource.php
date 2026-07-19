<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Prompt 18: API Resource for DonorResponse.
 *
 * Returned when a donor responds to a blood request via the API.
 */
class DonorResponseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var \App\Models\DonorResponse $this */

        return [
            'id'              => $this->id,
            'blood_request_id'=> $this->blood_request_id,
            'donor_id'        => $this->donor_id,
            'donor_name'      => $this->donor?->name,
            'status'          => $this->status,
            'responded_at'    => $this->responded_at?->toIso8601String(),
            'created_at'      => $this->created_at->toIso8601String(),
        ];
    }
}
