<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Language;
use App\Support\ApproxRows;
use App\Support\CacheVersion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AdminCategoriesController extends Controller
{
    use ApproxRows;

    private function bustCategoryCache(): void
    {
        $codes = Language::where('is_active', true)->pluck('code');
        foreach ($codes as $loc) {
            Cache::forget("categories:list:{$loc}");
        }

        try {
            CacheVersion::bump('home');
            CacheVersion::bump('list');
            CacheVersion::bump('sitemap');
        } catch (\Throwable $e) {
            Log::warning('Cache bump failed after category save: '.$e->getMessage());
        }
    }

    public function index(Request $request)
    {
        $locale = $request->query('locale', 'en');

        $filters = [
            'id' => trim((string) $request->query('id', '')),
            'name' => trim((string) $request->query('name', '')),
        ];

        // Pre-fetch petition counts per category (single query, avoids N+1)
        $petitionCounts = DB::table('petitions')
            ->whereNotNull('category_id')
            ->select('category_id', DB::raw('COUNT(*) as petition_count'))
            ->groupBy('category_id')
            ->pluck('petition_count', 'category_id')
            ->toArray();

        $q = DB::table('categories')
            ->leftJoin('category_translations as ct', function ($j) use ($locale) {
                $j->on('ct.category_id', '=', 'categories.id')
                    ->where('ct.locale', '=', $locale);
            })
            ->select([
                'categories.id',
                DB::raw("'".$locale."' as locale"),
                'ct.name',
                'ct.slug',
            ]);

        if ($filters['id'] !== '') {
            $q->where('categories.id', (int) $filters['id']);
        }

        if ($filters['name'] !== '') {
            $q->where('ct.name', 'like', '%'.$filters['name'].'%');
        }

        $q->orderBy('categories.id');

        $categories = $q->paginate(50)->withQueryString();
        $approxTotal = $this->approxTableRows('categories');

        // Attach petition counts to paginated collection
        $categories->getCollection()->transform(function ($cat) use ($petitionCounts) {
            $cat->petition_count = $petitionCounts[$cat->id] ?? 0;

            return $cat;
        });

        $selectedId = $request->query('select');
        $selectedCategory = null;
        $selectedTranslation = null;
        $selectedPetitionCount = 0;

        if ($selectedId) {
            $selectedCategory = DB::table('categories')
                ->where('id', (int) $selectedId)
                ->first();

            $selectedTranslation = DB::table('category_translations')
                ->where('category_id', (int) $selectedId)
                ->where('locale', $locale)
                ->first();

            $selectedPetitionCount = $petitionCounts[$selectedId] ?? 0;
        }

        $locales = Language::where('is_active', true)
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->pluck('name', 'code')
            ->toArray();

        return view('admin.categories.index', compact(
            'categories',
            'filters',
            'approxTotal',
            'selectedCategory',
            'selectedTranslation',
            'selectedPetitionCount',
            'locale',
            'locales'
        ));
    }

    public function save(Request $request)
    {
        $data = $request->validate([
            'id' => ['required', 'integer', 'exists:categories,id'],
            'locale' => ['required', 'string', Rule::exists('languages', 'code')->where('is_active', 1)],
            'name' => ['required', 'string', 'max:150'],
            'slug' => ['nullable', 'string', 'max:150'],
            'is_active' => ['nullable'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $slug = $data['slug']
            ? Str::slug($data['slug'])
            : Str::slug($data['name']);

        $slugTaken = DB::table('category_translations')
            ->where('locale', $data['locale'])
            ->where('slug', $slug)
            ->where('category_id', '!=', $data['id'])
            ->exists();

        if ($slugTaken) {
            return back()->withErrors(['slug' => 'Slug already used for this locale.'])->withInput();
        }

        DB::transaction(function () use ($request, $data, $slug) {
            $categoryUpdate = ['is_active' => $request->boolean('is_active')];
            if ($request->has('sort_order')) {
                $categoryUpdate['sort_order'] = (int) ($data['sort_order'] ?? 0);
            }
            DB::table('categories')->where('id', $data['id'])->update($categoryUpdate);

            DB::table('category_translations')->updateOrInsert(
                [
                    'category_id' => $data['id'],
                    'locale' => $data['locale'],
                ],
                [
                    'name' => $data['name'],
                    'slug' => $slug,
                ]
            );
        });

        $this->bustCategoryCache();

        return redirect()
            ->route('admin.categories', [
                'locale' => $data['locale'],
                'select' => $data['id'],
            ])
            ->with('success', 'saved');
    }

    public function createCategory(Request $request)
    {
        $data = $request->validate([
            'locale' => ['required', 'string', Rule::exists('languages', 'code')->where('is_active', 1)],
            'name' => ['required', 'string', 'max:150'],
        ]);

        $categoryId = DB::transaction(function () use ($data) {
            $categoryId = DB::table('categories')->insertGetId([
                'is_active' => true,
                'sort_order' => 0,
            ]);

            $base = Str::slug($data['name']) ?: 'category';
            $slug = $base;
            $i = 1;
            while (DB::table('category_translations')
                ->where('locale', $data['locale'])
                ->where('slug', $slug)
                ->exists()) {
                $slug = $base.'-'.$i++;
            }

            DB::table('category_translations')->insert([
                'category_id' => $categoryId,
                'locale' => $data['locale'],
                'name' => $data['name'],
                'slug' => $slug,
            ]);

            return $categoryId;
        });

        $this->bustCategoryCache();

        return redirect()
            ->route('admin.categories', ['locale' => $data['locale'], 'select' => $categoryId])
            ->with('success', 'Category created.');
    }

    public function destroy(Request $request)
    {
        $data = $request->validate([
            'id' => ['required', 'integer', 'exists:categories,id'],
        ]);

        $id = (int) $data['id'];

        $petitionCount = DB::table('petitions')->where('category_id', $id)->count();
        if ($petitionCount > 0) {
            return back()->withErrors(['id' => "Cannot delete: {$petitionCount} petition(s) are using this category."]);
        }

        DB::table('category_translations')->where('category_id', $id)->delete();
        DB::table('categories')->where('id', $id)->delete();

        $this->bustCategoryCache();

        return redirect()
            ->route('admin.categories')
            ->with('success', 'Category deleted.');
    }
}
