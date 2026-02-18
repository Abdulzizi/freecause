<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Cache;
use App\Models\PageContent;
use App\Models\Language;
use App\Support\Settings;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        if (!Schema::hasTable('settings')) {
            return;
        }


        if ($customBase = Settings::get('base_url')) {
            // URL::forceRootUrl($customBase);
        }


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


        View::composer('partials.navbar', function ($view) {

            $locale = app()->getLocale();

            $defaultLocale = Cache::remember(
                'default_language',
                60,
                fn () => Language::where('is_default', 1)->value('code') ?? 'en'
            );

            $navbarContent = PageContent::where('page', 'navbar')
                ->where('locale', $locale)
                ->pluck('value', 'key')
                ->toArray();

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
                            \DB::raw("COALESCE(ct_locale.name, ct_default.name) as name"),
                            \DB::raw("COALESCE(ct_locale.slug, ct_default.slug) as slug"),
                        ]);
                }
            );

            $view->with(compact('navbarContent', 'pageContent', 'categories'));
        });


        if (Settings::get('smtp_enabled', false)) {

            $encryption = Settings::get('smtp_encryption');

            $port = match ($encryption) {
                'tls' => 587,
                'ssl' => 465,
                default => 25,
            };

            Config::set('mail.default', 'smtp');
            Config::set('mail.mailers.smtp.host', Settings::get('smtp_host'));
            Config::set('mail.mailers.smtp.port', $port);
            Config::set('mail.mailers.smtp.username', Settings::get('smtp_user'));
            Config::set('mail.mailers.smtp.password', Settings::get('smtp_pass'));
            Config::set('mail.mailers.smtp.encryption', $encryption);
            Config::set('mail.from.address', Settings::get('email_from') ?: config('mail.from.address'));
            Config::set('mail.from.name', config('app.name'));
        }


        Config::set('services.google.client_id', Settings::get('google_client_id'));
        Config::set('services.google.client_secret', Settings::get('google_client_secret'));
        Config::set('services.google.redirect', url(app()->getLocale() . '/oauth/google/callback'));

        Config::set('services.facebook.client_id', Settings::get('facebook_app_id'));
        Config::set('services.facebook.client_secret', Settings::get('facebook_secret'));
        Config::set('services.facebook.redirect', url(app()->getLocale() . '/oauth/facebook/callback'));
    }
}
