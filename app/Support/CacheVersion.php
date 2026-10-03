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
