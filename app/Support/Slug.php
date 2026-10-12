<?php

namespace App\Support;

use Illuminate\Support\Str;

class Slug
{
    public static function dedupe(string $value, callable $exists, string $fallback = 'item', ?int $maxLength = null): string
    {
        $base = Str::slug($value);

        if ($base === '') {
            $base = Str::slug($fallback) ?: 'item';
        }

        if ($maxLength !== null && Str::length($base) > $maxLength) {
            $base = rtrim(Str::substr($base, 0, $maxLength), '-');
        }

        $slug = $base;
        $suffix = 1;

        while ($exists($slug)) {
            $suffixText = '-'.$suffix++;

            $candidate = $maxLength !== null && Str::length($base) + Str::length($suffixText) > $maxLength
                ? rtrim(Str::substr($base, 0, $maxLength - Str::length($suffixText)), '-').$suffixText
                : $base.$suffixText;

            $slug = $candidate;
        }

        return $slug;
    }
}
