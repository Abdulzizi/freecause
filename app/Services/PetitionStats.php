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

    public static function increment(int $petitionId, int $by = 1): void
    {
        self::incrementMany([$petitionId => $by]);
    }

    /**
     * Increment signature counts in bulk. Returns a map of petitionId =>
     * previousCount (the count BEFORE this increment) so callers can detect
     * milestones crossed and send notifications AFTER their transaction
     * commits — notification must never fire inside a transaction that may
     * roll back.
     *
     * @param  array<int,int>  $byPetitionId
     * @return array<int,int>
     */
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

            $previous[$id] = (int) DB::table('petitions')->where('id', $id)->value('signature_count');
            DB::table('petitions')->where('id', $id)->increment('signature_count', $by);
            $touched[] = $id;
        }

        self::invalidate($touched);

        return $previous;
    }

    /**
     * Notify the owner for every milestone CROSSED when the signature count
     * moved from $previousCount to its current value. Passing null for
     * $previousCount preserves the legacy exact-equality behaviour (fire only
     * when the current count lands exactly on a milestone).
     */
    public static function notifyMilestoneIfCrossed(int $petitionId, ?int $previousCount = null): void
    {
        try {
            $count = (int) DB::table('petitions')->where('id', $petitionId)->value('signature_count');

            $crossed = array_values(array_filter(self::MILESTONES, function (int $m) use ($count, $previousCount): bool {
                if ($previousCount === null) {
                    return $m === $count;
                }

                // Any milestone strictly above the old count and at/below the new one.
                return $m > $previousCount && $m <= $count;
            }));

            if ($crossed === []) {
                return;
            }

            $petition = Petition::with('user')->find($petitionId);
            $owner = $petition?->user;

            if (! $owner instanceof User || ! $owner->wantsMilestoneEmails()) {
                return;
            }

            // Notify for the highest milestone crossed (one email per transition).
            $milestone = max($crossed);

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
        $count = DB::table('signatures')
            ->where('petition_id', $petitionId)
            ->where('confirmed', 1)
            ->count();

        DB::table('petitions')->where('id', $petitionId)->update(['signature_count' => $count]);

        self::invalidate([$petitionId]);

        return $count;
    }

    public static function recalculateMany(array $petitionIds): void
    {
        $touched = [];

        foreach (array_unique(array_map('intval', $petitionIds)) as $id) {
            if ($id <= 0) {
                continue;
            }

            $count = DB::table('signatures')
                ->where('petition_id', $id)
                ->where('confirmed', 1)
                ->count();

            DB::table('petitions')->where('id', $id)->update(['signature_count' => $count]);
            $touched[] = $id;
        }

        self::invalidate($touched);
    }

    private static function invalidate(array $petitionIds): void
    {
        if ($petitionIds === []) {
            return;
        }

        CacheVersion::bumpListings();

        foreach ($petitionIds as $id) {
            Cache::forget("petition:latest_sigs:{$id}");
        }
    }
}
