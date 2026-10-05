<?php

namespace App\Support;

use Illuminate\Support\Str;

class Slug
{
    /**
     * Create a slug that is not already taken according to the supplied callback.
     *
     * The callback receives each candidate slug and must return true when that
     * slug is already in use. The first available candidate is returned.
     */
    public static function dedupe(string $value, callable $exists, string $fallback = 'item'): string
    {
        $base = Str::slug($value);

        if ($base === '') {
            $base = Str::slug($fallback) ?: 'item';
        }

        $slug = $base;
        $suffix = 1;

        while ($exists($slug)) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }
}
