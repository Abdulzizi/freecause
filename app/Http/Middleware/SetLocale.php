<?php

namespace App\Http\Middleware;

use App\Models\Language;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\URL;

class SetLocale
{
    public function handle(Request $request, Closure $next)
    {
        $locale = $request->route('locale');
        $allowedLocales = cache()->remember('active_languages', 300, fn () => Language::where('is_active', 1)->pluck('code')->toArray());
        $defaultLocale = cache()->remember('default_language', 300, fn () => Language::where('is_default', 1)->value('code') ?? 'en');

        if (! in_array($locale, $allowedLocales)) {
            $browserLocale = $this->detectBrowserLanguage($request, $allowedLocales);
            $locale = $browserLocale ?? $defaultLocale;

            return redirect("/{$locale}");
        }

        App::setLocale($locale);
        URL::defaults(['locale' => $locale]);

        session(['locale' => $locale]);

        return $next($request);
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
