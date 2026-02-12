<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\DB;

class BlockBannedIp
{
    public function handle($request, Closure $next)
    {
        $ip = $request->ip();

        $isBanned = DB::table('banned_ips')
            ->where('ip', $ip)
            ->exists();

        if ($isBanned) {
            DB::table('spam_logs')->insert([
                'type' => 'blocked',
                'ip' => $ip,
                'payload' => 'Attempted access while banned',
                'created_at' => now(),
            ]);

            abort(403);
        }

        return $next($request);
    }
}
