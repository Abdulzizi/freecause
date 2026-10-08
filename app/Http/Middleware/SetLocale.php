<?php

namespace App\Http\Middleware;

use App\Support\Locale;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\URL;

class SetLocale
{
    /** Set once a first-time visitor has been auto-redirected to their browser language. */
    private const REDIRECT_COOKIE = 'locale_redirected_to';

    public function handle(Request $request, Closure $next)
    {
        $locale = strtolower((string) $request->route('locale'));
        $allowedLocales = self::allowedLocales();
        $defaultLocale = self::defaultLocale();

        $mappedLocale = $this->mapLegacyLocale($locale, $allowedLocales);
        if ($mappedLocale !== null) {
            return $this->redirectPreservingPath($request, $mappedLocale, 301);
        }

        if (! in_array($locale, $allowedLocales)) {
            $browserLocale = $this->detectBrowserLanguage($request, $allowedLocales);
            $locale = $browserLocale ?? $defaultLocale;

            return $this->redirectPreservingPath($request, $locale, 302);
        }

        if ($autoRedirect = $this->autoRedirectToBrowserLocale($request, $locale)) {
            return $autoRedirect;
        }

        App::setLocale($locale);
        URL::defaults(['locale' => $locale]);

        session(['locale' => $locale]);

        return $next($request);
    }

    /**
     * Legacy parity (gtranslate on the old site): a first-time visitor landing on
     * the site root is sent to the language their browser asks for, unless that
     * language is the default one. The root-only scope means an explicit
     * /{locale}/... link is never hijacked, and the cookie keeps it to one
     * redirect for 30 days instead of fighting the visitor on every page.
     *
     * Public so the unprefixed root route resolves the language in one hop rather
     * than bouncing / -> /{default} -> /{browser}.
     */
    public function autoRedirectToBrowserLocale(Request $request, ?string $currentLocale = null): ?RedirectResponse
    {
        if (! $request->isMethod('GET')
            || ! $request->routeIs('root', 'home')
            || $request->cookies->has(self::REDIRECT_COOKIE)
            || $request->header('X-Purpose') === 'prefetch'
            || preg_match('/bot|spider|slurp|crawl|facebook|preview/i', (string) $request->header('User-Agent'))
        ) {
            return null;
        }

        $defaultLocale = self::defaultLocale();
        $browserLocale = $this->detectBrowserLanguage($request, self::allowedLocales());

        if ($browserLocale === null || $browserLocale === $defaultLocale || $browserLocale === $currentLocale) {
            return null;
        }

        return redirect('/'.$browserLocale, 302)
            ->cookie(cookie(self::REDIRECT_COOKIE, $browserLocale, 2592000))
            ->header('Vary', 'Accept-Language');
    }

    private static function allowedLocales(): array
    {
        return Locale::supported();
    }

    private static function defaultLocale(): string
    {
        return Locale::default();
    }

    private function mapLegacyLocale(string $locale, array $allowedLocales): ?string
    {
        $short = self::legacyLocaleMap()[strtolower(str_replace('-', '_', $locale))] ?? null;

        return $short !== null && in_array($short, $allowedLocales, true) ? $short : null;
    }

    /**
     * Full legacy locale literals (it_IT, en_US, pt-BR, ...) mapped to the
     * short new-app locale they convert to.
     */
    public static function legacyLocaleMap(): array
    {
        $legacyMap = [];
        foreach (config('language_flags', []) as $short => $full) {
            $legacyMap[strtolower((string) $full)] = $short;
        }
        foreach (self::legacyLocaleAliases() as $full => $short) {
            $legacyMap[strtolower((string) $full)] = $short;
        }

        return $legacyMap;
    }

    private static function legacyLocaleAliases(): array
    {
        return [
            'da_DK' => 'da',
            'de_AT' => 'de',
            'de_CH' => 'de',
            'de_DE' => 'de',
            'el_GR' => 'el',
            'en_AU' => 'en',
            'en_CA' => 'en',
            'en_EU' => 'en',
            'en_GB' => 'en',
            'en_IE' => 'en',
            'en_IN' => 'en',
            'en_NZ' => 'en',
            'en_PH' => 'en',
            'en_US' => 'en',
            'es_AR' => 'es',
            'es_CO' => 'es',
            'es_ES' => 'es',
            'es_MX' => 'es',
            'es_PE' => 'es',
            'fr_CA' => 'fr',
            'fr_CH' => 'fr',
            'fr_FR' => 'fr',
            'it_CH' => 'it',
            'it_IT' => 'it',
            'nl_NL' => 'nl',
            'pl_PL' => 'pl',
            'pt_BR' => 'pt',
            'pt_PT' => 'pt',
            'ro_RO' => 'ro',
            'ru_RU' => 'ru',
            'sv_SE' => 'sv',
            'tr_TR' => 'tr',
        ];
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
