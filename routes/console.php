<?php

use App\Console\Commands\ExpireSubscriptions;
use App\Console\Commands\SendExpiringReminders;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ── Tandai langganan kadaluarsa setiap tengah malam ──────────────────────────
Schedule::command(ExpireSubscriptions::class)
    ->dailyAt('00:00')
    ->withoutOverlapping()
    ->runInBackground();

// ── Kirim reminder H-7: peringatan awal setiap pagi jam 08:00 ────────────────
Schedule::command(SendExpiringReminders::class, ['--days=7'])
    ->dailyAt('08:00')
    ->withoutOverlapping()
    ->runInBackground();

// ── Kirim reminder H-3: pengingat mendesak setiap pagi jam 08:00 ─────────────
Schedule::command(SendExpiringReminders::class, ['--days=3'])
    ->dailyAt('08:00')
    ->withoutOverlapping()
    ->runInBackground();


