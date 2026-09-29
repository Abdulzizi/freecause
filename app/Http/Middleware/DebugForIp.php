<?php

namespace App\Http\Middleware;

use App\Support\Settings;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class DebugForIp
{
    public function handle(Request $request, Closure $next)
    {
        if (app()->isProduction()) {
            return $next($request);
        }

        try {
            $debugIp = Settings::get('special_debug_ip', '', 'global');

            if (! empty($debugIp) && $request->ip() === trim($debugIp)) {
                Config::set('app.debug', true);
                \DB::enableQueryLog();
            }
        } catch (\Throwable $e) {
            Log::warning('DebugForIp middleware failed: '.$e->getMessage());
        }

        return $next($request);
    }
}
