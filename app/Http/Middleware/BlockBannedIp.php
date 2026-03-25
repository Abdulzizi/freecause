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

        $isBanned = cache()->remember("banned_ip:{$ip}", 300, fn() => BannedIp::where('ip', $ip)->exists());

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
