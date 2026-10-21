<?php

namespace App\Jobs;

use App\Services\ReviewKeywordRescan;
use App\Support\Settings;
use Illuminate\Bus\Queueable;
use Illuminate\Bus\UniqueLock;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RescanReviewKeywords implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public const MAX_SECONDS = 45;

    public const MAX_SEGMENTS = 50;

    public const CURSOR_KEY = 'review_keywords_rescan_cursor';

    public const RUNNING_KEY = 'review_keywords_rescan_running';

    public const STARTED_KEY = 'review_keywords_rescan_started_at';

    public const STATUS_KEY = 'review_keywords_rescan_status';

    public const CURSOR_GROUP = 'moderation';

    public const LEASE_SECONDS = 600;

    public int $tries = 3;

    public int $backoff = 30;

    public int $timeout = 80;

    public int $uniqueFor = self::LEASE_SECONDS;

    private ?ReviewKeywordRescan $rescan;

    private bool $leaseLost = false;

    public function __construct(?ReviewKeywordRescan $rescan = null, public int $segment = 0, public int $leaseStart = 0)
    {
        $this->rescan = $rescan;
    }

    public function uniqueId(): string
    {
        return 'review-keywords-rescan-'.$this->segment;
    }

    /**
     * @return array{running: bool, startedAt: int}
     */
    public static function leaseState(): array
    {
        $rows = self::moderationSettings([self::RUNNING_KEY, self::STARTED_KEY]);

        return [
            'running' => (int) ($rows[self::RUNNING_KEY] ?? 0) === 1,
            'startedAt' => (int) ($rows[self::STARTED_KEY] ?? 0),
        ];
    }

    public static function lastStatus(): string
    {
        $rows = self::moderationSettings([self::STATUS_KEY]);

        return (string) ($rows[self::STATUS_KEY] ?? '');
    }

    public static function leaseIsFresh(int $startedAt): bool
    {
        return $startedAt > 0 && (time() - $startedAt) < self::LEASE_SECONDS;
    }

    /**
     * @param  array<int, string>  $keys
     * @return array<string, string>
     */
    protected static function moderationSettings(array $keys): array
    {
        return DB::table('settings')
            ->where('group', self::CURSOR_GROUP)
            ->whereIn('key', $keys)
            ->pluck('value', 'key')
            ->all();
    }

    public static function claimLease(): int
    {
        $startedAt = time();

        Settings::set(self::STARTED_KEY, $startedAt, self::CURSOR_GROUP);
        Settings::set(self::RUNNING_KEY, 1, self::CURSOR_GROUP);

        return $startedAt;
    }

    public static function releaseLease(?int $expectedStart = null): void
    {
        if ($expectedStart !== null) {
            $rows = self::moderationSettings([self::STARTED_KEY]);

            if ((int) ($rows[self::STARTED_KEY] ?? 0) !== $expectedStart) {
                return;
            }
        }

        Settings::set(self::RUNNING_KEY, 0, self::CURSOR_GROUP);
        Settings::set(self::STARTED_KEY, 0, self::CURSOR_GROUP);
    }

    /**
     * @param  array{scanned: int, held: int, lastId: int, complete: bool, failedChunks?: int}  $result
     */
    public static function statusText(array $result, bool $continues = false): string
    {
        $failed = (int) ($result['failedChunks'] ?? 0);

        $state = $failed > 0
            ? 'failed'
            : ($result['complete'] ? 'complete' : ($continues ? 'in progress' : 'stopped early'));

        $text = $state.' — scanned '.$result['scanned'].', held '.$result['held'];

        if ($failed > 0) {
            $text .= ', '.$failed.' chunk'.($failed === 1 ? '' : 's').' failed';
        }

        return $text;
    }

    public function handle(?ReviewKeywordRescan $rescan = null): void
    {
        $service = $this->rescan ?? $rescan ?? app(ReviewKeywordRescan::class);

        if (! $this->acquireLease()) {
            Log::warning('Review keyword rescan stopped: another run holds the lease.');

            return;
        }

        $cursor = $this->segment === 0
            ? 0
            : max(0, (int) (self::moderationSettings([self::CURSOR_KEY])[self::CURSOR_KEY] ?? 0));

        $result = $service->run(
            null,
            self::MAX_SECONDS,
            $cursor,
            function (int $lastId): bool {
                if (! $this->ownsLease()) {
                    $this->leaseLost = true;

                    return false;
                }

                Settings::set(self::CURSOR_KEY, $lastId, self::CURSOR_GROUP);

                return true;
            }
        );

        if ($this->leaseLost || ! $this->ownsLease()) {
            Log::warning('Review keyword rescan stopped: the lease was claimed by another run.');

            return;
        }

        $failedChunks = (int) ($result['failedChunks'] ?? 0);

        Settings::set(self::CURSOR_KEY, $result['complete'] ? 0 : $result['lastId'], self::CURSOR_GROUP);

        $continues = ! $result['complete'] && $failedChunks === 0 && $this->shouldContinue($result, $cursor);

        Settings::set(self::STATUS_KEY, self::statusText($result, $continues), self::CURSOR_GROUP);

        Log::info(
            'Review keyword rescan run: scanned '.$result['scanned']
            .', held '.$result['held']
            .', last id '.$result['lastId']
            .', complete '.($result['complete'] ? 'yes' : 'no')
            .($failedChunks > 0 ? ', failed chunks '.$failedChunks : '')
            .'.'
        );

        if (! $continues) {
            self::releaseLease($this->leaseStart);

            return;
        }

        $this->continueScan();
    }

    public function failed(?\Throwable $exception): void
    {
        if ($this->ownsLease()) {
            self::releaseLease($this->leaseStart);
        }

        Settings::set(self::STATUS_KEY, 'failed — the rescan stopped before the end of the table', self::CURSOR_GROUP);

        Log::error('Review keyword rescan failed: '.($exception?->getMessage() ?? 'unknown error'));
    }

    protected function acquireLease(): bool
    {
        if ($this->leaseStart > 0) {
            return $this->ownsLease();
        }

        $lease = self::leaseState();

        if ($lease['running'] && self::leaseIsFresh($lease['startedAt'])) {
            return false;
        }

        $this->leaseStart = self::claimLease();

        return true;
    }

    protected function ownsLease(): bool
    {
        if ($this->leaseStart <= 0) {
            return false;
        }

        $rows = self::moderationSettings([self::STARTED_KEY]);

        return (int) ($rows[self::STARTED_KEY] ?? 0) === $this->leaseStart;
    }

    /**
     * @param  array{scanned: int, held: int, lastId: int, complete: bool}  $result
     */
    protected function shouldContinue(array $result, int $cursor): bool
    {
        if ($this->segment >= self::MAX_SEGMENTS) {
            return false;
        }

        return $this->segment === 0 || $result['lastId'] > $cursor;
    }

    protected function continueScan(): void
    {
        if (! $this->ownsLease()) {
            Log::warning('Review keyword rescan stopped: the lease was claimed by another run.');

            return;
        }

        (new UniqueLock(app(CacheRepository::class)))->release($this);

        $renewed = time();

        $updated = DB::table('settings')
            ->where('group', self::CURSOR_GROUP)
            ->where('key', self::STARTED_KEY)
            ->where('value', (string) $this->leaseStart)
            ->update([
                'value' => (string) $renewed,
                'updated_at' => now(),
            ]);

        if ($updated === 0) {
            Log::warning('Review keyword rescan stopped: the lease was claimed by another run.');

            return;
        }

        Settings::forget();

        $this->leaseStart = $renewed;

        if ($this->nextSegmentIsQueued()) {
            self::releaseLease($this->leaseStart);

            Settings::set(
                self::STATUS_KEY,
                'stopped — segment '.($this->segment + 1).' is already queued',
                self::CURSOR_GROUP
            );

            Log::warning('Review keyword rescan stopped: segment '.($this->segment + 1).' is already queued.');

            return;
        }

        static::dispatch($this->rescan, $this->segment + 1, $this->leaseStart);
    }

    protected function nextSegmentIsQueued(): bool
    {
        $lockKey = UniqueLock::getKey(new RescanReviewKeywords(null, $this->segment + 1));

        $acquired = rescue(
            fn (): bool => Cache::lock($lockKey, 5)->get(fn (): bool => true),
            true,
            report: false
        );

        return ! $acquired;
    }
}
