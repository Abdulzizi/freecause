<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Language;
use App\Models\Petition;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class WarmCache extends Command
{
    protected $signature = 'cache:warm {--locale= : Specific locale to warm}';

    protected $description = 'Warm up application caches for faster first-page loads';

    public function handle(): int
    {
        $startTime = microtime(true);

        $locales = Language::where('is_active', 1)->pluck('code')->toArray();

        $this->info('Warming cache for locales: '.implode(', ', $locales));

        foreach ($locales as $locale) {
            $this->warmLocaleCache($locale);
        }

        $this->warmSharedCache();

        $elapsed = round((microtime(true) - $startTime) * 1000);
        $this->info("Cache warming complete in {$elapsed}ms.");

        return 0;
    }

    private function warmLocaleCache(string $locale): void
    {
        $this->line("  Warming {$locale}...");

        $defaultLocale = config('app.fallback_locale', 'en');

        $this->warmHomePageCache($locale, $defaultLocale);
        $this->warmPetitionsCache($locale, $defaultLocale);
        $this->warmCategoriesCache($locale);
    }

    private function warmHomePageCache(string $locale, string $defaultLocale): void
    {
        Cache::remember("home:content:{$locale}", 1800, function () use ($locale) {
            return \App\Models\PageContent::where('page', 'home')
                ->where('locale', $locale)
                ->pluck('value', 'key')
                ->toArray();
        });

        Cache::remember("home:categories:{$locale}", 43200, function () use ($locale, $defaultLocale) {
            return Category::query()
                ->select(['categories.id'])
                ->selectRaw('COALESCE(ct_locale.name, ct_default.name) as name')
                ->leftJoin('category_translations as ct_locale', function ($join) use ($locale) {
                    $join->on('ct_locale.category_id', '=', 'categories.id')
                        ->where('ct_locale.locale', '=', $locale);
                })
                ->leftJoin('category_translations as ct_default', function ($join) use ($defaultLocale) {
                    $join->on('ct_default.category_id', '=', 'categories.id')
                        ->where('ct_default.locale', '=', $defaultLocale);
                })
                ->where('categories.is_active', true)
                ->orderBy('name')
                ->get()
                ->toArray();
        });

        Cache::remember("home:recent:{$locale}", 300, function () use ($locale) {
            return Petition::query()
                ->select(['petitions.id', 'petitions.signature_count'])
                ->selectRaw('COALESCE(pt_locale.title, pt_default.title) as title')
                ->leftJoin('petition_translations as pt_locale', function ($join) use ($locale) {
                    $join->on('pt_locale.petition_id', '=', 'petitions.id')
                        ->where('pt_locale.locale', '=', $locale);
                })
                ->leftJoin('petition_translations as pt_default', function ($join) {
                    $join->on('pt_default.petition_id', '=', 'petitions.id')
                        ->where('pt_default.locale', '=', 'en');
                })
                ->where('petitions.status', 'published')
                ->where('petitions.is_active', 1)
                ->orderByDesc('petitions.created_at')
                ->limit(10)
                ->get()
                ->toArray();
        });

        Cache::remember('home:magazine_posts', 1800, function () {
            return [];
        });
    }

    private function warmPetitionsCache(string $locale, string $defaultLocale): void
    {
        for ($page = 1; $page <= 10; $page++) {
            Cache::remember("petitions:index:{$locale}:page:{$page}", 300, function () use ($locale, $defaultLocale) {
                return Petition::query()
                    ->select([
                        'petitions.id',
                        'petitions.signature_count',
                        'petitions.goal_signatures',
                        'petitions.category_id',
                        'petitions.cover_image',
                        DB::raw('COALESCE(pt_locale.title, pt_default.title) as tr_title'),
                        DB::raw('COALESCE(pt_locale.slug, pt_default.slug) as tr_slug'),
                    ])
                    ->leftJoin('petition_translations as pt_locale', function ($join) use ($locale) {
                        $join->on('pt_locale.petition_id', '=', 'petitions.id')
                            ->where('pt_locale.locale', '=', $locale);
                    })
                    ->leftJoin('petition_translations as pt_default', function ($join) use ($defaultLocale) {
                        $join->on('pt_default.petition_id', '=', 'petitions.id')
                            ->where('pt_default.locale', '=', $defaultLocale);
                    })
                    ->where(function ($q) {
                        $q->whereNotNull('pt_locale.title')
                            ->orWhereNotNull('pt_default.title');
                    })
                    ->where('petitions.status', 'published')
                    ->where('petitions.is_active', 1)
                    ->orderByDesc('petitions.id')
                    ->paginate(15)
                    ->toArray();
            });
        }
    }

    private function warmCategoriesCache(string $locale): void
    {
        $defaultLocale = config('app.fallback_locale', 'en');

        Cache::remember("home:categories:{$locale}", 43200, function () use ($locale, $defaultLocale) {
            return Category::query()
                ->select(['categories.id'])
                ->selectRaw('COALESCE(ct_locale.name, ct_default.name) as name')
                ->leftJoin('category_translations as ct_locale', function ($join) use ($locale) {
                    $join->on('ct_locale.category_id', '=', 'categories.id')
                        ->where('ct_locale.locale', '=', $locale);
                })
                ->leftJoin('category_translations as ct_default', function ($join) use ($defaultLocale) {
                    $join->on('ct_default.category_id', '=', 'categories.id')
                        ->where('ct_default.locale', '=', $defaultLocale);
                })
                ->where('categories.is_active', true)
                ->orderBy('name')
                ->get()
                ->toArray();
        });
    }

    private function warmSharedCache(): void
    {
        $this->line('  Warming shared caches...');

        Cache::remember('active_languages', 300, function () {
            return Language::where('is_active', 1)->pluck('code')->toArray();
        });

        Cache::remember('default_language', 300, function () {
            return Language::where('is_default', 1)->value('code') ?? 'en';
        });

        Cache::remember('active_languages_full', 60, function () {
            return Language::where('is_active', 1)
                ->orderByDesc('is_default')
                ->get()
                ->toArray();
        });
    }
}
