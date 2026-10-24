<?php

namespace Tests\Feature;

use Spatie\Backup\Notifications\Notifications\BackupHasFailedNotification;
use Spatie\Backup\Notifications\Notifications\BackupWasSuccessfulNotification;
use Spatie\Backup\Notifications\Notifications\CleanupHasFailedNotification;
use Spatie\Backup\Notifications\Notifications\CleanupWasSuccessfulNotification;
use Spatie\Backup\Notifications\Notifications\HealthyBackupWasFoundNotification;
use Spatie\Backup\Notifications\Notifications\UnhealthyBackupWasFoundNotification;
use Tests\TestCase;

class BackupNotificationsConfigTest extends TestCase
{
    public function test_backup_notifications_config_has_all_mapped_empty_channels(): void
    {
        $classes = [
            BackupHasFailedNotification::class,
            UnhealthyBackupWasFoundNotification::class,
            CleanupHasFailedNotification::class,
            BackupWasSuccessfulNotification::class,
            HealthyBackupWasFoundNotification::class,
            CleanupWasSuccessfulNotification::class,
        ];

        foreach ($classes as $class) {
            $notifications = config('backup.notifications.notifications');
            $this->assertArrayHasKey($class, $notifications);
            $this->assertSame([], $notifications[$class]);
            $notification = (new \ReflectionClass($class))->newInstanceWithoutConstructor();
            $this->assertSame([], $notification->via());
        }
    }
}
