<?php

use App\Models\PetitionTranslation;
use Illuminate\Support\Facades\Route;

if (! function_exists('lroute')) {
    function lroute(string $name, array $params = [], bool $absolute = true): string
    {
        // Try multiple sources for locale
        $locale = $params['locale']
            ?? session('locale')
            ?? request()->segment(1)
            ?? app()->getLocale()
            ?? 'en';

        // Validate locale
        $allowedLocales = ['en', 'fr', 'it', 'es', 'de', 'pt', 'nl'];
        if (!in_array($locale, $allowedLocales)) {
            $locale = 'en';
        }

        $params = array_merge(['locale' => $locale], $params);

        return route($name, $params, $absolute);
    }
}

if (! function_exists('locale_url')) {
    function locale_url(string $newLocale): string
    {
        $route = Route::current();
        $routeName = $route?->getName();

        if (! $routeName) {
            return url("/{$newLocale}");
        }

        $params = $route->parameters();
        $params['locale'] = $newLocale;

        if (isset($params['id']) && (isset($params['slug']) || str_contains($routeName, 'petition.'))) {
            $id = (int) $params['id'];

            $tr = PetitionTranslation::query()
                ->where('petition_id', $id)
                ->where('locale', $newLocale)
                ->first()
                ?? PetitionTranslation::query()->where('petition_id', $id)->orderBy('id')->first();

            if ($tr) {
                $params['slug'] = $tr->slug;
                $params['locale'] = $tr->locale;
            }
        }

        return route($routeName, $params) . (request()->getQueryString()
            ? '?' . request()->getQueryString()
            : '');
    }
}
