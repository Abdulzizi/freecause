<?php

use App\Models\Language;
use App\Models\PetitionTranslation;
use Illuminate\Support\Facades\Route;

if (! function_exists('active_locales')) {
    function active_locales(): array
    {
        return cache()->remember(
            'active_languages',
            60,
            fn() => Language::where('is_active', 1)->pluck('code')->toArray()
        );
    }
}

if (! function_exists('default_locale')) {
    function default_locale(): string
    {
        return cache()->remember(
            'default_language',
            60,
            fn() => Language::where('is_default', 1)->value('code') ?? 'en'
        );
    }
}

if (! function_exists('is_valid_locale')) {
    function is_valid_locale(string $locale): bool
    {
        return in_array($locale, active_locales(), true);
    }
}

if (! function_exists('normalize_locale')) {
    function normalize_locale(?string $locale): string
    {
        $locale = $locale ?? default_locale();

        return is_valid_locale($locale)
            ? $locale
            : default_locale();
    }
}

if (! function_exists('lroute')) {
    function lroute(string $name, array $params = [], bool $absolute = true): string
    {
        $locale = $params['locale']
            ?? session('locale')
            ?? request()->segment(1)
            ?? app()->getLocale();

        $locale = normalize_locale($locale);

        $params = array_merge(['locale' => $locale], $params);

        return route($name, $params, $absolute);
    }
}

if (! function_exists('locale_url')) {
    function locale_url(string $newLocale): string
    {
        $newLocale = normalize_locale($newLocale);

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
                ?? PetitionTranslation::query()
                ->where('petition_id', $id)
                ->where('locale', default_locale())
                ->first();

            if ($tr) {
                $params['slug'] = $tr->slug;
                $params['locale'] = $tr->locale;
            }
        }

        return route($routeName, $params) .
            (request()->getQueryString()
                ? '?' . request()->getQueryString()
                : '');
    }
}

function base_url()
{
    return \App\Support\Settings::get('base_url', config('app.url'));
}
