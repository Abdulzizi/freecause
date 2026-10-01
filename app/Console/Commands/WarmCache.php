<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Language;
use App\Models\PageContent;
use App\Models\Petition;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

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
            return PageContent::where('page', 'home')
                ->where('locale', $locale)
                ->pluck('value', 'key')
                ->toArray();
        });

        Cache::remember("home:categories:{$locale}", 43200, function () use ($locale, $defaultLocale) {
            return Category::query()
                ->select(['categories.id'])
                ->withTranslation([
                    'base' => 'categories',
                    'base_key' => 'id',
                    'translation_table' => 'category_translations',
                    'translation_key' => 'category_id',
                    'locale_alias' => 'ct_locale',
                    'default_alias' => 'ct_default',
                    'locale' => $locale,
                    'default_locale' => $defaultLocale,
                    'columns' => ['name' => 'name'],
                ])
                ->where('categories.is_active', true)
                ->orderBy('name')
                ->get()
                ->toArray();
        });

        Cache::remember("home:recent:{$locale}", 300, function () use ($locale) {
            return Petition::query()
                ->select(['petitions.id', 'petitions.signature_count'])
                ->withTranslation([
                    'base' => 'petitions',
                    'base_key' => 'id',
                    'translation_table' => 'petition_translations',
                    'translation_key' => 'petition_id',
                    'locale_alias' => 'pt_locale',
                    'default_alias' => 'pt_default',
                    'locale' => $locale,
                    'default_locale' => 'en',
                    'columns' => ['title' => 'title'],
                ])
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
                    ])
                    ->withTranslation([
                        'base' => 'petitions',
                        'base_key' => 'id',
                        'translation_table' => 'petition_translations',
                        'translation_key' => 'petition_id',
                        'locale_alias' => 'pt_locale',
                        'default_alias' => 'pt_default',
                        'locale' => $locale,
                        'default_locale' => $defaultLocale,
                        'columns' => ['title' => 'tr_title', 'slug' => 'tr_slug'],
                    ])
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
                ->withTranslation([
                    'base' => 'categories',
                    'base_key' => 'id',
                    'translation_table' => 'category_translations',
                    'translation_key' => 'category_id',
                    'locale_alias' => 'ct_locale',
                    'default_alias' => 'ct_default',
                    'locale' => $locale,
                    'default_locale' => $defaultLocale,
                    'columns' => ['name' => 'name'],
                ])
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
