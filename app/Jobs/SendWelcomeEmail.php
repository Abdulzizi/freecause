<?php

namespace App\Jobs;

use App\Mail\WelcomeAccountMail;
use App\Models\User;
use App\Support\Locale;
use App\Support\MailQuota;
use App\Support\Settings;
use DateTimeInterface;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class SendWelcomeEmail implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public int $tries = 0;

    public int $uniqueFor = 604800;

    public DateTimeInterface $deadline;

    public function __construct(public int $userId)
    {
        $this->deadline = now()->addWeek();
    }

    public function uniqueId(): string
    {
        return (string) $this->userId;
    }

    public function retryUntil(): DateTimeInterface
    {
        return $this->deadline;
    }

    public function backoff(): array
    {
        return [60, 300, 900, 3600];
    }

    public function handle(): void
    {
        if (! Settings::get('smtp_enabled', false) || MailQuota::tripped()) {
            $this->release(3600);

            return;
        }

        // A row lock also prevents duplicate delivery if an expired unique lock is reacquired.
        DB::transaction(function () {
            $user = User::query()->lockForUpdate()->find($this->userId);

            if (! $user || ! $user->welcome_email_pending || $user->welcome_email_sent_at
                || ! $user->verified || ! $user->hasCompleteProfile()) {
                return;
            }

            try {
                Mail::to($user->email)->send(new WelcomeAccountMail(Locale::toShort($user->locale)));
            } catch (\Throwable $e) {
                if (MailQuota::causedBy($e)) {
                    MailQuota::trip();
                }

                throw $e;
            }

            $user->forceFill(['welcome_email_sent_at' => now(), 'welcome_email_pending' => false])->save();
        });
    }
}
