<?php

use App\Models\Permission;
use App\Models\PetitionTranslation;
use App\Models\User;
use App\Support\HtmlSanitizer;
use App\Support\Locale;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;

if (! function_exists('active_locales')) {
    function active_locales(): array
    {
        return Locale::supported();
    }
}

if (! function_exists('default_locale')) {
    function default_locale(): string
    {
        return Locale::default();
    }
}

if (! function_exists('is_valid_locale')) {
    function is_valid_locale(string $locale): bool
    {
        return in_array($locale, Locale::supported(), true);
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
            ?? request()->route('locale')            ?? session('locale')
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

        foreach ($params as $param) {
            if (is_string($param) && strpbrk($param, '{}') !== false) {
                return url("/{$newLocale}");
            }
        }

        $params['locale'] = $newLocale;

        if (str_contains($routeName, 'petition.') && isset($params['id'], $params['slug'])) {
            $id = (int) $params['id'];

            $translations = request()->attributes->get('locale_url_translations', []);

            if (! array_key_exists($id, $translations)) {
                $translations[$id] = PetitionTranslation::query()
                    ->where('petition_id', $id)
                    ->get(['locale', 'slug'])
                    ->keyBy('locale');

                request()->attributes->set('locale_url_translations', $translations);
            }

            $tr = $translations[$id]->get($newLocale) ?? $translations[$id]->first();

            if ($tr) {
                $params['slug'] = $tr->slug;
                $params['locale'] = $tr->locale;
            }
        }

        return route($routeName, $params).
            (request()->getQueryString()
                ? '?'.request()->getQueryString()
                : '');
    }
}

if (! function_exists('canonical_url')) {
    function canonical_url(?string $path = null): string
    {
        $base = rtrim((string) config('app.canonical_url', 'https://www.freecause.com'), '/');

        $path = trim((string) ($path ?? request()->path()), '/');
        if ($path !== '') {
            $base .= '/'.$path;
        }

        return $base;
    }
}

function toast($message, $type = 'info')
{
    session()->flash('toast', [
        'type' => $type,
        'message' => $message,
    ]);
}

function sanitizePetitionHtml(string $html): string
{
    return trim(HtmlSanitizer::petition()->sanitize($html));
}
if (! function_exists('admin_user')) {
    function admin_user(): ?User
    {
        return Auth::guard('admin')->user();
    }
}

if (! function_exists('admin_can')) {
    function admin_can(string $module, string $action = 'view'): bool
    {
        $user = admin_user();
        if (! $user) {
            return false;
        }

        $user->loadMissing('level');
        if (! $user->level_id) {
            return false;
        }
        if ($user->level && $user->level->is_system) {
            return true;
        }

        return Cache::remember(
            "permissions:{$user->level_id}:{$module}:{$action}",
            300,
            fn () => Permission::where('level_id', $user->level_id)
                ->where('module', $module)
                ->where('action', $action)
                ->exists()
        );
    }
}

if (! function_exists('sanitizeAdminHtml')) {
    function sanitizeAdminHtml(?string $html): string
    {
        if ($html === null) {
            return '';
        }

        return trim(HtmlSanitizer::admin()->sanitize($html));
    }
}
