<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EdgeCacheHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! $request->routeIs(...config('edge-cache.routes', []))) {
            return $response;
        }

        // The current web stack is sessionful; never strip its cookies or cache
        // its HTML.
        $response->headers->set('Cache-Control', 'private, no-store');
        $response->headers->set('X-FreeCause-Edge-Cache', 'bypass');

        return $response;
    }
}
