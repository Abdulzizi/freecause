<?php

namespace App\Services;

use App\Mail\PetitionMilestoneMail;
use App\Models\Petition;
use App\Models\User;
use App\Support\AppLog;
use App\Support\CacheVersion;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class PetitionStats
{
    public const MILESTONES = [100, 1000, 10000, 100000];

    public static function incrementMany(array $byPetitionId): array
    {
        $touched = [];
        $previous = [];

        foreach ($byPetitionId as $id => $by) {
            $id = (int) $id;
            $by = (int) $by;

            if ($id <= 0 || $by <= 0) {
                continue;
            }

            $previous[$id] = DB::transaction(function () use ($id, $by) {
                $value = (int) DB::table('petitions')->where('id', $id)->lockForUpdate()->value('signature_count');
                DB::table('petitions')->where('id', $id)->increment('signature_count', $by);

                return $value;
            });
            $touched[] = $id;
        }

        self::invalidate($touched);

        return $previous;
    }

    /**
     * Notify the owner for every milestone CROSSED when the signature count
     * moved from $previousCount to its current value.
     */
    public static function notifyMilestoneIfCrossed(int $petitionId, int $previousCount): void
    {
        try {
            $count = (int) DB::table('petitions')->where('id', $petitionId)->value('signature_count');

            $crossed = array_filter(self::MILESTONES, fn (int $m): bool => $m > $previousCount && $m <= $count);

            if ($crossed === []) {
                return;
            }

            $petition = Petition::with('user')->find($petitionId);
            $owner = $petition?->user;

            if (! $owner instanceof User || ! $owner->wantsMilestoneEmails()) {
                return;
            }

            $milestone = max($crossed);
            $lock = Cache::lock("milestone:{$petitionId}:{$milestone}", 600);
            if (! $lock->get()) {
                return;
            }

            DB::afterCommit(fn () => Mail::to($owner->email)->queue(new PetitionMilestoneMail($petition, $owner, $milestone)));

            AppLog::info(
                'Milestone email queued',
                'Petition ID: '.$petitionId.' | Milestone: '.$milestone,
                'petition.milestone'
            );
        } catch (\Throwable $e) {
            AppLog::error(
                'Milestone email failed',
                'Petition ID: '.$petitionId.' | '.$e->getMessage(),
                'petition.milestone'
            );
        }
    }

    public static function recalculate(int $petitionId): int
    {
        return self::recalculateMany([$petitionId])[$petitionId] ?? 0;
    }

    public static function recalculateMany(array $petitionIds): array
    {
        $counts = [];

        foreach (array_unique(array_map('intval', $petitionIds)) as $id) {
            if ($id <= 0) {
                continue;
            }

            $count = DB::table('signatures')
                ->where('petition_id', $id)
                ->where('confirmed', 1)
                ->where('is_spam', 0)
                ->count();

            DB::table('petitions')->where('id', $id)->update(['signature_count' => $count]);
            $counts[$id] = $count;
        }

        DB::afterCommit(fn () => CacheVersion::bumpListings());
        self::invalidate(array_keys($counts));

        return $counts;
    }

    public static function reconcileAll(): void
    {
        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::update(<<<'SQL'
                UPDATE petitions AS p
                LEFT JOIN (
                    SELECT petition_id, COUNT(*) AS actual_count
                    FROM signatures
                    WHERE confirmed = 1 AND is_spam = 0
                    GROUP BY petition_id
                ) AS s ON s.petition_id = p.id
                SET p.signature_count = COALESCE(s.actual_count, 0)
                WHERE p.signature_count != COALESCE(s.actual_count, 0)
            SQL);
        } else {
            DB::table('petitions')->update([
                'signature_count' => DB::raw(
                    '(SELECT COUNT(*) FROM signatures WHERE signatures.petition_id = petitions.id AND signatures.confirmed = 1 AND signatures.is_spam = 0)'
                ),
            ]);
        }

        CacheVersion::bumpListings();
    }

    private static function invalidate(array $petitionIds): void
    {
        if ($petitionIds === []) {
            return;
        }

        // Bump listing caches at most once a minute instead of on every signature,
        // after commit so a reader can't recache the old count.
        DB::afterCommit(function () {
            if (Cache::add('listings:bump', 1, 60)) {
                CacheVersion::bumpListings();
            }
        });

        foreach ($petitionIds as $id) {
            Cache::forget("petition:latest_sigs:{$id}");
        }
    }
}
