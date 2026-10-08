<?php

namespace App\Services;

use App\Support\CacheVersion;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * The single write path for petition signature counts.
 *
 * Every raw write to petitions.signature_count goes through here so the listing
 * caches are always invalidated and the per-petition signature cache forgotten.
 * Count changes affect listings (cards show counts) but not the sitemap, so only
 * the listing groups are bumped.
 */
class PetitionStats
{
    public static function increment(int $petitionId, int $by = 1): void
    {
        self::incrementMany([$petitionId => $by]);
    }

    /** @param array<int, int> $byPetitionId petition id => amount */
    public static function incrementMany(array $byPetitionId): void
    {
        $touched = [];

        foreach ($byPetitionId as $id => $by) {
            $id = (int) $id;
            $by = (int) $by;

            if ($id <= 0 || $by <= 0) {
                continue;
            }

            DB::table('petitions')->where('id', $id)->increment('signature_count', $by);
            $touched[] = $id;
        }

        self::invalidate($touched);
    }

    /** Recompute the canonical count (confirmed signatures) for one petition. */
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

    /** @param array<int, int|string> $petitionIds */
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

    /** @param array<int, int> $petitionIds */
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
