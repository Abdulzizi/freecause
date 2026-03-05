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
            return config('language_flags')[$locale] ?? config('language_flags')[config('locales.default')] ?? 'en_US';
        }

        $default = config('locales.default', 'en');

        return config('language_flags')[$default] ?? 'en_US';
    }

    /**
     * Convert full locale (en_US) → short (en)
     */
    public static function toShort(?string $fullLocale): string
    {
        if (!$fullLocale) {
            return self::default();
        }

        $fullLocale = trim($fullLocale);

        // flip config: full => short
        $map = array_flip(config('language_flags', []));

        return $map[$fullLocale] ?? self::default();
    }

    public static function supported(): array
    {
        return Language::where('is_active', true)->pluck('code')->toArray();
    }

    public static function default(): string
    {
        return Language::where('is_default', true)->value('code') ?? config('locales.default', 'en');
    }
}
