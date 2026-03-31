<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Language;
use App\Models\Page;
use App\Models\PageTranslation;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminPagesController extends Controller
{
    public function index(Request $request)
    {
        $filters = [
            'id'     => $request->query('id', ''),
            'title'  => $request->query('title', ''),
            'locale' => $request->query('locale', ''),
            'select' => $request->query('select', ''),
        ];

        $q = PageTranslation::query();

        if ($filters['id'] !== '') {
            $q->where('page_id', (int) $filters['id']);
        }

        if ($filters['title'] !== '') {
            $q->where('title', 'like', '%' . $filters['title'] . '%');
        }

        if ($filters['locale'] !== '') {
            $q->where('locale', $filters['locale']);
        }

        $q->orderByDesc('id');

        $pages = $q->paginate(25)->withQueryString();

        $pages->getCollection()->transform(function ($p) {
            $p->client_url = url($p->locale . '/' . $p->slug);
            return $p;
        });

        $approxTotal = PageTranslation::count();

        $selectedPage = null;

        if ($filters['select']) {
            $selectedPage = PageTranslation::where('page_id', $filters['select'])
                ->where('locale', $filters['locale'] ?: 'en')
                ->first();
        }

        // BUG 3 FIX: dynamic locales from languages table instead of hardcoded
        $languages = Language::where('is_active', true)
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get();

        $locales = ['' => '(Locale)'];
        foreach ($languages as $lang) {
            $locales[$lang->code] = $lang->name;
        }

        return view('admin.pages.index', compact(
            'pages',
            'approxTotal',
            'filters',
            'selectedPage',
            'locales'
        ));
    }


    public function save(Request $request)
    {
        $data = $request->validate([
            'page_id'   => 'nullable|integer',
            'locale'    => 'required|string|max:8',
            'title'     => 'required|string',
            'slug'      => 'nullable|string',
            'content'   => 'nullable|string',
        ]);

        $data['published'] = $request->has('published');

        if (empty($data['page_id'])) {
            $page = Page::create();
        } else {
            $page = Page::findOrFail($data['page_id']);
        }

        $slug = $data['slug'] ?: Str::slug($data['title']);

        PageTranslation::updateOrCreate(
            [
                'page_id' => $page->id,
                'locale'  => $data['locale'],
            ],
            [
                'title'     => $data['title'],
                'slug'      => $slug,
                'content'   => sanitizeAdminHtml($data['content'] ?? ''),
                'published' => $data['published'],
            ]
        );

        return redirect()->route('admin.pages')
            ->with('success', 'Page saved');
    }

    public function bulkAction(Request $request)
    {
        $ids = $request->input('ids', []);
        $action = $request->input('action');

        if (!is_array($ids) || empty($ids)) {
            return back()->with('error', 'No pages selected');
        }

        $q = PageTranslation::whereIn('page_id', $ids);

        switch ($action) {
            case 'publish':
                $q->update(['published' => 1]);
                break;

            case 'unpublish':
                $q->update(['published' => 0]);
                break;

            case 'delete':
                $q->delete();
                break;
        }

        return back()->with('success', 'Bulk action applied');
    }
}