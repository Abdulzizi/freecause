<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AddSecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Enforce HTTPS for 1 year
        $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');

        // Prevent clickjacking
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // Prevent MIME sniffing
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // CSP — restrictive policy for production security
        // Note: If scripts fail after removing unsafe-inline/eval, consider implementing nonce-based script loading
        $response->headers->set('Content-Security-Policy',
            "default-src 'self'; ".
            "script-src 'self' ".
                'https://cdn.jsdelivr.net https://cdnjs.cloudflare.com '.
                'https://ajax.googleapis.com https://accounts.google.com; '.
            "style-src 'self' ".
                'https://cdn.jsdelivr.net https://cdnjs.cloudflare.com '.
                'https://ajax.googleapis.com; '.
            "img-src 'self' data: blob: https:; ".
            "font-src 'self' https://cdnjs.cloudflare.com; ".
            "frame-src 'self' https://accounts.google.com; ".
            "connect-src 'self' https://www.googleapis.com https://cdn.jsdelivr.net; ".
            "object-src 'none'; ".
            "base-uri 'self';".
            'upgrade-insecure-requests;'
        );

        return $response;
    }
}
