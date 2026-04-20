<?php

namespace App\Providers;

use App\Models\Language;
use App\Models\PageContent;
use App\Support\Settings;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        try {
            if (! Schema::hasTable('settings')) {
                $this->bootLocale();
                $this->bootViewComposers();

                return;
            }
        } catch (\Throwable $e) {
            Log::error('AppServiceProvider boot failed: '.$e->getMessage());

            return;
        }

        $this->bootLocale();
        $this->bootViewComposers();
        $this->bootMail();
        $this->bootOAuth();
        $this->bootRateLimiting();
    }

    private function bootRateLimiting(): void
    {
        try {
            RateLimiter::for('petitions', function (Request $request) {
                return Limit::perMinute(10)->by($request->ip());
            });

            RateLimiter::for('signatures', function (Request $request) {
                return Limit::perMinute(20)->by($request->ip());
            });

            RateLimiter::for('api', function (Request $request) {
                return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
            });
        } catch (\Throwable $e) {
            Log::warning('bootRateLimiting failed: '.$e->getMessage());
        }
    }

    private function bootLocale(): void
    {
        try {
            $segmentLocale = request()->segment(1);

            $availableLocales = Cache::remember(
                'languages:codes',
                60,
                fn () => Schema::hasTable('languages')
                    ? Language::where('is_active', 1)->pluck('code')->toArray()
                    : []
            );

            $defaultLocale = Cache::remember(
                'default_language',
                60,
                fn () => Schema::hasTable('languages')
                    ? Language::where('is_default', 1)->value('code') ?? 'en'
                    : 'en'
            );

            if (in_array($segmentLocale, $availableLocales)) {
                App::setLocale($segmentLocale);
                URL::defaults(['locale' => $segmentLocale]);
            } else {
                App::setLocale($defaultLocale);
                URL::defaults(['locale' => $defaultLocale]);
            }

            try {
                $baseUrl = Settings::get('base_url', '', 'global');
                if (! empty($baseUrl)) {
                    URL::forceRootUrl(rtrim($baseUrl, '/'));
                    if (str_starts_with($baseUrl, 'https://')) {
                        URL::forceScheme('https');
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('bootLocale: could not apply base_url: '.$e->getMessage());
            }
        } catch (\Throwable $e) {
            Log::error('bootLocale failed, falling back to en: '.$e->getMessage());
            App::setLocale('en');
            URL::defaults(['locale' => 'en']);
        }
    }

    private function bootViewComposers(): void
    {
        try {
            $shortBaseUrl = Settings::get('short_base_url', '', 'global');
            View::share('shortBaseUrl', $shortBaseUrl ?: config('app.url'));
        } catch (\Throwable $e) {
            Log::warning('bootViewComposers: could not load short_base_url: '.$e->getMessage());
            View::share('shortBaseUrl', config('app.url'));
        }

        View::composer('partials.navbar', function ($view) {
            try {
                $locale = app()->getLocale();

                $defaultLocale = Cache::remember(
                    'default_language',
                    60,
                    fn () => Language::where('is_default', 1)->value('code') ?? 'en'
                );

                $pageContent = Cache::remember(
                    "page_contents:$locale",
                    now()->addHours(6),
                    fn () => PageContent::where('locale', $locale)->pluck('value', 'key')
                );

                $categories = Cache::remember(
                    "categories:list:$locale",
                    now()->addHours(12),
                    function () use ($locale, $defaultLocale) {
                        return \App\Models\Category::query()
                            ->leftJoin('category_translations as ct_locale', function ($j) use ($locale) {
                                $j->on('ct_locale.category_id', '=', 'categories.id')
                                    ->where('ct_locale.locale', '=', $locale);
                            })
                            ->leftJoin('category_translations as ct_default', function ($j) use ($defaultLocale) {
                                $j->on('ct_default.category_id', '=', 'categories.id')
                                    ->where('ct_default.locale', '=', $defaultLocale);
                            })
                            ->where('categories.is_active', true)
                            ->orderBy('categories.id')
                            ->get([
                                'categories.id',
                                \DB::raw('COALESCE(ct_locale.name, ct_default.name) as name'),
                                \DB::raw('COALESCE(ct_locale.slug, ct_default.slug) as slug'),
                            ]);
                    }
                );

                $view->with([
                    'pageContent' => $pageContent,
                    'navbarContent' => $pageContent,
                    'categories' => $categories,
                ]);
            } catch (\Throwable $e) {
                Log::warning('navbar composer failed: '.$e->getMessage());
                $view->with([
                    'pageContent' => collect(),
                    'categories' => collect(),
                    'navbarContent' => collect(),
                ]);
            }
        });
    }

    private function bootMail(): void
    {
        try {
            if (! Settings::get('smtp_enabled', false)) {
                return;
            }

            $encryption = Settings::get('smtp_encryption', 'tls');
            $host = Settings::get('smtp_host', '');
            $user = Settings::get('smtp_user', '');
            $pass = Settings::get('smtp_pass', '');
            $fromEmail = Settings::get('email_from', config('mail.from.address'));

            if (empty($host)) {
                return;
            }

            $port = match ($encryption) {
                'tls' => 587,
                'ssl' => 465,
                default => 25,
            };

            Config::set('mail.default', 'smtp');
            Config::set('mail.mailers.smtp.host', $host);
            Config::set('mail.mailers.smtp.port', $port);
            Config::set('mail.mailers.smtp.username', $user);
            Config::set('mail.mailers.smtp.password', $pass);
            Config::set('mail.mailers.smtp.encryption', $encryption ?: null);
            Config::set('mail.from.address', $fromEmail ?: config('mail.from.address'));
            Config::set('mail.from.name', config('app.name'));
        } catch (\Throwable $e) {
            Log::warning('bootMail failed: '.$e->getMessage());
        }
    }

    private function bootOAuth(): void
    {
        try {
            $locale = app()->getLocale();

            Config::set('services.google.client_id', Settings::get('google_client_id', ''));
            Config::set('services.google.client_secret', Settings::get('google_client_secret', ''));
            Config::set('services.google.redirect', url("/{$locale}/oauth/google/callback"));
        } catch (\Throwable $e) {
            Log::warning('bootOAuth Google failed: '.$e->getMessage());
        }

    }
}
