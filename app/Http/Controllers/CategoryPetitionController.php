<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Petition;
use App\Support\CacheKeys;

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
            ->translated($locale, 'pt_locale', 'pt_default', ['title' => 'tr_title', 'slug' => 'tr_slug'], $defaultLocale)
            ->where(function ($q) {
                $q->whereNotNull('pt_locale.title')
                    ->orWhereNotNull('pt_default.title');
            })
            ->where('petitions.status', 'published')
            ->where('petitions.is_active', 1)
            ->where('petitions.category_id', $category->id);

        if ($search !== '') {
            $like = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $search).'%';
            $query->where(function ($q) use ($like) {
                $q->where('pt_locale.title', 'LIKE', $like)
                    ->orWhere('pt_default.title', 'LIKE', $like);
            });
            $petitions = $query->orderByDesc('petitions.id')->paginate(15, ['*'], 'page', $page)->withQueryString();
        } else {
            $cacheKey = CacheKeys::petitionsByCategory($locale, $category->id, $page);
            $petitions = cache()->remember($cacheKey, now()->addSeconds(300), function () use ($query, $page) {
                return $query->orderByDesc('petitions.id')->paginate(15, ['*'], 'page', $page);
            });

            $petitions = $petitions->withQueryString();
        }

        return view('pages.petitions-list', [
            'pageTitle' => $tr->name,
            'heading' => __('list.category_petitions', ['name' => $tr->name]),
            'category' => $category,
            'categoryTr' => $tr,
            'search' => $search,
            'petitions' => $petitions,
        ]);
    }
}
