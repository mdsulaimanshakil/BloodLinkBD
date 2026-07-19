<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Prompt 18: API Resource for DonorProfile.
 *
 * Shapes the public-facing donor data for the API.
 * Phone is masked by default; full phone only returned for authenticated callers.
 */
class DonorResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var \App\Models\DonorProfile $this */

        $canRevealPhone = $request->user()?->isAdmin()
            || $request->user()?->donorProfile?->is_verified;

        return [
            'id'                => $this->id,
            'name'              => $this->user?->name,
            'blood_group'       => $this->blood_group,
            'district'          => $this->district,
            'phone'             => $canRevealPhone
                                        ? $this->phone
                                        : $this->masked_phone,
            'is_verified'       => (bool) $this->is_verified,
            'is_available'      => (bool) $this->is_available,
            'donation_count'    => (int) $this->donation_count,
            'trust_score'       => $this->trust_score ? (float) $this->trust_score : null,
            'is_trusted'        => $this->is_trusted,
            'last_donation_date'=> $this->last_donation_date?->toDateString(),
            'days_since_last_donation' => $this->days_since_last_donation,
            'whatsapp_link'     => $canRevealPhone
                                        ? 'https://wa.me/' . preg_replace('/^0/', '88', $this->phone)
                                        : null,
        ];
    }
}
