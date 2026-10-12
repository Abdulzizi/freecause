<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminPageBulkRequest;
use App\Http\Requests\Admin\AdminPageSaveRequest;
use App\Models\Language;
use App\Models\Page;
use App\Models\PageTranslation;
use App\Support\EdgeCachePurge;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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

    public function save(AdminPageSaveRequest $request)
    {
        $data = $request->validated();

        $redirectParams = array_filter([
            'locale' => $request->input('locale'),
            'select' => $request->input('page_id'),
        ], fn ($value) => $value !== null && $value !== '');

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

        $translation = PageTranslation::query()
            ->where('page_id', $pageId)
            ->where('locale', $data['locale'])
            ->first(['locale', 'slug']);
        $viewUrl = $translation?->slug
            ? url(($translation->locale ?: $data['locale']).'/'.$translation->slug)
            : null;

        return redirect()->route('admin.pages', [
            'locale' => $data['locale'],
            'select' => $pageId,
        ])
            ->with('success', 'Page saved')
            ->with('view_url', $viewUrl);
    }

    public function bulkAction(AdminPageBulkRequest $request)
    {
        $data = $request->validated();
        $ids = $data['ids'];
        $action = $data['action'];

        $permission = $action === 'delete' ? 'delete' : 'edit';
        if (! admin_can('pages', $permission)) {
            return response()->json(['ok' => false, 'error' => "You do not have permission to {$permission} pages."], 403);
        }

        $q = PageTranslation::whereIn('page_id', $ids);

        $affected = match ($action) {
            'publish' => $q->update(['published' => 1]),
            'unpublish' => $q->update(['published' => 0]),
            'delete' => Page::whereIn('id', $ids)->delete(),
        };

        EdgeCachePurge::listings();

        return response()->json(['ok' => true, 'action' => $action, 'affected' => $affected]);
    }
}
