<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Petition;

class CategoryPetitionController extends Controller
{
    public function index($locale, string $categorySlug, Category $category)
    {
        // safety check (slug mismatch)
        if ($category->slug !== $categorySlug) {
            return redirect()->to(
                url("/{$locale}/petitions/{$category->slug}-{$category->id}")
            );
        }

        // for now: placeholder logic
        // later this becomes a real relation
        $petitions = Petition::query()
            ->where('status', 'published')
            ->where('locale', $locale)
            ->latest()
            ->paginate(12);

        return view('pages.petitions-category', compact(
            'category',
            'petitions'
        ));
    }
}
