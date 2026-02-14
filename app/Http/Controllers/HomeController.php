<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\PageContent;
use App\Models\Petition;
use App\Models\Signature;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $locale = app()->getLocale();

        $content = PageContent::where('page', 'home')
            ->where('locale', $locale)
            ->pluck('value', 'key');

        $excludedIds = [];

        if (!empty($content['exclude_most_read'])) {
            $excludedIds = array_filter(
                array_map('trim', explode(',', $content['exclude_most_read']))
            );
        }

        $categories = Category::select([
            'categories.id',
            'categories.sort_order',
            'ct.name as tr_name',
            'ct.slug as tr_slug',
        ])
            ->join('category_translations as ct', function ($join) use ($locale) {
                $join->on('ct.category_id', '=', 'categories.id')
                    ->where('ct.locale', '=', $locale);
            })
            ->where('categories.is_active', true)
            ->orderBy('categories.sort_order')
            ->get();

        $featuredPetition = Petition::select([
            'petitions.*',
            'pt.title as tr_title',
            'pt.slug as tr_slug',
            'pt.description as tr_description',
        ])
            ->join('petition_translations as pt', function ($join) use ($locale) {
                $join->on('pt.petition_id', '=', 'petitions.id')
                    ->where('pt.locale', '=', $locale);
            })
            ->where('petitions.status', 'published')
            ->where('petitions.is_active', 1)
            ->where('petitions.is_featured', 1)
            ->whereNotIn('petitions.id', $excludedIds)
            ->orderByDesc('petitions.signature_count')
            ->first();

        $recentActivities = Signature::select([
            'signatures.*',
            'petitions.id as petition_id',
            'pt.title as petition_title',
            'pt.slug as petition_slug',
        ])
            ->join('petitions', function ($join) {
                $join->on('petitions.id', '=', 'signatures.petition_id')
                    ->where('petitions.status', 'published')
                    ->where('petitions.is_active', 1);
            })
            ->join('petition_translations as pt', function ($join) use ($locale) {
                $join->on('pt.petition_id', '=', 'petitions.id')
                    ->where('pt.locale', '=', $locale);
            })
            ->latest('signatures.created_at')
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
