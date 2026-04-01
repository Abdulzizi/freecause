<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Petition;
use Illuminate\Support\Facades\DB;

class CategoryPetitionController extends Controller
{
    public function index(string $locale, string $categorySlug, Category $category)
    {
        $locale = normalize_locale($locale);
        $defaultLocale = default_locale();

        $tr = $category->translations()
            ->where('locale', $locale)
            ->first()
            ?? $category->translations()
            ->where('locale', $defaultLocale)
            ->first();

        abort_if(! $tr, 404);

        if ($tr->slug !== $categorySlug || $tr->locale !== $locale) {
            return redirect()->route('petitions.byCategory', [
                'locale' => $tr->locale,
                'categorySlug' => $tr->slug,
                'category' => $category->id,
            ]);
        }

        $petitions = Petition::query()
            ->select([
                'petitions.id',
                'petitions.signature_count',
                'petitions.goal_signatures',
                'petitions.category_id',
                'petitions.cover_image',
                DB::raw("COALESCE(pt_locale.title, pt_default.title) as tr_title"),
                DB::raw("COALESCE(pt_locale.slug, pt_default.slug) as tr_slug"),
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
            ->where('petitions.category_id', $category->id)
            ->orderByDesc('petitions.id')
            ->paginate(15)
            ->withQueryString();

        return view('pages.petitions-list', [
            'pageTitle' => $tr->name,
            'heading' => $tr->name . ' Petitions',
            'category' => $category,
            'categoryTr' => $tr,
            'petitions' => $petitions,
        ]);
    }
}
