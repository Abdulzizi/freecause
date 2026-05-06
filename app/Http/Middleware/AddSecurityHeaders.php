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

        // Restrict browser features
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=()');

        // Admin routes get a permissive CSP (inline scripts/styles needed for the panel).
        // Public routes get a strict CSP — no unsafe-inline on scripts.
        if ($request->is('admin') || $request->is('admin/*')) {
            $csp =
                "default-src 'self'; ".
                "script-src 'self' 'unsafe-inline' 'unsafe-eval' ".
                    'https://cdn.jsdelivr.net https://cdnjs.cloudflare.com '.
                    'https://ajax.googleapis.com https://accounts.google.com '.
                    'https://cdn.ckeditor.com; '.
                "style-src 'self' 'unsafe-inline' ".
                    'https://cdn.jsdelivr.net https://cdnjs.cloudflare.com '.
                    'https://ajax.googleapis.com; '.
                "img-src 'self' data: blob: https:; ".
                "font-src 'self' https://cdnjs.cloudflare.com; ".
                "frame-src 'self' https://accounts.google.com; ".
                "connect-src 'self' https://www.googleapis.com https://cdn.jsdelivr.net; ".
                "object-src 'none'; ".
                "base-uri 'self';";
        } else {
            $csp =
                "default-src 'self'; ".
                "script-src 'self' ".
                    'https://cdn.jsdelivr.net https://cdnjs.cloudflare.com '.
                    'https://ajax.googleapis.com https://accounts.google.com; '.
                "style-src 'self' 'unsafe-inline' ".
                    'https://cdn.jsdelivr.net https://cdnjs.cloudflare.com '.
                    'https://ajax.googleapis.com; '.
                "img-src 'self' data: blob: https:; ".
                "font-src 'self' https://cdnjs.cloudflare.com; ".
                "frame-src 'self' https://accounts.google.com; ".
                "connect-src 'self' https://www.googleapis.com https://cdn.jsdelivr.net; ".
                "object-src 'none'; ".
                "base-uri 'self';".
                'upgrade-insecure-requests;';
        }
        $response->headers->set('Content-Security-Policy', $csp);

        return $response;
    }
}
