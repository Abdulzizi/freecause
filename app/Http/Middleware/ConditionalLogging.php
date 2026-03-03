<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Support\Settings;
use Illuminate\Support\Facades\Log;

class ConditionalLogging
{
    public function handle(Request $request, Closure $next)
    {
        try {
            $enabled     = (bool) Settings::get('logging_enabled', false, 'global');
            $cookieName  = Settings::get('logging_cookie_name', '', 'global');
            $cookieValue = Settings::get('logging_cookie_value', '', 'global');

            if ($enabled && !empty($cookieName)) {
                $actual = $request->cookie($cookieName);

                $matches = empty($cookieValue)
                    ? !is_null($actual)
                    : ($actual === $cookieValue);

                if ($matches) {
                    Log::channel('daily')->info('request', [
                        'ip'     => $request->ip(),
                        'method' => $request->method(),
                        'url'    => $request->fullUrl(),
                        'agent'  => $request->userAgent(),
                    ]);
                }
            }
        } catch (\Throwable $e) {
            
        }

        return $next($request);
    }
}
