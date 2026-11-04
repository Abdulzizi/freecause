<?php

namespace Tests\Feature;

use App\Mail\VerifyAccountMail;
use App\Models\User;
use App\Support\MailQuota;
use App\Support\MailQuotaGate;
use App\Support\Settings;
use Database\Seeders\LanguageSeeder;
use Database\Seeders\UserLevelSeeder;
use Illuminate\Contracts\Queue\Job;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Mail\SendQueuedMailable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Queue;
use RuntimeException;
use Tests\TestCase;

class MailQuotaUserMessageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([UserLevelSeeder::class, LanguageSeeder::class]);
    }

    public function test_password_reset_says_try_later_without_sending_when_tripped(): void
    {
        MailQuota::trip();
        Password::shouldReceive('sendResetLink')->never();

        $this->from('/en/forgot-password')
            ->post('/en/forgot-password', ['email' => 'a@example.com'])
            ->assertRedirect('/en/forgot-password')
            ->assertSessionHasErrors(['email' => __('auth.email_try_later')]);
    }

    public function test_password_reset_quota_rejection_trips_flag_and_says_try_later(): void
    {
        Password::shouldReceive('sendResetLink')->once()
            ->andThrow(new RuntimeException('552 Your monthly email allowance is exhausted'));

        $this->from('/en/forgot-password')
            ->post('/en/forgot-password', ['email' => 'a@example.com'])
            ->assertSessionHasErrors(['email' => __('auth.email_try_later')]);

        $this->assertTrue(MailQuota::tripped());
    }

    public function test_password_reset_other_mail_errors_still_surface(): void
    {
        Password::shouldReceive('sendResetLink')->once()
            ->andThrow(new RuntimeException('550 unroutable domain'));

        $this->post('/en/forgot-password', ['email' => 'a@example.com'])->assertStatus(500);

        $this->assertFalse(MailQuota::tripped());
    }

    public function test_resend_verification_says_try_later_and_keeps_token_when_tripped(): void
    {
        Settings::set('smtp_enabled', true);
        Mail::fake();
        $user = User::factory()->create(['verified' => false, 'verification_token' => 'keep-me']);
        MailQuota::trip();

        $this->from('/en/resend-verification')
            ->post('/en/resend-verification', ['email' => $user->email])
            ->assertSessionHasErrors(['email' => __('auth.email_try_later')]);

        Mail::assertNothingQueued();
        $this->assertSame('keep-me', $user->fresh()->verification_token);
    }

    public function test_verification_mail_is_dropped_not_deferred_while_tripped(): void
    {
        MailQuota::trip();
        Queue::fake();
        $mailable = new VerifyAccountMail(new User, 'en');
        $job = (new SendQueuedMailable($mailable))->through($mailable->middleware());
        $job->job = $this->createMock(Job::class);

        (new MailQuotaGate($mailable->transactional ?? false))
            ->handle($job, fn () => $this->fail('SMTP reached'));

        Queue::assertNothingPushed();
    }
}
