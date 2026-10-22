<?php

namespace App\Http\Middleware;

use App\Models\BannedIp;
use Closure;
use Illuminate\Support\Facades\DB;

class BlockBannedIp
{
    public function handle($request, Closure $next)
    {
        $ip = $request->ip();

        try {
            $isBanned = cache()->remember("banned_ip:{$ip}", 300, fn () => BannedIp::where('ip', $ip)->exists());
        } catch (\Throwable) {
            $isBanned = false;
        }

        if ($isBanned) {
            try {
                DB::table('spam_logs')->insert([
                    'type' => 'blocked',
                    'ip' => $ip,
                    'payload' => 'Attempted access while banned',
                    'created_at' => now(),
                ]);
            } catch (\Throwable) {
            }

            abort(403);
        }

        return $next($request);
    }
}
