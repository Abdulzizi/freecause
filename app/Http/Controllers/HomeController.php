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
        $locale =  app()->getLocale();

        $content = PageContent::query()
            ->where('page', 'home')
            ->where('locale', $locale)
            ->pluck('value', 'key');

        $h1 = $content['hero_h1'] ?? '';
        $h2 = $content['hero_h2'] ?? '';

        $categories = Category::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $featuredPetition = Petition::query()
            ->where('locale', $locale)
            ->whereNotNull('category_id')
            ->orderByDesc('signature_count')
            ->latest('id')
            ->with('category')
            ->first();

        $recentActivities = Signature::query()
            ->where('locale', $locale)
            ->with(['petition' => function ($q) use ($locale) {
                $q->select('id', 'slug', 'title', 'locale');
            }])
            ->latest('created_at')
            ->limit(10)
            ->get();

        return view('pages.home', compact(
            'locale',
            'h1',
            'h2',
            'categories',
            'featuredPetition',
            'recentActivities'
        ));
    }
}
