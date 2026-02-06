<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Petition;
use Illuminate\Http\Request;

class CategoryPetitionController extends Controller
{
    public function index(string $locale, string $categorySlug, Category $category)
    {
        if ($category->slug !== $categorySlug) {
            return redirect()->to(url("/{$locale}/petitions/category-{$category->slug}-{$category->id}"));
        }

        $petitions = Petition::query()
            ->select([
                'petitions.*',
                'pt.title as tr_title',
                'pt.slug as tr_slug',
            ])
            ->join('petition_translations as pt', function ($join) use ($locale) {
                $join->on('pt.petition_id', '=', 'petitions.id')
                    ->where('pt.locale', '=', $locale);
            })
            ->where('petitions.status', 'published')
            ->where('petitions.category_id', $category->id)
            ->with('category')
            ->orderByDesc('petitions.id')
            ->paginate(15)
            ->withQueryString();

        return view('pages.petitions-list', [
            'pageTitle' => $category->name,
            'heading' => "{$category->name} Petitions",
            'category' => $category,
            'petitions' => $petitions,

            'petitionTitle' => fn($p) => $p->tr_title,
            'petitionUrl'   => fn($p) => url("/{$locale}/petition/{$p->tr_slug}/{$p->id}"),
        ]);
    }
}
