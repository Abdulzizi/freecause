<?php

namespace Tests\Feature;

use App\Support\MailQuota;
use Illuminate\Contracts\Queue\Job;
use Illuminate\Queue\Events\JobExceptionOccurred;
use Illuminate\Support\Facades\Event;
use RuntimeException;
use Tests\TestCase;

class MailQuotaGuardTest extends TestCase
{
    public function test_quota_error_messages_are_recognised(): void
    {
        $this->assertTrue(MailQuota::causedBy(new RuntimeException(
            'Expected response code "250/251/252" but got code "552", with message "552 Your monthly email allowance is exhausted".'
        )));

        $this->assertTrue(MailQuota::causedBy(new RuntimeException(
            'Expected response code "354" but got code "452", with message "452 Sending to this many would exceed your monthly email allowance".'
        )));

        $this->assertFalse(MailQuota::causedBy(new RuntimeException(
            'Expected response code "250/251/252" but got code "550", with message "550 unroutable domain: engl.co.at".'
        )));
    }

    public function test_worker_quota_failure_trips_the_abort_flag(): void
    {
        Event::dispatch(new JobExceptionOccurred(
            'database',
            $this->createMock(Job::class),
            new RuntimeException('552 Your monthly email allowance is exhausted')
        ));

        $this->assertTrue(MailQuota::tripped());
    }

    public function test_unrelated_worker_failure_leaves_flag_clear(): void
    {
        Event::dispatch(new JobExceptionOccurred(
            'database',
            $this->createMock(Job::class),
            new RuntimeException('550 unroutable domain: engl.co.at')
        ));

        $this->assertFalse(MailQuota::tripped());
    }
}
