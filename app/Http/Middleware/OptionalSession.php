<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Http\Request;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Symfony\Component\HttpFoundation\Response;

class OptionalSession
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->cookies->count() === 0
            && ! $request->headers->has('Authorization')
            && auth()->guest()) {
            $request->attributes->set('stateless_render', true);

            return $next($request);
        }

        return app(Pipeline::class)
            ->send($request)
            ->through([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                ShareErrorsFromSession::class,
                ValidateCsrfToken::class,
            ])
            ->then(fn (Request $request) => $next($request));
    }
}
