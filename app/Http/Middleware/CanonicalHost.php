<?php

namespace App\Http\Middleware;

use App\Support\Ads;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CanonicalHost
{
    public const PUBLIC_HOSTS = [
        'freecause.com',
        'www.freecause.com',
        'client.freecause.com',
        'www.client.freecause.com',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        if (Ads::frameHostMatchesRequest() && ! $request->is('ads/frame/*')) {
            abort(404);
        }

        $canonicalUrl = (string) config('app.canonical_url', 'https://www.freecause.com');
        $canonicalHost = strtolower((string) parse_url($canonicalUrl, PHP_URL_HOST));

        $host = strtolower($request->getHost());
        $adHosts = array_filter([
            'ads.freecause.com', // retired frame host that must keep 301-ing to canonical
            Ads::frameHost(),
            strtolower((string) parse_url((string) config('ads.frame_origin'), PHP_URL_HOST)),
        ]);
        $retiredAdHost = $host !== Ads::frameHost() && in_array($host, $adHosts, true);
        $isPublicDomain = $retiredAdHost || in_array($host, self::PUBLIC_HOSTS, true);

        if ($isPublicDomain && $host !== $canonicalHost) {
            $target = rtrim($canonicalUrl, '/').'/'.ltrim($request->path(), '/');

            $query = $request->getQueryString();
            if ($query) {
                $target .= '?'.$query;
            }

            return redirect($target, 301);
        }

        return $next($request);
    }
}
