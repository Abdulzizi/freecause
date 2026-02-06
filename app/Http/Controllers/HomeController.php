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
            ->select([
                'petitions.*',
                'pt.title as tr_title',
                'pt.slug as tr_slug',
                'pt.description as tr_description',
            ])
            ->join('petition_translations as pt', function ($join) use ($locale) {
                $join->on('pt.petition_id', '=', 'petitions.id')
                    ->where('pt.locale', '=', $locale);
            })
            ->whereNotNull('petitions.category_id')
            ->with('category')
            ->orderByDesc('petitions.signature_count')
            ->orderByDesc('petitions.id')
            ->first();

        $recentActivities = Signature::query()
            ->where('locale', $locale)
            ->with([
                'petition' => function ($q) use ($locale) {
                    $q->select('petitions.*')
                        ->with(['translations' => function ($t) use ($locale) {
                            $t->where('locale', $locale);
                        }]);
                },
            ])
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
