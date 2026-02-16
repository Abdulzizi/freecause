<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BlockBannedUser
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next)
    {
        if (auth()->check() && auth()->user()->level === 'banned') {
            $locale = app()->getLocale();
            auth()->logout();

            return redirect("/{$locale}")
                ->withErrors(['account' => 'Your account has been suspended.']);
        }

        return $next($request);
    }
}
