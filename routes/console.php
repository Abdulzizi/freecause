<?php

use App\Models\Log;
use App\Support\AppLog;
use App\Support\Settings;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('signatures:reconcile')->dailyAt('03:00');
Schedule::command('backup:run')->dailyAt('02:00');
Schedule::command('backup:clean')->dailyAt('02:30');
Schedule::command('cache:warm')->hourly();
Schedule::command('queue:monitor', [
    '--alert-email' => Settings::get('email_to_staff', '') ?: config('mail.from.address'),
])->everyFiveMinutes();

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

Schedule::call(function () {
    $finalized = Log::onlyTrashed()->where('deleted_at', '<', now()->subDays(7))->forceDelete();

    if ($finalized > 0) {
        AppLog::info(
            'Soft-deleted logs finalized',
            "Removed {$finalized} logs",
            'system.logs'
        );
    }
})->daily();

Schedule::call(function () {
    $deleted = DB::table('spam_logs')
        ->where('created_at', '<', now()->subDays(7))
        ->delete();

    if ($deleted > 0) {
        AppLog::info(
            'Old spam logs pruned',
            "Deleted {$deleted} spam logs",
            'system.logs'
        );
    }
})->daily();
