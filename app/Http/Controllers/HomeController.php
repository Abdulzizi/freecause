<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\PageContent;
use App\Models\Petition;
use App\Support\Settings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $locale = app()->getLocale();
        $defaultLocale = default_locale();

        $cacheKey = "home:content:{$locale}";
        $content = cache()->get($cacheKey);

        if ($content === null) {
            $lockKey = "lock:{$cacheKey}";
            $lock = cache()->lock($lockKey, 10);

            try {
                $lock->block(2);
                $content = cache()->remember($cacheKey, 1800, function () use ($locale, $defaultLocale) {
                    $c = PageContent::where('page', 'home')
                        ->where('locale', $locale)
                        ->pluck('value', 'key');

                    if ($c->isEmpty()) {
                        $c = PageContent::where('page', 'home')
                            ->where('locale', $defaultLocale)
                            ->pluck('value', 'key');
                    }

                    return $c;
                });
                $lock->release();
            } catch (\Exception $e) {
                $content = cache()->remember($cacheKey, 1800, function () use ($locale, $defaultLocale) {
                    $c = PageContent::where('page', 'home')
                        ->where('locale', $locale)
                        ->pluck('value', 'key');

                    if ($c->isEmpty()) {
                        $c = PageContent::where('page', 'home')
                            ->where('locale', $defaultLocale)
                            ->pluck('value', 'key');
                    }

                    return $c;
                });
            }
        }

        $excludedIds = [];
        if (! empty($content['exclude_most_read'])) {
            $excludedIds = array_filter(
                array_map('trim', explode(',', $content['exclude_most_read']))
            );
        }

        $categories = cache()->remember("home:categories:{$locale}", 43200, function () use ($locale, $defaultLocale) {
            return Category::select([
                'categories.id',
                'categories.sort_order',
                DB::raw('COALESCE(ct_locale.name, ct_default.name) as tr_name'),
                DB::raw('COALESCE(ct_locale.slug, ct_default.slug) as tr_slug'),
            ])
                ->leftJoin('category_translations as ct_locale', function ($join) use ($locale) {
                    $join->on('ct_locale.category_id', '=', 'categories.id')
                        ->where('ct_locale.locale', '=', $locale);
                })
                ->leftJoin('category_translations as ct_default', function ($join) use ($defaultLocale) {
                    $join->on('ct_default.category_id', '=', 'categories.id')
                        ->where('ct_default.locale', '=', $defaultLocale);
                })
                ->where('categories.is_active', true)
                ->orderBy('categories.sort_order')
                ->get();
        });

        // phpredis can deserialize Eloquent collection items as arrays — normalise back to objects
        $categories = collect(array_map(fn ($c) => is_array($c) ? (object) $c : $c, $categories->all()));

        $recentActivities = cache()->remember("home:recent:{$locale}", 300, function () use ($locale, $defaultLocale) {
            return DB::select("
                SELECT s.*, p.id as petition_id,
                    COALESCE(pt_locale.title, pt_default.title) as petition_title,
                    COALESCE(pt_locale.slug, pt_default.slug) as petition_slug
                FROM signatures s FORCE INDEX (signatures_created_at_index)
                JOIN petitions p ON p.id = s.petition_id AND p.status = 'published' AND p.is_active = 1
                LEFT JOIN petition_translations pt_locale ON pt_locale.petition_id = p.id AND pt_locale.locale = ?
                LEFT JOIN petition_translations pt_default ON pt_default.petition_id = p.id AND pt_default.locale = ?
                WHERE pt_locale.title IS NOT NULL OR pt_default.title IS NOT NULL
                ORDER BY s.created_at DESC
                LIMIT 10
            ", [$locale, $defaultLocale]);
        });

        // phpredis can deserialize DB::select() stdClass results as arrays — normalise back to objects
        $recentActivities = array_map(fn ($r) => is_array($r) ? (object) $r : $r, (array) $recentActivities);

        $maxFeatured = (int) Settings::get('max_featured_petitions_per_country', 5, 'global');
        if ($maxFeatured < 1) {
            $maxFeatured = 5;
        }

        $slot = (int) floor(time() / 60);

        $pool = cache()->remember("home:pool:{$locale}", 300, function () use ($locale, $defaultLocale, $excludedIds, $maxFeatured) {
            return Petition::select(['petitions.id'])
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
                ->where('petitions.is_featured', 1)
                ->whereNotIn('petitions.id', $excludedIds)
                ->orderByDesc('petitions.signature_count')
                ->limit($maxFeatured)
                ->pluck('petitions.id')
                ->toArray();
        });

        $featuredPetition = null;

        if (! empty($pool)) {
            $featuredId = $pool[$slot % count($pool)];

            $featuredPetition = cache()->remember("home:featured:{$locale}:{$slot}", 65, function () use ($locale, $defaultLocale, $featuredId) {
                return Petition::select([
                    'petitions.*',
                    DB::raw('COALESCE(pt_locale.title, pt_default.title) as tr_title'),
                    DB::raw('COALESCE(pt_locale.slug, pt_default.slug) as tr_slug'),
                    DB::raw('COALESCE(pt_locale.description, pt_default.description) as tr_description'),
                ])
                    ->leftJoin('petition_translations as pt_locale', function ($join) use ($locale) {
                        $join->on('pt_locale.petition_id', '=', 'petitions.id')
                            ->where('pt_locale.locale', '=', $locale);
                    })
                    ->leftJoin('petition_translations as pt_default', function ($join) use ($defaultLocale) {
                        $join->on('pt_default.petition_id', '=', 'petitions.id')
                            ->where('pt_default.locale', '=', $defaultLocale);
                    })
                    ->where('petitions.id', $featuredId)
                    ->first();
            });
        }

        $magazinePosts = cache()->remember('home:magazine_posts', 1800, function () {
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
                \Illuminate\Support\Facades\Log::error('Magazine DB connection failed: '.$e->getMessage());

                return [];
            }
        });

        // phpredis can deserialize DB::select() stdClass results as arrays — normalise back to objects
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
