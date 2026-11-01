<?php

namespace App\Console\Commands;

use App\Http\Controllers\Admin\DashboardController;
use App\Models\Category;
use App\Models\Language;
use App\Models\Petition;
use App\Services\PageContentService;
use App\Support\CacheKeys;
use App\Support\TranslationJoin;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WarmCache extends Command
{
    protected $signature = 'cache:warm {--locale= : Specific locale to warm}';

    protected $description = 'Warm up application caches for faster first-page loads';

    public function handle(): int
    {
        $startTime = microtime(true);

        $defaultLocale = default_locale();

        $locales = $this->option('locale')
            ? [normalize_locale($this->option('locale'))]
            : Language::where('is_active', 1)->pluck('code')->toArray();

        $this->info('Warming cache for locales: '.implode(', ', $locales));

        foreach ($locales as $locale) {
            $this->warmLocaleCache($locale, $defaultLocale);
        }

        $this->warmSharedCache();

        $elapsed = round((microtime(true) - $startTime) * 1000);
        $this->info("Cache warming complete in {$elapsed}ms.");

        return 0;
    }

    private function warmLocaleCache(string $locale, string $defaultLocale): void
    {
        $this->line("  Warming {$locale}...");

        $this->warmHomeContent($locale);
        $this->warmHomeCategories($locale, $defaultLocale);
        $this->warmHomeRecent($locale, $defaultLocale);
        $this->warmHomeMagazine();
        $this->warmPetitionsCache($locale, $defaultLocale);
    }

    private function warmHomeContent(string $locale): void
    {
        app(PageContentService::class)->getPage('home', $locale);
    }

    private function warmHomeCategories(string $locale, string $defaultLocale): void
    {
        Cache::remember(CacheKeys::homeCategories($locale), 43200, function () use ($locale, $defaultLocale) {
            return Category::select(['categories.id', 'categories.sort_order'])
                ->withTranslation([
                    'base' => 'categories',
                    'base_key' => 'id',
                    'translation_table' => 'category_translations',
                    'translation_key' => 'category_id',
                    'locale_alias' => 'ct_locale',
                    'default_alias' => 'ct_default',
                    'locale' => $locale,
                    'default_locale' => $defaultLocale,
                    'columns' => ['name' => 'tr_name', 'slug' => 'tr_slug'],
                ])
                ->where('categories.is_active', true)
                ->orderBy('categories.sort_order')
                ->get();
        });
    }

    private function warmHomeRecent(string $locale, string $defaultLocale): void
    {
        Cache::remember(CacheKeys::homeRecent($locale), 300, function () use ($locale, $defaultLocale) {
            $tr = TranslationJoin::raw([
                'base' => 'p',
                'base_key' => 'id',
                'translation_table' => 'petition_translations',
                'translation_key' => 'petition_id',
                'locale_alias' => 'pt_locale',
                'default_alias' => 'pt_default',
                'locale' => $locale,
                'default_locale' => $defaultLocale,
                'columns' => ['title' => 'petition_title', 'slug' => 'petition_slug'],
            ]);

            // live display name per signer identify_mode; guest falls back to frozen s.name
            $displayExpr = "COALESCE(CASE u.identify_mode
                    WHEN 'name' THEN COALESCE(NULLIF(u.first_name, ''), u.name)
                    WHEN 'nick' THEN COALESCE(NULLIF(u.nickname, ''), u.name)
                    ELSE u.name END, s.name) AS display_name";

            if (in_array(DB::connection()->getDriverName(), ['mysql', 'mariadb'], true)) {
                return DB::select("
                    SELECT s.*, p.id as petition_id, {$displayExpr},
                        {$tr['selects'][0]},
                        {$tr['selects'][1]}
                    FROM signatures s FORCE INDEX (signatures_created_at_index)
                    STRAIGHT_JOIN petitions p ON p.id = s.petition_id AND p.status = 'published' AND p.is_active = 1
                    LEFT JOIN users u ON u.id = s.user_id
                    {$tr['locale_join']}
                    {$tr['default_join']}
                    WHERE s.confirmed = 1 AND s.is_spam = 0
                    AND (pt_locale.title IS NOT NULL OR pt_default.title IS NOT NULL)
                    ORDER BY s.created_at DESC
                    LIMIT 10
                ", $tr['bindings']);
            }

            return DB::select("
                SELECT s.*, p.id as petition_id, {$displayExpr},
                    {$tr['selects'][0]},
                    {$tr['selects'][1]}
                FROM signatures s
                JOIN petitions p ON p.id = s.petition_id AND p.status = 'published' AND p.is_active = 1
                LEFT JOIN users u ON u.id = s.user_id
                {$tr['locale_join']}
                {$tr['default_join']}
                WHERE s.confirmed = 1 AND s.is_spam = 0
                    AND (pt_locale.title IS NOT NULL OR pt_default.title IS NOT NULL)
                ORDER BY s.created_at DESC
                LIMIT 10
            ", $tr['bindings']);
        });
    }

    private function warmHomeMagazine(): void
    {
        Cache::remember(CacheKeys::homeMagazinePosts(), 1800, function () {
            try {
                return DB::connection('magazine')->select("
                    SELECT
                        p.ID,
                        p.post_title,
                        p.post_name,
                        p.post_date,
                        pm_thumb.meta_value AS thumbnail_path
                    FROM abi_posts p
                    LEFT JOIN abi_postmeta pm_tid  ON pm_tid.post_id  = p.ID AND pm_tid.meta_key  = '_thumbnail_id'
                    LEFT JOIN abi_postmeta pm_thumb ON pm_thumb.post_id = pm_tid.meta_value AND pm_thumb.meta_key = '_wp_attached_file'
                    WHERE p.post_status = 'publish' AND p.post_type = 'post'
                    ORDER BY p.post_date DESC
                    LIMIT 3
                ");
            } catch (\Exception $e) {
                Log::error('Magazine DB connection failed: '.$e->getMessage());

                return [];
            }
        });
    }

    private function warmPetitionsCache(string $locale, string $defaultLocale): void
    {
        for ($page = 1; $page <= 10; $page++) {
            Cache::remember(CacheKeys::petitionsIndex($locale, $page), 300, function () use ($locale, $page) {
                $petitions = Petition::query()
                    ->select('petitions.id')
                    ->join('petition_translations as pt_locale', function ($join) use ($locale) {
                        $join->on('pt_locale.petition_id', '=', 'petitions.id')
                            ->where('pt_locale.locale', '=', $locale);
                    })
                    ->whereNotNull('pt_locale.title')
                    ->visible()
                    ->orderByDesc('petitions.id')
                    ->paginate(15, ['petitions.id'], 'page', $page);

                return ['ids' => $petitions->items(), 'total' => $petitions->total()];
            });
        }
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

        try {
            app(DashboardController::class)->warm();
        } catch (\Throwable) {
        }
    }
}
