<?php

namespace App\Support;

use App\Models\Petition;
use Illuminate\Support\Facades\Cache;

/**
 * Cache invalidation for raw (non-Eloquent) petition writes.
 *
 * Raw DB::table('petitions') updates/increments bypass the PetitionObserver,
 * so the write sites must invalidate the caches the observer would have
 * bumped. This mirrors PetitionObserver::saved() exactly and additionally
 * forgets the per-petition "latest signatures" cache.
 */
class PetitionCache
{
    /**
     * Bump the home/list/sitemap cache versions and forget the petition's
     * latest-signatures cache. Does NOT recount or correct signature counts.
     */
    public static function touch(Petition $petition): void
    {
        static::bump();

        Cache::forget("petition:latest_sigs:{$petition->id}");
    }

    /**
     * Bump the home/list/sitemap cache versions (same groups the
     * PetitionObserver bumps on save/delete).
     */
    public static function bump(): void
    {
        CacheVersion::bump('home');
        CacheVersion::bump('list');
        CacheVersion::bump('sitemap');
    }
}
