<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Petition;
use App\Services\PageContentService;
use App\Support\CacheKeys;
use App\Support\Settings;
use App\Support\TranslationJoin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $locale = app()->getLocale();
        $defaultLocale = default_locale();

        $content = app(PageContentService::class)->getPageWithFallback('home', $locale, $defaultLocale);

        $excludedIds = [];
        if (! empty($content['exclude_most_read'])) {
            $excludedIds = array_filter(
                array_map('trim', explode(',', $content['exclude_most_read']))
            );
        }

        $categories = cache()->remember(CacheKeys::homeCategories($locale), 43200, function () use ($locale, $defaultLocale) {
            return Category::select(['categories.id', 'categories.sort_order'])
                ->translated($locale, 'ct_locale', 'ct_default', ['name' => 'tr_name', 'slug' => 'tr_slug'], $defaultLocale)
                ->where('categories.is_active', true)
                ->orderBy('categories.sort_order')
                ->get();
        });

        $recentActivities = cache()->remember(CacheKeys::homeRecent($locale), 300, function () use ($locale, $defaultLocale) {
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

        $recentActivities = array_map(fn ($r) => is_array($r) ? (object) $r : $r, (array) $recentActivities);

        $maxFeatured = (int) Settings::get('max_featured_petitions_per_country', 5, 'global');
        if ($maxFeatured < 1) {
            $maxFeatured = 5;
        }

        $pool = cache()->remember(CacheKeys::homePool($locale), 300, function () use ($locale, $defaultLocale, $excludedIds, $maxFeatured) {
            return Petition::select(['petitions.id'])
                ->translated($locale, 'pt_locale', 'pt_default', [], $defaultLocale)
                ->whereNotNull('pt_locale.title')
                ->where('pt_locale.is_featured', 1)
                ->where('petitions.status', 'published')
                ->where('petitions.is_active', 1)
                ->whereNotIn('petitions.id', $excludedIds)
                ->orderByDesc('petitions.signature_count')
                ->limit($maxFeatured)
                ->pluck('petitions.id')
                ->toArray();
        });

        $usingFallbackPool = false;
        if (empty($pool) && $locale !== $defaultLocale) {
            $usingFallbackPool = true;
            $pool = cache()->remember(CacheKeys::homePool($defaultLocale), 300, function () use ($defaultLocale, $excludedIds, $maxFeatured) {
                return Petition::select(['petitions.id'])
                    ->leftJoin('petition_translations as pt_default', function ($join) use ($defaultLocale) {
                        $join->on('pt_default.petition_id', '=', 'petitions.id')
                            ->where('pt_default.locale', '=', $defaultLocale);
                    })
                    ->whereNotNull('pt_default.title')
                    ->where('pt_default.is_featured', 1)
                    ->where('petitions.status', 'published')
                    ->where('petitions.is_active', 1)
                    ->whereNotIn('petitions.id', $excludedIds)
                    ->orderByDesc('petitions.signature_count')
                    ->limit($maxFeatured)
                    ->pluck('petitions.id')
                    ->toArray();
            });
        }

        $featuredPetition = null;

        if (! empty($pool)) {
            $featuredPetition = cache()->remember(CacheKeys::homeFeatured($locale), 60, function () use ($locale, $defaultLocale, $pool) {
                $featuredId = $pool[(int) floor(time() / 60) % count($pool)];

                return Petition::select(['petitions.*'])
                    ->translated($locale, 'pt_locale', 'pt_default', ['title' => 'tr_title', 'slug' => 'tr_slug', 'description' => 'tr_description'], $defaultLocale)
                    ->where('petitions.id', $featuredId)
                    ->first();
            });
        }

        $magazinePosts = cache()->remember(CacheKeys::homeMagazinePosts(), 1800, function () {
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

        $magazinePosts = array_map(fn ($r) => is_array($r) ? (object) $r : $r, (array) $magazinePosts);

        return view('pages.home', compact(
            'content',
            'categories',
            'featuredPetition',
            'recentActivities',
            'magazinePosts'
        ));
    }
}
