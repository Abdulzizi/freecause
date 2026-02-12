<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\ApproxRows;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class AdminCategoriesController extends Controller
{
    use ApproxRows;

    public function index(Request $request)
    {
        $locale = $request->query('locale', 'en');

        $filters = [
            'id' => trim((string) $request->query('id', '')),
            'name' => trim((string) $request->query('name', '')),
            // 'text' => trim((string) $request->query('text', '')),
        ];

        $q = DB::table('categories')
            ->join('category_translations as ct', function ($j) use ($locale) {
                $j->on('ct.category_id', '=', 'categories.id')
                    ->where('ct.locale', '=', $locale);
            })
            ->select([
                'categories.id',
                'ct.locale',
                'ct.name',
                'ct.slug',
                // 'ct.text',
            ]);

        if ($filters['id'] !== '') {
            $q->where('categories.id', (int) $filters['id']);
        }

        if ($filters['name'] !== '') {
            $q->where('ct.name', 'like', '%' . $filters['name'] . '%');
        }

        // if ($filters['text'] !== '') {
        //     $q->where('ct.text', 'like', '%' . $filters['text'] . '%');
        // }

        $q->orderBy('categories.id');

        $categories = $q->paginate(50)->withQueryString();

        $approxTotal = $this->approxTableRows('categories');

        $selectedId = $request->query('select');
        $selectedCategory = null;
        $selectedTranslation = null;

        if ($selectedId) {
            $selectedCategory = DB::table('categories')->where('id', (int) $selectedId)->first();
            $selectedTranslation = DB::table('category_translations')
                ->where('category_id', (int) $selectedId)
                ->where('locale', $locale)
                ->first();
        }

        $locales = [
            '' => '(Locale)',
            'en' => 'English',
            'fr' => 'French',
            'it' => 'Italian',
            // 'es_AR' => 'Spanish (AR)',
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
            'id' => ['required', 'integer'],
            'locale' => ['required', 'string'],
            'name' => ['nullable', 'string'],
            'slug' => ['nullable', 'string'],
            // 'text' => ['nullable', 'string'],
        ]);

        //* NOTE: category base row exists already (seeded). if you later want "create new category"
        //* we can implement it, but for now usually edit existing ones.

        DB::table('category_translations')->updateOrInsert(
            [
                'category_id' => $data['id'],
                'locale' => $data['locale'],
            ],
            [
                'name' => $data['name'] ?? '',
                'slug' => $data['slug'] ?? '',
                // 'text' => $data['text'] ?? '',
            ]
        );

        return redirect()
            ->route('admin.categories', ['locale' => $data['locale'], 'select' => $data['id']])
            ->with('success', 'saved');
    }
}
