<?php

namespace App\Http\Middleware;

use App\Models\Language;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\URL;

class SetLocale
{
    public function handle(Request $request, Closure $next)
    {
        $locale = strtolower((string) $request->route('locale'));
        $allowedLocales = cache()->remember('active_languages', 300, fn () => Language::where('is_active', 1)->pluck('code')->toArray());
        $defaultLocale = cache()->remember('default_language', 300, fn () => Language::where('is_default', 1)->value('code') ?? 'en');

        $mappedLocale = $this->mapLegacyLocale($locale, $allowedLocales);
        if ($mappedLocale !== null) {
            return $this->redirectPreservingPath($request, $mappedLocale, 301);
        }

        if (! in_array($locale, $allowedLocales)) {
            $browserLocale = $this->detectBrowserLanguage($request, $allowedLocales);
            $locale = $browserLocale ?? $defaultLocale;

            return $this->redirectPreservingPath($request, $locale, 302);
        }

        App::setLocale($locale);
        URL::defaults(['locale' => $locale]);

        session(['locale' => $locale]);

        return $next($request);
    }

    private function mapLegacyLocale(string $locale, array $allowedLocales): ?string
    {
        $legacyMap = [];
        foreach (config('language_flags', []) as $short => $full) {
            $legacyMap[strtolower((string) $full)] = $short;
        }

        $short = $legacyMap[strtolower(str_replace('-', '_', $locale))] ?? null;

        return $short !== null && in_array($short, $allowedLocales, true) ? $short : null;
    }

    private function redirectPreservingPath(Request $request, string $locale, int $status): RedirectResponse
    {
        $segments = explode('/', $request->path());
        array_shift($segments);

        $target = '/'.$locale.(count($segments) > 0 ? '/'.implode('/', $segments) : '');

        $query = $request->getQueryString();
        if ($query) {
            $target .= '?'.$query;
        }

        return redirect($target, $status);
    }

    private function detectBrowserLanguage(Request $request, array $allowedLocales): ?string
    {
        $acceptLanguage = $request->header('Accept-Language');

        if (! $acceptLanguage) {
            return null;
        }

        $preferredLanguages = [];
        foreach (explode(',', $acceptLanguage) as $lang) {
            $lang = trim(explode(';', $lang)[0]);
            $lang = substr($lang, 0, 2);
            if ($lang) {
                $preferredLanguages[] = $lang;
            }
        }

        foreach ($preferredLanguages as $lang) {
            if (in_array($lang, $allowedLocales)) {
                return $lang;
            }
        }

        foreach ($preferredLanguages as $lang) {
            foreach ($allowedLocales as $allowed) {
                if (str_starts_with($allowed, $lang)) {
                    return $allowed;
                }
            }
        }

        return null;
    }
}
