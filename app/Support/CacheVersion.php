<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;

class CacheVersion
{
    public static function get(string $group): string
    {
        $key = "ver:{$group}";

        try {
            if (! Cache::has($key)) {
                Cache::forever($key, 1);
            }

            return (string) Cache::get($key);
        } catch (\Throwable) {
            return 'x';
        }
    }

    /**
     * Bump the listing groups whose content (cards, counts, ordering) changes.
     * Also drives the queued edge-listing purge via the 'list' group.
     */
    public static function bumpListings(): void
    {
        self::bump('home');
        self::bump('list');
    }

    /**
     * Bump the sitemap group. Only structural changes (added/removed/renamed or
     * published/unpublished URLs) need this; signature counts do not.
     */
    public static function bumpSitemap(): void
    {
        self::bump('sitemap');
    }

    /**
     * Bump every content group: listings plus the sitemap.
     */
    public static function bumpAll(): void
    {
        self::bumpListings();
        self::bumpSitemap();
    }

    public static function bump(string $group): void
    {
        if ($group === 'list') {
            EdgeCachePurge::listings();
        }

        $key = "ver:{$group}";

        try {
            if (! Cache::has($key)) {
                Cache::forever($key, 1);
            }

            Cache::increment($key);
        } catch (\Throwable) {
        }
    }
}
