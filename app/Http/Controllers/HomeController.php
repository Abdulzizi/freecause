<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\PageContent;
use App\Models\Petition;
use App\Models\Signature;
use App\Support\Settings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $locale        = app()->getLocale();
        $defaultLocale = default_locale();

        $content = cache()->remember("home:content:{$locale}", 1800, function () use ($locale, $defaultLocale) {
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

        $excludedIds = [];
        if (!empty($content['exclude_most_read'])) {
            $excludedIds = array_filter(
                array_map('trim', explode(',', $content['exclude_most_read']))
            );
        }

        $categories = cache()->remember("home:categories:{$locale}", 43200, function () use ($locale, $defaultLocale) {
            return Category::select([
                'categories.id',
                'categories.sort_order',
                DB::raw("COALESCE(ct_locale.name, ct_default.name) as tr_name"),
                DB::raw("COALESCE(ct_locale.slug, ct_default.slug) as tr_slug"),
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

        $recentActivities = cache()->remember("home:recent:{$locale}", 60, function () use ($locale, $defaultLocale) {
            return Signature::select([
                'signatures.*',
                'petitions.id as petition_id',
                DB::raw("COALESCE(pt_locale.title, pt_default.title) as petition_title"),
                DB::raw("COALESCE(pt_locale.slug, pt_default.slug) as petition_slug"),
            ])
                ->join('petitions', function ($join) {
                    $join->on('petitions.id', '=', 'signatures.petition_id')
                        ->where('petitions.status', 'published')
                        ->where('petitions.is_active', 1);
                })
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
                ->orderByDesc('signatures.created_at')
                ->limit(10)
                ->get();
        });

        $maxFeatured = (int) Settings::get('max_featured_petitions_per_country', 5, 'global');
        if ($maxFeatured < 1) $maxFeatured = 5;

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
                ->whereNotIn('petitions.id', $excludedIds)
                ->orderByDesc('petitions.signature_count')
                ->limit($maxFeatured)
                ->pluck('petitions.id')
                ->toArray();
        });

        $featuredPetition = null;

        if (!empty($pool)) {
            $featuredId = $pool[$slot % count($pool)];

            $featuredPetition = cache()->remember("home:featured:{$locale}:{$slot}", 65, function () use ($locale, $defaultLocale, $featuredId) {
                return Petition::select([
                    'petitions.*',
                    DB::raw("COALESCE(pt_locale.title, pt_default.title) as tr_title"),
                    DB::raw("COALESCE(pt_locale.slug, pt_default.slug) as tr_slug"),
                    DB::raw("COALESCE(pt_locale.description, pt_default.description) as tr_description"),
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

        return view('pages.home', compact(
            'content',
            'categories',
            'featuredPetition',
            'recentActivities'
        ));
    }
}