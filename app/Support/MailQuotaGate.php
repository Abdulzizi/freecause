<?php

namespace App\Support;

use Illuminate\Mail\SendQueuedMailable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;

/**
 * Job middleware for queued mailables. While the provider quota is exhausted
 * the job never touches SMTP, and a quota rejection is absorbed here (not
 * thrown) so it neither fails the job nor reaches Sentry once per queued mail.
 */
class MailQuotaGate
{
    public function __construct(private bool $transactional) {}

    public function handle(SendQueuedMailable $job, callable $next): void
    {
        if (! MailQuota::tripped()) {
            try {
                $next($job);

                return;
            } catch (\Throwable $e) {
                if (! MailQuota::causedBy($e)) {
                    throw $e;
                }
                MailQuota::trip();
            }
        }

        $name = $job->displayName();
        $job->delete();

        if (! $this->transactional) {
            Log::warning("Mail quota exhausted - dropped {$name}");

            return;
        }

        // Fresh job (attempts reset) so --tries cannot fail it; retried after the flag expires.
        // Only Mailable::newQueuedJob() attaches middleware, so copy it or the retry skips this gate.
        $retry = (new SendQueuedMailable($job->mailable))->through($job->middleware);
        Queue::connection($retry->connection)->later(
            now()->addDay()->startOfDay()->addMinutes(random_int(5, 60)),
            $retry,
            '',
            $retry->queue
        );
        Log::warning("Mail quota exhausted - deferred {$name} to tomorrow");
    }
}
