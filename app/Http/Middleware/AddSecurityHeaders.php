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

        // CSP — tightened to block inline scripts, allows specific external CDNs
        // Admin inject HTML uses nonce-based allowances (see below)
        // Quill editor and Google OAuth require specific allowances
        $nonce = base64_encode(random_bytes(16));

        $response->headers->set('Content-Security-Policy',
            "default-src 'self'; ".
            "script-src 'self' 'nonce-{$nonce}' 'strict-dynamic' ".
                'https://cdn.jsdelivr.net https://cdnjs.cloudflare.com '.
                'https://ajax.googleapis.com https://accounts.google.com; '.
            "style-src 'self' 'nonce-{$nonce}' ".
                'https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; '.
            "img-src 'self' data: blob: https:; ".
            "font-src 'self' https://cdnjs.cloudflare.com; ".
            "frame-src 'self' https://accounts.google.com; ".
            "connect-src 'self' https://www.googleapis.com; ".
            "object-src 'none'; ".
            "base-uri 'self';"
        );

        // Pass nonce to views for admin-injected content
        view()->share('csp_nonce', $nonce);

        return $response;
    }
}
