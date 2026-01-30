<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Petition;
use App\Models\Signature;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    // public function __invoke(string $locale)
    // {
    //     $categories = Category::query()
    //         ->where('is_active', true)
    //         ->orderBy('sort_order')
    //         ->get();

    //     $featuredPetition = Petition::query()
    //         ->where('locale', $locale)
    //         ->where('status', 'published')
    //         ->orderByDesc('signature_count') // good “featured” proxy for now
    //         ->latest()
    //         ->first();

    //     return view('pages.home', compact('categories', 'featuredPetition'));
    // }

    public function index(Request $request)
    {
        $locale =  app()->getLocale();

        $categories = Category::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // featured = highest signature_count in this locale
        $featuredPetition = Petition::query()
            ->where('locale', $locale)
            ->whereNotNull('category_id')
            ->orderByDesc('signature_count')
            ->latest('id') // small tie-breaker
            ->with('category')
            ->first();

        // recent activity = latest signatures in this locale (join petitions to show title + url)
        $recentActivities = Signature::query()
            ->where('locale', $locale)
            ->with(['petition' => function ($q) use ($locale) {
                $q->select('id', 'slug', 'title', 'locale');
            }])
            ->latest('created_at')
            ->limit(10)
            ->get();

        return view('pages.home', compact(
            'categories',
            'featuredPetition',
            'recentActivities'
        ));
    }
}
