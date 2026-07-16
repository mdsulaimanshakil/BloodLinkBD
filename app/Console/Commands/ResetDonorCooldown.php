<?php

namespace App\Console\Commands;

use App\Models\DonorProfile;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class ResetDonorCooldown extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'donors:reset-cooldown';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset availability for donors whose 90-day cooldown has elapsed';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $cutoffDate = Carbon::now()->subDays(90)->toDateString();

        $updated = DonorProfile::query()
            ->where('is_available', false)
            ->whereNotNull('last_donation_date')
            ->where('last_donation_date', '<=', $cutoffDate)
            ->update(['is_available' => true]);

        $this->info("Cooldown reset complete: {$updated} donor(s) marked as available.");

        return self::SUCCESS;
    }
}
