<?php

namespace App\Services;

use App\Models\Petition;
use App\Support\CacheVersion;
use App\Support\ReviewKeywords;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReviewKeywordRescan
{
    public const CHUNK_SIZE = 200;

    public const LOG_TYPE = 'petition.review';

    public const PAYLOAD_LIMIT = 60000;

    public const DEFAULT_MAX_SECONDS = 120;

    public static int $chunkSize = self::CHUNK_SIZE;

    /**
     * @param  callable(int): (bool|void)|null  $onProgress  called with the last scanned id after every chunk, return false to stop the scan
     * @return array{scanned: int, held: int, lastId: int, complete: bool, failedChunks: int}
     */
    public function run(?int $limit = null, int $maxSeconds = self::DEFAULT_MAX_SECONDS, ?int $afterId = null, ?callable $onProgress = null): array
    {
        $scanned = 0;
        $held = 0;
        $failedChunks = 0;
        $lastId = max(0, (int) $afterId);
        $deadline = microtime(true) + max(1, $maxSeconds);
        $complete = true;

        while (true) {
            if (microtime(true) >= $deadline) {
                $complete = ! $this->hasRemaining($lastId);

                break;
            }

            $size = max(1, (int) self::$chunkSize);

            if ($limit !== null) {
                $remaining = $limit - $scanned;

                if ($remaining <= 0) {
                    $complete = ! $this->hasRemaining($lastId);

                    break;
                }

                $size = min($size, $remaining);
            }

            $petitions = Petition::query()
                ->where('status', 'published')
                ->where('id', '>', $lastId)
                ->orderBy('id')
                ->with(['translations' => fn ($query) => $query->orderBy('id')])
                ->limit($size)
                ->get();

            if ($petitions->isEmpty()) {
                break;
            }

            try {
                $chunk = $this->holdChunk($petitions);
            } catch (\Throwable $e) {
                Log::warning('Review keyword rescan chunk failed at petition '.(int) $petitions->last()->id.': '.$e->getMessage());

                $scanned += $petitions->count();
                $failedChunks++;
                $complete = false;

                break;
            }

            $scanned += $chunk['scanned'];
            $held += $chunk['held'];
            $lastId = (int) $petitions->last()->id;

            if ($onProgress !== null && $onProgress($lastId) === false) {
                $complete = ! $this->hasRemaining($lastId);

                break;
            }

            if (microtime(true) >= $deadline) {
                $complete = ! $this->hasRemaining($lastId);

                break;
            }
        }

        if (! $complete) {
            Log::warning('Review keyword rescan stopped early: scanned '.$scanned.', held '.$held.', last id '.$lastId.'.');
        }

        if ($held > 0) {
            try {
                CacheVersion::bumpAll();
            } catch (\Throwable $e) {
                Log::warning('Cache bump failed after review keyword rescan: '.$e->getMessage());
            }
        }

        return [
            'scanned' => $scanned,
            'held' => $held,
            'lastId' => $lastId,
            'complete' => $complete,
            'failedChunks' => $failedChunks,
        ];
    }

    /**
     * @param  Collection<int, Petition>  $petitions
     * @return array{scanned: int, held: int}
     */
    protected function holdChunk(Collection $petitions): array
    {
        $scanned = 0;
        $candidates = [];

        foreach ($petitions as $petition) {
            $scanned++;

            if ($petition->status !== 'published') {
                continue;
            }

            try {
                $matches = $this->matchFields($petition);

                if ($matches === []) {
                    continue;
                }

                $candidates[] = [
                    'id' => (int) $petition->id,
                    'title' => $this->payloadTitle($petition),
                    'matches' => $matches,
                ];
            } catch (\Throwable $e) {
                Log::warning('Review keyword rescan skipped petition '.(int) $petition->id.': '.$e->getMessage());
            }
        }

        if ($candidates === []) {
            return ['scanned' => $scanned, 'held' => 0];
        }

        $held = DB::transaction(function () use ($candidates): int {
            $rows = DB::table('petitions')
                ->whereIn('id', array_column($candidates, 'id'))
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $ids = [];
            $logs = [];

            foreach ($candidates as $candidate) {
                $row = $rows->get($candidate['id']);

                if ($row === null || $row->status !== 'published') {
                    continue;
                }

                $ids[] = $candidate['id'];

                $payload = (string) json_encode([
                    'petition_id' => $candidate['id'],
                    'title' => $candidate['title'],
                    'keywords' => $candidate['matches'],
                ], JSON_INVALID_UTF8_SUBSTITUTE);

                $logs[] = [
                    'type' => self::LOG_TYPE,
                    'ip' => '0.0.0.0',
                    'payload' => mb_strcut(mb_scrub($payload, 'UTF-8'), 0, self::PAYLOAD_LIMIT, 'UTF-8'),
                    'created_at' => now(),
                ];
            }

            if ($ids === []) {
                return 0;
            }

            DB::table('petitions')->whereIn('id', $ids)->update([
                'status' => 'draft',
                'updated_at' => now(),
            ]);

            DB::table('spam_logs')->insert($logs);

            return count($ids);
        });

        return ['scanned' => $scanned, 'held' => $held];
    }

    protected function hasRemaining(int $lastId): bool
    {
        return Petition::query()
            ->where('status', 'published')
            ->where('id', '>', $lastId)
            ->exists();
    }

    /**
     * @return array<int, string>
     */
    protected function matchFields(Petition $petition): array
    {
        $matches = [];

        foreach ($petition->translations as $translation) {
            $fields = [
                (string) $translation->title,
                strip_tags((string) $translation->description),
            ];

            foreach ($fields as $field) {
                if (trim($field) === '') {
                    continue;
                }

                foreach (ReviewKeywords::matches($field) as $match) {
                    $matches[$match] = $match;
                }
            }
        }

        return array_values($matches);
    }

    protected function payloadTitle(Petition $petition): string
    {
        $translations = $petition->translations;

        if ($translations->isEmpty()) {
            return '';
        }

        $default = $translations->firstWhere('locale', default_locale());

        return (string) ($default?->title ?? $translations->first()?->title);
    }
}
