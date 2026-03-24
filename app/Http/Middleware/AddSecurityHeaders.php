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

        // CSP — allows inline scripts/styles (needed for inject_head_html/inject_body_html
        // and Quill editor), but blocks loading resources from untrusted external origins.
        // If the admin injects a <script src="evil.com/x.js">, this stops it from loading.
        // Inline scripts injected by a compromised admin can still run — that is intentional
        // by design (client wants full HTML control). The CSP limits the blast radius.
        $response->headers->set('Content-Security-Policy',
            "default-src 'self'; " .
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' " .
                "https://cdn.jsdelivr.net https://cdnjs.cloudflare.com " .
                "https://ajax.googleapis.com https://connect.facebook.net " .
                "https://accounts.google.com; " .
            "style-src 'self' 'unsafe-inline' " .
                "https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; " .
            "img-src 'self' data: blob: https:; " .
            "font-src 'self' https://cdnjs.cloudflare.com; " .
            "frame-src 'self' https://accounts.google.com https://www.facebook.com; " .
            "connect-src 'self'; " .
            "object-src 'none';"
        );

        return $response;
    }
}