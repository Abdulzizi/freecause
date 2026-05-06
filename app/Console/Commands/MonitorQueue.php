<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class MonitorQueue extends Command
{
    protected $signature = 'queue:monitor {--alert-email= : Email to send alerts to}';

    protected $description = 'Monitor queue health and alert on issues';

    public function handle(): int
    {
        $alerts = [];

        $pendingJobs = DB::table('jobs')->count();
        $failedJobs = DB::table('failed_jobs')->count();
        $oldestJob = DB::table('jobs')->orderBy('available_at')->value('available_at');

        $this->info("Queue Health Check - ".now()->toDateTimeString());
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
                ->get(['id', 'queue', 'failed_at']);

            foreach ($recentFailures as $f) {
                $payload = json_decode($f->payload, true);
                $jobName = $payload['displayName'] ?? 'unknown';
                $this->line("  - {$jobName} (queue: {$f->queue}, failed: {$f->failed_at})");
            }
        }

        if ($oldestJob) {
            $age = now()->diffInMinutes($oldestJob);
            if ($age > 30) {
                $msg = "Stale job detected: oldest job is {$age} minutes old";
                $alerts[] = $msg;
                $this->warn($msg);
            }
        }

        $supervisorStatus = $this->checkSupervisor();
        if (!$supervisorStatus['healthy']) {
            $msg = "Supervisor workers unhealthy: {$supervisorStatus['details']}";
            $alerts[] = $msg;
            $this->error($msg);
        }

        if (!empty($alerts) && $this->option('alert-email')) {
            $this->sendAlert($this->option('alert-email'), $alerts);
        }

        Log::channel('daily')->info('Queue monitor check', [
            'pending' => $pendingJobs,
            'failed' => $failedJobs,
            'alerts' => $alerts,
        ]);

        return empty($alerts) ? 0 : 1;
    }

    private function checkSupervisor(): array
    {
        try {
            $output = [];
            exec('supervisorctl status freecause-queue:* 2>&1', $output, $exitCode);

            $running = 0;
            $total = 0;
            $details = [];

            foreach ($output as $line) {
                if (trim($line) === '') {
                    continue;
                }
                $total++;
                if (str_contains($line, 'RUNNING')) {
                    $running++;
                } else {
                    $parts = preg_split('/\s+/', trim($line));
                    $details[] = $parts[0] ?? 'unknown' . ': ' . ($parts[1] ?? 'unknown');
                }
            }

            return [
                'healthy' => $running === $total && $total > 0,
                'details' => empty($details) ? "{$running}/{$total} running" : implode(', ', $details),
            ];
        } catch (\Exception $e) {
            return [
                'healthy' => false,
                'details' => $e->getMessage(),
            ];
        }
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
