<?php

namespace App\Console\Commands;

use App\Models\BloodRequest;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class ExpireBloodRequests extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'requests:expire';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mark active blood requests as expired once their expires_at time has passed';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $now = Carbon::now();

        $updated = BloodRequest::query()
            ->where('status', 'active')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', $now)
            ->update(['status' => 'expired']);

        $this->info("Auto-expiry complete: {$updated} request(s) marked as expired.");

        return self::SUCCESS;
    }
}
