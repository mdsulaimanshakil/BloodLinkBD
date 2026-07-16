<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ──────────────────────────────────────────
// Scheduled Commands
// ──────────────────────────────────────────

// Reset donor availability after 90-day cooldown (runs daily at midnight)
Schedule::command('donors:reset-cooldown')->daily();

// Expire blood requests past their expires_at time (runs hourly)
Schedule::command('requests:expire')->hourly();

