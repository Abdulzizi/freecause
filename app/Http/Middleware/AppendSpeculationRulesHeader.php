<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AppendSpeculationRulesHeader
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! $request->isMethod('GET')) {
            return $response;
        }

        if ($response->getStatusCode() !== 200) {
            return $response;
        }

        if (! str_starts_with(strtolower($response->headers->get('Content-Type', '')), 'text/html')) {
            return $response;
        }

        if (str_starts_with($request->path(), 'admin')) {
            return $response;
        }

        if ($response->headers->has('Speculation-Rules')) {
            return $response;
        }

        $response->headers->set('Speculation-Rules', '"/speculationrules.json"');

        return $response;
    }
}
