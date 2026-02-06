<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\App;
use App\Models\PageContent;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
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

            $view->with('navbarContent', $navbarContent);
        });

        View::composer('partials.navbar', function ($view) {
            $locale = app()->getLocale();

            $pageContent = cache()->remember(
                "page_contents:$locale",
                now()->addHours(6),
                fn() => \App\Models\PageContent::where('locale', $locale)->pluck('value', 'key')
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

            $view->with(compact('pageContent', 'categories'));
        });
    }
}
