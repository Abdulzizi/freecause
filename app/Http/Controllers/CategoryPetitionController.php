<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Petition;

class CategoryPetitionController extends Controller
{
    public function index(string $locale, string $categorySlug, Category $category)
    {
        if ($category->slug !== $categorySlug) {
            return redirect()->to(url("/{$locale}/petitions/category-{$category->slug}-{$category->id}"));
        }

        $petitions = Petition::query()
            ->where('locale', $locale)
            ->where('status', 'published')
            ->where('category_id', $category->id)
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('pages.petitions-list', [
            'pageTitle' => $category->name,
            'heading' => "{$category->name} Petitions",
            'category' => $category,
            'petitions' => $petitions,
            'petitionTitle' => fn($p) => $p->title,
            'petitionUrl' => fn($p) => url("/{$locale}/petition/{$p->slug}/{$p->id}"),
        ]);
    }
}
