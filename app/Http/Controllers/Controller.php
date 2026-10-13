<?php

namespace App\Http\Controllers;

abstract class Controller
{
    public static function sanitizeRedirectPath(?string $dest, string $fallback): string
    {
        if ($dest !== null && $dest !== ''
            && str_starts_with($dest, '/')
            && ! str_starts_with($dest, '//')
            && preg_match('#^[A-Za-z0-9/\-._~?&=,%+:!$()*\'@]*$#', $dest) === 1) {
            return $dest;
        }

        return $fallback;
    }

    public static function profileCompletionTarget(string $locale, string $dest): string
    {
        $locale = normalize_locale($locale);
        $profile = "/{$locale}/profile";
        $dest = trim($dest);

        if ($dest === '' || $dest === "/{$locale}" || $dest === $profile) {
            return $profile;
        }

        return $profile.'?'.http_build_query(['return' => $dest]);
    }
}
