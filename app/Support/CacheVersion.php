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
     * Bump the shared listing groups (home, listings, sitemap) that depend on
     * petition/category/translation changes.
     */
    public static function bumpListings(): void
    {
        self::bump('home');
        self::bump('list');
        self::bump('sitemap');
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
