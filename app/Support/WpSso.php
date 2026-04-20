<?php

namespace App\Support;

class WpSso
{
    /**
     * Build a signed SSO login URL that passes through sso.php to set
     * the WordPress auth cookie before redirecting to $redirect.
     */
    public static function loginUrl(string $email, string $displayName, string $redirect): string
    {
        $secret = config('app.sso_secret');

        if (! $secret) {
            \Illuminate\Support\Facades\Log::warning('SSO: sso_secret not configured, skipping WordPress login');

            return $redirect;
        }

        try {
            $payload = base64_encode(json_encode([
                'e' => $email,
                'n' => $displayName,
                'x' => time() + 120, // 2-minute TTL
            ]));

            $sig = hash_hmac('sha256', $payload, $secret);

            return '/magazine/sso.php?'.http_build_query([
                'p' => $payload,
                's' => $sig,
                'r' => $redirect,
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('SSO: failed to generate login URL - '.$e->getMessage());

            return $redirect;
        }
    }

    /**
     * Build a URL that clears the WordPress session before redirecting to $redirect.
     */
    public static function logoutUrl(string $redirect): string
    {
        return '/magazine/sso.php?'.http_build_query([
            'action' => 'logout',
            'r' => $redirect,
        ]);
    }
}
