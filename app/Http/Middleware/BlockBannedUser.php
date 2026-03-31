<?php

namespace App\Http\Middleware;

use App\Models\UserLevel;
use Closure;

class BlockBannedUser
{
    public function handle($request, Closure $next)
    {
        if (auth()->check() && auth()->user()->hasLevel('banned')) {
            $user = auth()->user();

            // If ban has a fixed expiry and it has passed, auto-unban
            if ($user->banned_until !== null && now()->gt($user->banned_until)) {
                $userLevel = UserLevel::where('name', 'user')->value('id');
                $user->update([
                    'level_id'      => $userLevel,
                    'banned_reason' => null,
                    'banned_until'  => null,
                ]);
                return $next($request);
            }

            $locale = app()->getLocale();
            auth()->logout();

            return redirect("/{$locale}")
                ->withErrors(['account' => 'Your account has been suspended.']);
        }

        return $next($request);
    }
}
