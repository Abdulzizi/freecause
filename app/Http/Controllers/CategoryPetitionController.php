<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Petition;
use Illuminate\Http\Request;

class CategoryPetitionController extends Controller
{
    public function index(string $locale, string $categorySlug, Category $category)
    {
        $tr = $category->translations()->where('locale', $locale)->first();
        abort_if(!$tr, 404);

        if ($tr->slug !== $categorySlug) {
            return redirect()->to(
                url("/{$locale}/petitions/category-{$tr->slug}-{$category->id}")
            );
        }

        $petitions = Petition::query()
            ->select([
                'petitions.id',
                'petitions.signature_count',
                'petitions.goal_signatures',
                'petitions.category_id',
                'petitions.cover_image',
                'pt.title as tr_title',
                'pt.slug as tr_slug',
            ])
            ->join('petition_translations as pt', function ($join) use ($locale) {
                $join->on('pt.petition_id', '=', 'petitions.id')
                    ->where('pt.locale', '=', $locale);
            })
            ->where('petitions.status', 'published')
            ->where('petitions.is_active', 1)
            ->where('petitions.category_id', $category->id)
            ->orderByDesc('petitions.id')
            ->paginate(15)
            ->withQueryString();

        return view('pages.petitions-list', [
            'pageTitle' => $tr->name,
            'heading' => "{$tr->name} Petitions",
            'category' => $category,
            'categoryTr' => $tr,
            'petitions' => $petitions,
        ]);
    }
}
