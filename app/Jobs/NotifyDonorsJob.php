<?php

namespace App\Jobs;

use App\Helpers\BloodCompatibility;
use App\Models\BloodRequest;
use App\Models\DonorProfile;
use App\Notifications\BloodRequestNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Queued job that finds eligible donors for a blood request and notifies them.
 *
 * Eligibility criteria:
 * 1. Compatible blood group (via BloodCompatibility helper)
 * 2. Same district as the request
 * 3. is_available = true
 * 4. is_verified = true
 *
 * Uses the `database` queue driver for local development.
 */
class NotifyDonorsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     */
    public int $backoff = 30;

    public function __construct(
        protected BloodRequest $bloodRequest,
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $request = $this->bloodRequest;

        // Skip if the request is no longer active
        if ($request->status !== 'active') {
            Log::info("NotifyDonorsJob: Skipping request #{$request->id} — status is '{$request->status}'.");
            return;
        }

        // 1. Get compatible donor blood groups
        $compatibleGroups = BloodCompatibility::compatibleDonors($request->blood_group);

        if (empty($compatibleGroups)) {
            Log::warning("NotifyDonorsJob: No compatible blood groups found for {$request->blood_group}.");
            return;
        }

        // 2. Find eligible donors: compatible blood group + same district + available + verified
        $eligibleDonors = DonorProfile::query()
            ->where('is_available', true)
            ->where('is_verified', true)
            ->where('district', $request->district)
            ->whereIn('blood_group', $compatibleGroups)
            ->with('user')
            ->get();

        if ($eligibleDonors->isEmpty()) {
            Log::info("NotifyDonorsJob: No eligible donors found for request #{$request->id} ({$request->blood_group} in {$request->district}).");
            return;
        }

        // 3. Notify each eligible donor
        $notification = new BloodRequestNotification($request);
        $notifiedCount = 0;

        foreach ($eligibleDonors as $donorProfile) {
            if ($donorProfile->user) {
                try {
                    $donorProfile->user->notify($notification);
                    $notifiedCount++;
                } catch (\Exception $e) {
                    Log::error("NotifyDonorsJob: Failed to notify donor #{$donorProfile->user_id}: " . $e->getMessage());
                }
            }
        }

        Log::info("NotifyDonorsJob: Notified {$notifiedCount} donor(s) for request #{$request->id} ({$request->blood_group} in {$request->district}).");
    }
}
