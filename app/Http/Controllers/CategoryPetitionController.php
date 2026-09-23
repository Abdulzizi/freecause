<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Petition;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CategoryPetitionController extends Controller
{
    public function index(string $locale, string $categorySlug, Category $category)
    {
        Log::info('CategoryPetitionController', [
            'locale' => $locale,
            'categorySlug' => $categorySlug,
            'categoryId' => $category->id ?? 'null',
        ]);

        $locale = normalize_locale($locale);
        $defaultLocale = default_locale();
        $search = trim((string) request('q', ''));
        $page = max(1, min((int) request('page', 1), 500));

        $tr = $category->translations()
            ->where('locale', $locale)
            ->first()
            ?? $category->translations()
                ->where('locale', $defaultLocale)
                ->first()
            ?? $category->translations()
                ->orderBy('id')
                ->first();

        Log::info('CategoryPetitionController translation', [
            'tr' => $tr ? ['slug' => $tr->slug, 'locale' => $tr->locale] : 'null',
            'categorySlug' => $categorySlug,
            'locale' => $locale,
        ]);

        abort_if(! $tr, 404);

        if ($tr->slug !== $categorySlug || $tr->locale !== $locale) {
            return redirect()->route('petitions.byCategory', [
                'locale' => $tr->locale,
                'categorySlug' => $tr->slug,
                'category' => $category->id,
            ]);
        }

        $query = Petition::query()
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
            ->where('petitions.category_id', $category->id);

        if ($search !== '') {
            $like = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $search).'%';
            $query->where(function ($q) use ($like) {
                $q->where('pt_locale.title', 'LIKE', $like)
                    ->orWhere('pt_default.title', 'LIKE', $like);
            });
            $petitions = $query->orderByDesc('petitions.id')->paginate(15, ['*'], 'page', $page)->withQueryString();
        } else {
            $cacheKey = "petitions:byCategory:{$locale}:{$category->id}:page:{$page}";
            $petitions = cache()->remember($cacheKey, now()->addSeconds(300), function () use ($query, $page) {
                return $query->orderByDesc('petitions.id')->paginate(15, ['*'], 'page', $page);
            });

            if (is_array($petitions)) {
                $items = collect(array_map(fn ($i) => is_array($i) ? (object) $i : $i, $petitions['data'] ?? []));
                $petitions = new \Illuminate\Pagination\LengthAwarePaginator(
                    $items,
                    $petitions['total'] ?? 0,
                    $petitions['per_page'] ?? 15,
                    $petitions['current_page'] ?? $page,
                    ['path' => \Illuminate\Pagination\Paginator::resolveCurrentPath()]
                );
            }

            $petitions = $petitions->withQueryString();
        }

        return view('pages.petitions-list', [
            'pageTitle' => $tr->name,
            'heading' => $tr->name . ' Petitions',
            'category' => $category,
            'categoryTr' => $tr,
            'search' => $search,
            'petitions' => $petitions,
        ]);
    }
}
