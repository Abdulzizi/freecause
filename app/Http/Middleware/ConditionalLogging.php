<?php

namespace App\Http\Middleware;

use App\Support\Settings;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ConditionalLogging
{
    public function handle(Request $request, Closure $next)
    {
        if (self::isSpeculative($request)) {
            return $next($request);
        }

        try {
            $enabled = (bool) Settings::get('logging_enabled', false, 'global');
            $cookieName = Settings::get('logging_cookie_name', '', 'global');
            $cookieValue = Settings::get('logging_cookie_value', '', 'global');

            if ($enabled && ! empty($cookieName)) {
                $actual = $request->cookie($cookieName);

                $matches = empty($cookieValue)
                    ? ! is_null($actual)
                    : ($actual === $cookieValue);

                if ($matches) {
                    Log::channel('daily')->info('request', [
                        'ip' => $request->ip(),
                        'method' => $request->method(),
                        'url' => $request->fullUrl(),
                        'agent' => $request->userAgent(),
                    ]);
                }
            }
        } catch (\Throwable $e) {
            \Log::warning('ConditionalLogging middleware failed: '.$e->getMessage());
        }

        return $next($request);
    }

    public static function isSpeculative(Request $request): bool
    {
        $purpose = strtolower((string) $request->header('Sec-Purpose'));

        return str_contains($purpose, 'prefetch') || str_contains($purpose, 'prerender');
    }

    public static function isPrefetchOnly(Request $request): bool
    {
        $purpose = strtolower((string) $request->header('Sec-Purpose'));

        return str_contains($purpose, 'prefetch') && ! str_contains($purpose, 'prerender');
    }
}
