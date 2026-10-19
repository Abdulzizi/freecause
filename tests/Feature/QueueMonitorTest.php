<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class QueueMonitorTest extends TestCase
{
    use RefreshDatabase;

    public function test_monitor_reads_integer_available_at_without_type_error(): void
    {
        config(['queue.default' => 'database']);

        DB::table('jobs')->insert([
            'queue' => 'default',
            'payload' => json_encode(['displayName' => 'App\\Jobs\\Example']),
            'attempts' => 0,
            'available_at' => time(),
            'created_at' => time(),
        ]);

        $this->artisan('queue:monitor')->assertExitCode(0);
    }

    public function test_monitor_alerts_on_stale_job(): void
    {
        config(['queue.default' => 'database']);

        DB::table('jobs')->insert([
            'queue' => 'default',
            'payload' => json_encode(['displayName' => 'App\\Jobs\\Example']),
            'attempts' => 0,
            'available_at' => now()->subHours(2)->getTimestamp(),
            'created_at' => now()->subHours(2)->getTimestamp(),
        ]);

        $this->artisan('queue:monitor')->assertExitCode(1);
    }
}
