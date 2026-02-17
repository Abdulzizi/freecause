<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Language;
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
        $locale = app()->getLocale();
        $defaultLocale = default_locale();

        $content = PageContent::where('page', 'home')
            ->where('locale', $locale)
            ->pluck('value', 'key');

        if ($content->isEmpty()) {
            $content = PageContent::where('page', 'home')
                ->where('locale', $defaultLocale)
                ->pluck('value', 'key');
        }

        $excludedIds = [];

        if (!empty($content['exclude_most_read'])) {
            $excludedIds = array_filter(
                array_map('trim', explode(',', $content['exclude_most_read']))
            );
        }

        // $categories = Category::select([
        //     'categories.id',
        //     'categories.sort_order',
        //     'ct.name as tr_name',
        //     'ct.slug as tr_slug',
        // ])
        //     ->join('category_translations as ct', function ($join) use ($locale) {
        //         $join->on('ct.category_id', '=', 'categories.id')
        //             ->where('ct.locale', '=', $locale);
        //     })
        //     ->where('categories.is_active', true)
        //     ->orderBy('categories.sort_order')
        //     ->get();

        $categories = Category::select([
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

        // $featuredPetition = Petition::select([
        //     'petitions.*',
        //     'pt.title as tr_title',
        //     'pt.slug as tr_slug',
        //     'pt.description as tr_description',
        // ])
        //     ->join('petition_translations as pt', function ($join) use ($locale) {
        //         $join->on('pt.petition_id', '=', 'petitions.id')
        //             ->where('pt.locale', '=', $locale);
        //     })
        //     ->where('petitions.status', 'published')
        //     ->where('petitions.is_active', 1)
        //     //
        //     // ->limit(Settings::get('max_featured_petitions_per_country', 10))
        //     ->whereNotIn('petitions.id', $excludedIds)
        //     ->orderByDesc('petitions.signature_count')
        //     ->first();
        //     // ->get();

        $featuredPetition = Petition::select([
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
            ->where(function ($q) {
                $q->whereNotNull('pt_locale.title')
                    ->orWhereNotNull('pt_default.title');
            })
            ->where('petitions.status', 'published')
            ->where('petitions.is_active', 1)
            ->whereNotIn('petitions.id', $excludedIds)
            ->orderByDesc('petitions.signature_count')
            ->first();

        // $recentActivities = Signature::select([
        //     'signatures.*',
        //     'petitions.id as petition_id',
        //     'pt.title as petition_title',
        //     'pt.slug as petition_slug',
        // ])
        //     ->join('petitions', function ($join) {
        //         $join->on('petitions.id', '=', 'signatures.petition_id')
        //             ->where('petitions.status', 'published')
        //             ->where('petitions.is_active', 1);
        //     })
        //     ->join('petition_translations as pt', function ($join) use ($locale) {
        //         $join->on('pt.petition_id', '=', 'petitions.id')
        //             ->where('pt.locale', '=', $locale);
        //     })
        //     ->latest('signatures.created_at')
        //     ->limit(10)
        //     ->get();

        $recentActivities = Signature::select([
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

        return view('pages.home', compact(
            'content',
            'categories',
            'featuredPetition',
            'recentActivities'
        ));
    }
}
