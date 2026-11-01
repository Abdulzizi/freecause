<?php

namespace Tests\Feature;

use Illuminate\Console\Scheduling\Schedule;
use Tests\TestCase;

class BackupConfigTest extends TestCase
{
    public function test_dump_uses_single_transaction(): void
    {
        $this->assertTrue(config('database.connections.mysql.dump.useSingleTransaction'));
    }

    public function test_backup_jobs_are_scheduled_and_monitored_disks_match_destinations(): void
    {
        $commands = collect(app(Schedule::class)->events())->pluck('command')->implode(' ');

        foreach (['backup:run', 'backup:clean', 'backup:monitor'] as $name) {
            $this->assertStringContainsString($name, $commands);
        }

        $this->assertSame(
            config('backup.backup.destination.disks'),
            config('backup.monitor_backups.0.disks'),
        );
    }
}
