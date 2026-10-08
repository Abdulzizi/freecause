<?php

namespace App\Support;

/**
 * Cache invalidation for raw (non-Eloquent) structural petition writes.
 *
 * Raw DB::table('petitions') writes bypass the Eloquent observers, so the write
 * sites must invalidate the caches the observer would have bumped. Signature
 * counts are handled separately by App\Services\PetitionStats.
 */
class PetitionCache
{
    /**
     * Bump the listings and the sitemap after a structural change (create,
     * delete, visibility, slug/title).
     */
    public static function bump(): void
    {
        CacheVersion::bumpAll();
    }
}
