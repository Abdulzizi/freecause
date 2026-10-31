<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Petition;
use App\Support\CacheKeys;
use Illuminate\Pagination\LengthAwarePaginator;

class CategoryPetitionController extends Controller
{
    public function index(string $locale, string $categorySlug, string $category)
    {
        $locale = normalize_locale($locale);
        $defaultLocale = default_locale();
        $search = trim((string) request('q', ''));
        $page = max(1, min((int) request('page', 1), 500));

        $categoryId = (int) $category;
        $category = Category::find($categoryId)
            ?? Category::whereHas('translations', fn ($q) => $q->where('slug', $categorySlug))->first();

        abort_if(! $category, 404);

        $tr = $category->translations()
            ->where('locale', $locale)
            ->first()
            ?? $category->translations()
                ->where('locale', $defaultLocale)
                ->first()
            ?? $category->translations()
                ->orderBy('id')
                ->first();

        abort_if(! $tr, 404);

        if ($category->id !== $categoryId || $tr->slug !== $categorySlug || $tr->locale !== $locale) {
            $url = route('petitions.byCategory', [
                'locale' => $tr->locale,
                'categorySlug' => $tr->slug,
                'category' => $category->id,
            ]);
            $query = request()->getQueryString();

            return redirect($url.($query ? '?'.$query : ''), 301);
        }

        $query = Petition::query()
            ->select([
                'petitions.id',
                'petitions.signature_count',
                'petitions.goal_signatures',
                'petitions.category_id',
                'petitions.cover_image',
            ])
            ->addSelect(['pt_locale.title as tr_title', 'pt_locale.slug as tr_slug'])
            ->join('petition_translations as pt_locale', function ($join) use ($locale) {
                $join->on('pt_locale.petition_id', '=', 'petitions.id')
                    ->where('pt_locale.locale', '=', $locale);
            })
            ->whereNotNull('pt_locale.title')
            ->where('petitions.category_id', $category->id)
            ->visible();

        if ($search !== '') {
            $like = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $search).'%';
            $query->where(function ($q) use ($like) {
                $q->where('pt_locale.title', 'LIKE', $like);
            });
            $petitions = $query->orderByDesc('petitions.id')->paginate(15, ['*'], 'page', $page)->withQueryString();
        } else {
            $cacheKey = CacheKeys::petitionsByCategory($locale, $category->id, $page);
            $cached = cache()->remember($cacheKey, now()->addSeconds(300), function () use ($query, $page) {
                $p = (clone $query)->orderByDesc('petitions.id')->paginate(15, ['petitions.id'], 'page', $page);

                return ['ids' => $p->items(), 'total' => $p->total()];
            });
            $ids = collect($cached['ids'] ?? [])->pluck('id')->all();
            $rows = empty($ids) ? collect() : (clone $query)->whereIn('petitions.id', $ids)->with('translations')->get()->sortBy(fn ($r) => array_search($r->id, $ids))->values();
            $petitions = new LengthAwarePaginator($rows, $cached['total'] ?? 0, 15, $page, ['path' => request()->url()]);

            $petitions = $petitions->withQueryString();
        }

        $categories = cache()->remember(CacheKeys::homeCategories($locale), 43200, function () use ($locale, $defaultLocale) {
            return Category::select(['categories.id', 'categories.sort_order'])
                ->translated($locale, 'ct_locale', 'ct_default', ['name' => 'tr_name', 'slug' => 'tr_slug'], $defaultLocale)
                ->where('categories.is_active', true)
                ->orderBy('categories.sort_order')
                ->get();
        });

        return view('pages.petitions-list', [
            'pageTitle' => $tr->name,
            'heading' => __('list.category_petitions', ['name' => $tr->name]),
            'category' => $category,
            'categoryTr' => $tr,
            'search' => $search,
            'categories' => $categories,
            'petitions' => $petitions,
        ]);
    }
}
