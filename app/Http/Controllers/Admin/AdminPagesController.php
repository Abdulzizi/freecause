<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Language;
use App\Models\Page;
use App\Models\PageTranslation;
use App\Support\EdgeCachePurge;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AdminPagesController extends Controller
{
    public function index(Request $request)
    {
        $filters = [
            'id' => $request->query('id', ''),
            'title' => $request->query('title', ''),
            'locale' => $request->query('locale', ''),
            'select' => $request->query('select', ''),
        ];

        $q = PageTranslation::query();

        if ($filters['id'] !== '') {
            $q->where('page_id', (int) $filters['id']);
        }

        if ($filters['title'] !== '') {
            $q->where('title', 'like', '%'.$filters['title'].'%');
        }

        if ($filters['locale'] !== '') {
            $q->where('locale', $filters['locale']);
        }

        $q->orderByDesc('id');

        $pages = $q->paginate(25)->withQueryString();

        $pages->getCollection()->transform(function ($p) {
            $p->client_url = url($p->locale.'/'.$p->slug);

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
        $validator = Validator::make($request->all(), [
            'page_id' => ['nullable', 'integer', 'exists:pages,id'],
            'locale' => ['required', 'string', Rule::exists('languages', 'code')->where('is_active', 1)],
            'title' => ['required', 'string', 'max:190'],
            'slug' => ['nullable', 'string', 'max:190'],
            'content' => ['nullable', 'string'],
        ]);

        $redirectParams = array_filter([
            'locale' => $request->input('locale'),
            'select' => $request->input('page_id'),
        ], fn ($value) => $value !== null && $value !== '');

        if ($validator->fails()) {
            return redirect()
                ->route('admin.pages', $redirectParams)
                ->withErrors($validator)
                ->withInput();
        }

        $data = $validator->validated();

        $published = $request->has('published');
        $slug = $data['slug'] ? Str::slug($data['slug']) : Str::slug($data['title']);

        $slugQuery = PageTranslation::where('locale', $data['locale'])
            ->where('slug', $slug);

        if (! empty($data['page_id'])) {
            $slugQuery->where('page_id', '!=', $data['page_id']);
        }

        if ($slugQuery->exists()) {
            return redirect()
                ->route('admin.pages', $redirectParams)
                ->withErrors(['slug' => 'Slug already used for this locale.'])
                ->withInput();
        }

        $pageId = DB::transaction(function () use ($data, $slug, $published) {
            $page = empty($data['page_id'])
                ? Page::create()
                : Page::findOrFail($data['page_id']);

            PageTranslation::updateOrCreate(
                [
                    'page_id' => $page->id,
                    'locale' => $data['locale'],
                ],
                [
                    'title' => $data['title'],
                    'slug' => $slug,
                    'content' => sanitizeAdminHtml($data['content'] ?? ''),
                    'published' => $published,
                ]
            );

            return $page->id;
        });

        EdgeCachePurge::listings();

        $viewUrl = null;
        try {
            $row = DB::table('page_translations')->find($pageId);
            if ($row?->slug) {
                $viewUrl = url(($row->locale ?: $data['locale']).'/'.$row->slug);
            }
        } catch (\Throwable $e) {
        }

        return redirect()->route('admin.pages', [
            'locale' => $data['locale'],
            'select' => $pageId,
        ])
            ->with('success', 'Page saved')
            ->with('view_url', $viewUrl);
    }

    public function bulkAction(Request $request)
    {
        $ids = $request->input('ids', []);
        $action = $request->input('action');

        if (! is_array($ids) || empty($ids)) {
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
                // Checkboxes submit page_id (resources/views/admin/pages/index.blade.php),
                // so deleting removes the parent pages rows — the FK cascade
                // removes all their translations in every locale.
                Page::whereIn('id', $ids)->delete();
                break;
        }

        if (in_array($action, ['publish', 'unpublish', 'delete'], true)) {
            EdgeCachePurge::listings();
        }

        return back()->with('success', 'Bulk action applied');
    }
}
