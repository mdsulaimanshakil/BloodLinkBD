<?php

namespace App\Services;

use App\Models\DonationHistory;
use App\Models\DonorProfile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DonationService
{
    /**
     * Record a donation and trigger the 90-day cooldown on the donor.
     *
     * Creates a DonationHistory record, sets the donor's is_available to false,
     * updates last_donation_date to now, and increments donation_count.
     *
     * @param  int         $donorId         The user ID of the donor.
     * @param  int|null    $bloodRequestId  The blood request this donation fulfills (nullable).
     * @param  string|null $hospital        Hospital name where donation took place.
     * @param  string|null $district        District where donation took place.
     * @return DonationHistory              The newly created donation history record.
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function recordDonation(
        int $donorId,
        ?int $bloodRequestId = null,
        ?string $hospital = null,
        ?string $district = null,
    ): DonationHistory {
        return DB::transaction(function () use ($donorId, $bloodRequestId, $hospital, $district) {
            // 1. Create the donation history record
            $donation = DonationHistory::create([
                'donor_id'         => $donorId,
                'blood_request_id' => $bloodRequestId,
                'donated_at'       => Carbon::now()->toDateString(),
                'hospital'         => $hospital,
                'district'         => $district,
            ]);

            // 2. Apply the 90-day cooldown on the donor profile
            $donorProfile = DonorProfile::where('user_id', $donorId)->firstOrFail();

            $donorProfile->update([
                'is_available'       => false,
                'last_donation_date' => Carbon::now(),
                'donation_count'     => $donorProfile->donation_count + 1,
            ]);

            return $donation;
        });
    }
}
