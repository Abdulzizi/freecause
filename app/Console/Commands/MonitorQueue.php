<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;

class MonitorQueue extends Command
{
    protected $signature = 'queue:monitor {--alert-email= : Email to send alerts to}';

    protected $description = 'Monitor queue health and alert on issues';

    public function handle(): int
    {
        $alerts = [];

        // Read the backlog from the CONFIGURED queue backend. Prod runs
        // QUEUE_CONNECTION=redis, so counting DB::table('jobs') always
        // returned 0 and backlog/stale alerts could never fire.
        $connection = config('queue.default');
        $driver = config("queue.connections.{$connection}.driver");

        try {
            $pendingJobs = Queue::connection($connection)->size();
        } catch (\Throwable $e) {
            $pendingJobs = $driver === 'database' ? DB::table('jobs')->count() : 0;
        }

        // failed_jobs is backend-agnostic (always the database failed driver).
        $failedJobs = DB::table('failed_jobs')->count();

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

        if ($failedJobs > 0) {
            $msg = "Failed jobs detected: {$failedJobs}";
            $alerts[] = $msg;
            $this->error($msg);

            $recentFailures = DB::table('failed_jobs')
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
            'alerts' => $alerts,
        ]);

        return empty($alerts) ? 0 : 1;
    }

    private function sendAlert(string $email, array $alerts): void
    {
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
