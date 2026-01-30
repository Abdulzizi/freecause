<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Petition;

class HomeController extends Controller
{
    public function __invoke(string $locale)
    {
        $categories = Category::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $featuredPetition = Petition::query()
            ->where('locale', $locale)
            ->where('status', 'published')
            ->orderByDesc('signature_count') // good “featured” proxy for now
            ->latest()
            ->first();

        return view('pages.home', compact('categories', 'featuredPetition'));
    }
}
