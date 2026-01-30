<?php

use Illuminate\Support\Facades\Route;

if (! function_exists('lroute')) {
    function lroute(string $name, array $params = [], bool $absolute = true): string
    {
        $params = array_merge(['locale' => app()->getLocale()], $params);

        return route($name, $params, $absolute);
    }
}

if (! function_exists('locale_url')) {
    function locale_url(string $newLocale): string
    {
        $route = Route::current();
        $routeName = $route?->getName();

        // fallback: go to home if route is unnamed
        if (! $routeName) {
            return url("/{$newLocale}");
        }

        // keep current route params, replace locale
        $params = $route->parameters();
        $params['locale'] = $newLocale;

        // keep query string (?page=2 etc)
        return route($routeName, $params) . (request()->getQueryString()
            ? '?' . request()->getQueryString()
            : '');
    }
}
