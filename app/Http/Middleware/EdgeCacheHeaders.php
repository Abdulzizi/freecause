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

        $cacheable = $request->attributes->get('stateless_render') === true
            && $request->isMethod('GET')
            && $request->cookies->count() === 0
            && ! $request->headers->has('Cookie')
            && ! $request->headers->has('Authorization')
            && $request->getQueryString() === null
            && $response->getStatusCode() === 200
            && str_starts_with(strtolower($response->headers->get('Content-Type', '')), 'text/html')
            && count($response->headers->getCookies()) === 0
            && ! $response->headers->has('Vary');

        if ($cacheable) {
            $ttl = max(1, min(900, (int) config('edge-cache.ttl', 600)));
            $response->headers->set('Cache-Control', "public, max-age=0, s-maxage={$ttl}");
            $response->headers->set('X-FreeCause-Edge-Cache', 'eligible');
        } else {
            $response->headers->set('Cache-Control', 'private, no-store');
            $response->headers->set('X-FreeCause-Edge-Cache', 'bypass');
        }

        return $response;
    }
}
