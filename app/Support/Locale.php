<?php

namespace App\Support;

use App\Models\Language;

class Locale
{
    public static function toFull(string $locale): string
    {
        $locale = strtolower(trim($locale));

        $flags = config('language_flags');
        $default = self::default();

        if (in_array($locale, self::supported(), true)) {
            return $flags[$locale] ?? $flags[$default] ?? 'en_US';
        }

        return $flags[$default] ?? 'en_US';
    }

    public static function toShort(?string $fullLocale): string
    {
        if (! $fullLocale) {
            return self::default();
        }

        $fullLocale = trim($fullLocale);

        $flags = config('language_flags', []);

        if (array_key_exists($fullLocale, $flags)) {
            return $fullLocale;
        }

        $map = array_flip($flags);

        return $map[$fullLocale] ?? self::default();
    }

    public static function supported(): array
    {
        return cache()->remember(
            'active_languages',
            300,
            fn () => Language::where('is_active', true)->pluck('code')->toArray()
        );
    }

    public static function options(): array
    {
        return Language::where('is_active', true)
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->pluck('name', 'code')
            ->toArray();
    }

    public static function default(): string
    {
        return cache()->remember(
            'default_language',
            300,
            fn () => Language::where('is_default', true)->value('code') ?? config('locales.default', 'en')
        );
    }
}
