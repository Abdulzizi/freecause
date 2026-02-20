<?php

namespace App\Support;

class Locale
{
    public static function toFull(string $locale): string
    {
        $supported = config('locales.supported', []);
        $default   = config('locales.default', 'en');
        $map       = config('language_flags', []);

        $locale = strtolower(trim($locale));

        // if not supported → fallback
        if (!in_array($locale, $supported)) {
            $locale = $default;
        }

        return $map[$locale] ?? ($map[$default] ?? 'en_US');
    }

    public static function default(): string
    {
        return config('locales.default', 'en');
    }

    public static function supported(): array
    {
        return config('locales.supported', []);
    }
}
