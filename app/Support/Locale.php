<?php

namespace App\Support;

use App\Models\Language;

class Locale
{
    public static function toFull(string $locale): string
    {
        $locale = strtolower(trim($locale));

        $language = Language::where('code', $locale)->where('is_active', true)->first();

        if ($language) {
            return config('language_flags')[$locale]
                ?? config('language_flags')[config('locales.default')]
                ?? 'en_US';
        }

        $default = config('locales.default', 'en');

        return config('language_flags')[$default] ?? 'en_US';
    }

    public static function supported(): array
    {
        return Language::where('is_active', true)->pluck('code')->toArray();
    }

    public static function default(): string
    {
        return Language::where('is_default', true) ->value('code') ?? config('locales.default', 'en');
    }
}
