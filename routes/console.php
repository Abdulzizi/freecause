<?php

use App\Models\Log;
use App\Support\AppLog;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('signatures:reconcile')->dailyAt('03:00');
Schedule::command('db:backup --keep=30')->dailyAt('02:00');
Schedule::command('db:backup --keep=7')->weeklyOn(0, '03:00');
Schedule::command('cache:warm')->hourly();

Schedule::call(function () {
    $deleted = Log::where('created_at', '<', now()->subDays(30))->delete();

    if ($deleted > 0) {
        AppLog::info(
            'Old logs pruned',
            "Deleted {$deleted} logs",
            'system.logs'
        );
    }
})->daily();
