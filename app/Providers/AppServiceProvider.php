<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Config;
use App\Models\PageContent;
use App\Support\Settings;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        if ($customBase = Settings::get('base_url')) {
            // URL::forceRootUrl($customBase);
        }

        $locale = request()->segment(1);

        if (in_array($locale, ['en', 'fr', 'it'])) {
            App::setLocale($locale);
            URL::defaults(['locale' => $locale]);
        } else {
            URL::defaults(['locale' => 'en']);
        }

        View::composer('partials.navbar', function ($view) {
            $locale = app()->getLocale();

            $navbarContent = PageContent::query()
                ->where('page', 'navbar')
                ->where('locale', $locale)
                ->pluck('value', 'key')
                ->toArray();

            $pageContent = cache()->remember(
                "page_contents:$locale",
                now()->addHours(6),
                fn() => PageContent::where('locale', $locale)->pluck('value', 'key')
            );

            $categories = cache()->remember(
                "categories:list:$locale",
                now()->addHours(12),
                fn() => \App\Models\Category::query()
                    ->join('category_translations as ct', function ($j) use ($locale) {
                        $j->on('ct.category_id', '=', 'categories.id')
                          ->where('ct.locale', '=', $locale);
                    })
                    ->orderBy('categories.id')
                    ->get(['categories.id', 'ct.name as name', 'ct.slug as slug'])
            );

            $view->with(compact('navbarContent', 'pageContent', 'categories'));
        });

        if (Settings::get('smtp_enabled', false)) {
            Config::set('mail.mailers.smtp.host', Settings::get('smtp_host'));
            Config::set('mail.mailers.smtp.port', (int) Settings::get('smtp_port'));
            Config::set('mail.mailers.smtp.username', Settings::get('smtp_user'));
            Config::set('mail.mailers.smtp.password', Settings::get('smtp_pass'));
            Config::set('mail.mailers.smtp.encryption', Settings::get('smtp_encryption'));
        }

        // Google
        Config::set('services.google.client_id', Settings::get('google_client_id'));
        Config::set('services.google.client_secret', Settings::get('google_client_secret'));
        Config::set(
            'services.google.redirect',
            url(app()->getLocale() . '/oauth/google/callback')
        );

        // Facebook
        Config::set('services.facebook.client_id', Settings::get('facebook_app_id'));
        Config::set('services.facebook.client_secret', Settings::get('facebook_secret'));
        Config::set(
            'services.facebook.redirect',
            url(app()->getLocale() . '/oauth/facebook/callback')
        );
    }
}
