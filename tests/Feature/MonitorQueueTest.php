<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class MonitorQueueTest extends TestCase
{
    use RefreshDatabase;

    private function recordFailure(string $at): void
    {
        DB::table('failed_jobs')->insert([
            'uuid' => (string) str()->uuid(),
            'connection' => 'database',
            'queue' => 'default',
            'payload' => json_encode(['displayName' => 'App\\Mail\\PetitionDigestMail']),
            'exception' => 'boom',
            'failed_at' => $at,
        ]);
    }

    public function test_stale_backlog_does_not_alert(): void
    {
        Cache::flush();
        $this->recordFailure('2026-01-01 00:00:00');

        Cache::forever('queue:monitor:last-checked-at', '2026-06-01 00:00:00');

        $this->artisan('queue:monitor')->assertExitCode(0);
    }

    public function test_new_failure_alerts(): void
    {
        // exit 0: see MonitorQueue::handle
        Cache::flush();
        Cache::forever('queue:monitor:last-checked-at', '2026-01-01 00:00:00');
        $this->recordFailure(now()->toDateTimeString());

        $this->artisan('queue:monitor')->assertExitCode(0);
    }

    public function test_first_run_reports_existing_failures(): void
    {
        Cache::flush();
        $this->recordFailure(now()->toDateTimeString());

        $this->artisan('queue:monitor')->assertExitCode(0);
    }

    public function test_repeated_new_failures_mail_at_most_hourly(): void
    {
        Cache::flush();
        Mail::shouldReceive('raw')->once();

        $this->recordFailure(now()->toDateTimeString());
        $this->artisan('queue:monitor', ['--alert-email' => 'ops@example.com'])->expectsOutput('Alert sent to ops@example.com')->run();

        $this->travel(5)->minutes();
        $this->recordFailure(now()->toDateTimeString());
        $this->artisan('queue:monitor', ['--alert-email' => 'ops@example.com'])
            ->expectsOutput('New failed jobs: 1')
            ->doesntExpectOutput('Alert sent to ops@example.com')
            ->assertExitCode(0);
    }
}
