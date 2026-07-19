<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Prompt 18: API Resource for authenticated User (token holder).
 *
 * Returned from the /api/v1/user endpoint and token creation response.
 */
class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var \App\Models\User $this */

        return [
            'id'         => $this->id,
            'name'       => $this->name,
            'email'      => $this->email,
            'role'       => $this->role,
            'is_admin'   => $this->isAdmin(),
            'is_donor'   => $this->isDonor(),
            'donor_profile' => $this->when(
                $this->relationLoaded('donorProfile'),
                fn () => $this->donorProfile ? new DonorResource($this->donorProfile) : null
            ),
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
