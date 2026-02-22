<?php

namespace App\Http\Middleware;

use Closure;

class BlockBannedUser
{
    public function handle($request, Closure $next)
    {
        if (auth()->check() && auth()->user()->hasLevel('banned')) {

            $locale = app()->getLocale();

            auth()->logout();

            return redirect("/{$locale}")
                ->withErrors(['account' => 'Your account has been suspended.']);
        }

        return $next($request);
    }
}
