<?php

namespace App\Console\Commands;

use App\Support\MailQuota;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;

class MonitorQueue extends Command
{
    protected $signature = 'queue:monitor {--alert-email= : Email to send alerts to}';

    protected $description = 'Monitor queue health and alert on issues';

    private const CHECKPOINT_KEY = 'queue:monitor:last-checked-at';

    public function handle(): int
    {
        $alerts = [];

        // Read the backlog from the CONFIGURED queue backend (prod runs
        // QUEUE_CONNECTION=database) instead of assuming a table, so the
        // backlog/stale alerts follow whatever driver is configured.
        $connection = config('queue.default');
        $driver = config("queue.connections.{$connection}.driver");

        try {
            $pendingJobs = Queue::connection($connection)->size();
        } catch (\Throwable $e) {
            $pendingJobs = $driver === 'database' ? DB::table('jobs')->count() : 0;
        }

        // failed_jobs is backend-agnostic (always the database failed driver).
        $failedJobs = DB::table('failed_jobs')->count();

        // Only failures NEWER than the last check are actionable. Alerting on the
        // absolute count re-fires forever on a backlog nobody has cleared.
        $newFailures = DB::table('failed_jobs')
            ->when(
                Cache::get(self::CHECKPOINT_KEY),
                fn ($q, $since) => $q->where('failed_at', '>', $since)
            )
            ->count();

        // Oldest-job age is only derivable from the database driver's jobs table.
        $oldestJob = $driver === 'database'
            ? DB::table('jobs')->orderBy('available_at')->value('available_at')
            : null;

        $this->info('Queue Health Check - '.now()->toDateTimeString());
        $this->line("Pending jobs: {$pendingJobs}");
        $this->line("Failed jobs: {$failedJobs}");

        if ($pendingJobs > 1000) {
            $msg = "High queue backlog: {$pendingJobs} pending jobs";
            $alerts[] = $msg;
            $this->error($msg);
        }

        if ($newFailures > 0) {
            $msg = "New failed jobs: {$newFailures}";
            $alerts[] = $msg;
            $this->error($msg);

            $recentFailures = DB::table('failed_jobs')
                ->when(
                    Cache::get(self::CHECKPOINT_KEY),
                    fn ($q, $since) => $q->where('failed_at', '>', $since)
                )
                ->orderByDesc('failed_at')
                ->limit(5)
                ->get(['id', 'queue', 'payload', 'failed_at']);

            foreach ($recentFailures as $f) {
                $payload = json_decode($f->payload, true);
                $jobName = $payload['displayName'] ?? 'unknown';
                $this->line("  - {$jobName} (queue: {$f->queue}, failed: {$f->failed_at})");
            }
        }

        if ($oldestJob) {
            $age = Carbon::createFromTimestamp((int) $oldestJob)->diffInMinutes(now());
            if ($age > 30) {
                $msg = "Stale job detected: oldest job is {$age} minutes old";
                $alerts[] = $msg;
                $this->warn($msg);
            }
        }

        if (! empty($alerts) && $this->option('alert-email')) {
            $this->sendAlert($this->option('alert-email'), $alerts);
        }

        Log::channel('daily')->info('Queue monitor check', [
            'pending' => $pendingJobs,
            'failed' => $failedJobs,
            'new_failed' => $newFailures,
            'alerts' => $alerts,
        ]);

        Cache::forever(self::CHECKPOINT_KEY, now()->toDateTimeString());

        // Alerts are findings of a healthy check, not a command failure: a non-zero
        // exit makes the scheduler raise ScheduledTaskFailed (a Sentry event per run).
        return 0;
    }

    private function sendAlert(string $email, array $alerts): void
    {
        if (MailQuota::tripped()) {
            Log::channel('daily')->warning('Queue alert not emailed, mail quota exhausted', ['alerts' => $alerts]);
            $this->warn('Mail quota exhausted - alert logged, not emailed.');

            return;
        }

        try {
            Mail::raw(
                "Queue Health Alert\n\n".implode("\n", $alerts)."\n\nTime: ".now()->toDateTimeString(),
                fn ($m) => $m
                    ->to($email)
                    ->subject('[FreeCause] Queue Health Alert')
                    ->from(config('mail.from.address'), config('app.name'))
            );
            $this->info("Alert sent to {$email}");
        } catch (\Exception $e) {
            $this->error("Failed to send alert: {$e->getMessage()}");
        }
    }
}
