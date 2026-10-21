<?php

namespace App\Console\Commands;

use App\Jobs\RescanReviewKeywords as RescanJob;
use App\Services\ReviewKeywordRescan;
use App\Support\Settings;
use Illuminate\Console\Command;

class RescanReviewKeywords extends Command
{
    protected $signature = 'review-keywords:rescan
        {--limit= : Maximum number of petitions to scan}
        {--after-id= : Resume the scan after this petition id}';

    protected $description = 'Hold published petitions that match the current review keyword list';

    public function handle(): int
    {
        $limit = $this->option('limit');
        $afterId = $this->option('after-id');

        $lease = RescanJob::leaseState();

        if ($lease['running'] && RescanJob::leaseIsFresh($lease['startedAt'])) {
            Settings::set(
                RescanJob::STATUS_KEY,
                'failed — a rescan is already running, the command did not scan',
                RescanJob::CURSOR_GROUP
            );

            $this->error('A rescan is already running. The command did not scan any petitions.');

            return self::FAILURE;
        }

        $leaseStart = RescanJob::claimLease();

        $resumeFrom = $afterId === null
            ? max(0, (int) Settings::get(RescanJob::CURSOR_KEY, 0, RescanJob::CURSOR_GROUP))
            : max(0, (int) $afterId);

        try {
            $result = (new ReviewKeywordRescan)->run(
                $limit === null ? null : max(0, (int) $limit),
                ReviewKeywordRescan::DEFAULT_MAX_SECONDS,
                $resumeFrom,
                function (int $lastId): void {
                    Settings::set(RescanJob::CURSOR_KEY, $lastId, RescanJob::CURSOR_GROUP);
                }
            );
        } catch (\Throwable $e) {
            $message = $e->getMessage() !== '' ? $e->getMessage() : 'the rescan stopped before the end of the table';

            Settings::set(RescanJob::STATUS_KEY, 'failed — '.$message, RescanJob::CURSOR_GROUP);

            throw $e;
        } finally {
            RescanJob::releaseLease($leaseStart);
        }

        Settings::set(
            RescanJob::CURSOR_KEY,
            $result['complete'] ? 0 : $result['lastId'],
            RescanJob::CURSOR_GROUP
        );

        Settings::set(
            RescanJob::STATUS_KEY,
            RescanJob::statusText($result),
            RescanJob::CURSOR_GROUP
        );

        $this->info(
            'scanned '.$result['scanned']
            .', held '.$result['held']
            .', last id '.$result['lastId']
            .', complete '.($result['complete'] ? 'yes' : 'no')
        );

        return 0;
    }
}
