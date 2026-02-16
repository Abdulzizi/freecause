<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\ApproxRows;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AdminCategoriesController extends Controller
{
    use ApproxRows;

    public function index(Request $request)
    {
        $locale = $request->query('locale', 'en');

        $filters = [
            'id'   => trim((string) $request->query('id', '')),
            'name' => trim((string) $request->query('name', '')),
        ];

        $q = DB::table('categories')
            ->leftJoin('category_translations as ct', function ($j) use ($locale) {
                $j->on('ct.category_id', '=', 'categories.id')
                  ->where('ct.locale', '=', $locale);
            })
            ->select([
                'categories.id',
                DB::raw("'" . $locale . "' as locale"),
                'ct.name',
                'ct.slug',
            ]);

        if ($filters['id'] !== '') {
            $q->where('categories.id', (int) $filters['id']);
        }

        if ($filters['name'] !== '') {
            $q->where('ct.name', 'like', '%' . $filters['name'] . '%');
        }

        $q->orderBy('categories.id');

        $categories = $q->paginate(50)->withQueryString();
        $approxTotal = $this->approxTableRows('categories');

        $selectedId = $request->query('select');
        $selectedCategory = null;
        $selectedTranslation = null;

        if ($selectedId) {
            $selectedCategory = DB::table('categories')
                ->where('id', (int) $selectedId)
                ->first();

            $selectedTranslation = DB::table('category_translations')
                ->where('category_id', (int) $selectedId)
                ->where('locale', $locale)
                ->first();
        }

        $locales = [
            'en' => 'English',
            'fr' => 'French',
            'it' => 'Italian',
        ];

        return view('admin.categories.index', compact(
            'categories',
            'filters',
            'approxTotal',
            'selectedCategory',
            'selectedTranslation',
            'locale',
            'locales'
        ));
    }

    public function save(Request $request)
    {
        $data = $request->validate([
            'id'     => ['required', 'integer'],
            'locale' => ['required', 'string'],
            'name'   => ['required', 'string', 'max:150'],
            'slug'   => [
                'nullable',
                'string',
                'max:150',
                Rule::unique('category_translations')
                    ->where(fn ($q) => $q->where('locale', $request->locale))
                    ->ignore($request->id, 'category_id')
            ],
        ]);

        $slug = $data['slug']
            ? Str::slug($data['slug'])
            : Str::slug($data['name']);

        DB::table('category_translations')->updateOrInsert(
            [
                'category_id' => $data['id'],
                'locale'      => $data['locale'],
            ],
            [
                'name' => $data['name'],
                'slug' => $slug,
            ]
        );

        Cache::forget("categories_{$data['locale']}");

        return redirect()
            ->route('admin.categories', [
                'locale' => $data['locale'],
                'select' => $data['id']
            ])
            ->with('success', 'saved');
    }
}
