<?php

namespace App\Http\Controllers;

abstract class Controller
{
    /**
     * Sanitize a relative redirect destination. Accepts only same-site
     * paths (no scheme-relative `//`, no backslashes, no control chars).
     */
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
}
