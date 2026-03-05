<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\ApproxRows;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Models\Language;

class AdminPetitionsController extends Controller
{
    use ApproxRows;

    public function index(Request $request)
    {
        $locale = $request->query('locale', 'en');

        $filters = [
            'id' => trim((string) $request->query('id', '')),
            'title' => trim((string) $request->query('title', '')),
            'featured' => $request->query('featured', ''),
        ];

        $defaultLocale = cache()->remember(
            'default_language',
            60,
            fn() => Language::where('is_default', 1)->value('code') ?? 'en'
        );

        $q = DB::table('petitions')
            ->leftJoin('petition_translations as pt', function ($j) use ($locale) {
                $j->on('pt.petition_id', '=', 'petitions.id')
                    ->where('pt.locale', '=', $locale);
            })
            ->leftJoin('petition_translations as pt_default', function ($j) use ($defaultLocale) {
                $j->on('pt_default.petition_id', '=', 'petitions.id')
                    ->where('pt_default.locale', '=', $defaultLocale);
            })
            ->select([
                'petitions.id',
                'petitions.signature_count',
                'petitions.goal_signatures',
                'petitions.status',
                'petitions.is_active',
                'petitions.is_featured',
                'petitions.created_at',
                DB::raw('COALESCE(pt.title, pt_default.title) as title'),
                DB::raw('COALESCE(pt.slug, pt_default.slug) as slug'),
            ]);

        if ($filters['id'] !== '') {
            $q->where('petitions.id', (int) $filters['id']);
        }

        if ($filters['title'] !== '') {
            $q->where(function ($q) use ($filters) {
                $q->where('pt.title', 'like', '%' . $filters['title'] . '%')
                    ->orWhere('pt_default.title', 'like', '%' . $filters['title'] . '%');
            });
        }

        if ($filters['featured'] !== '') {
            $q->where('petitions.is_featured', 1);
        }

        $q->orderByDesc('petitions.id');

        $petitions = $q->paginate(25)->withQueryString();

        $approxTotal = $this->approxTableRows('petitions');

        $selectedId = $request->query('select');
        $selectedPetition = null;
        $selectedTranslation = null;

        if ($selectedId) {
            $selectedPetition = DB::table('petitions')
                ->where('id', (int) $selectedId)
                ->first();

            $selectedTranslation = DB::table('petition_translations')
                ->where('petition_id', (int) $selectedId)
                ->where('locale', $locale)
                ->first();
        }

        return view('admin.petitions.index', compact(
            'petitions',
            'filters',
            'approxTotal',
            'selectedPetition',
            'selectedTranslation',
            'locale'
        ));
    }

    public function save(Request $request)
    {
        $data = $request->validate([
            'id' => ['required', 'integer'],
            'locale' => ['required', 'string'],
            'title' => ['nullable', 'string'],
            'slug' => ['nullable', 'string'],
            'text' => ['nullable', 'string'],
            'goal_signatures' => ['nullable', 'integer'],
            'is_active' => ['nullable'],
            'status' => ['required', 'string'],
            'is_featured' => ['nullable'],
        ]);

        DB::table('petitions')
            ->where('id', $data['id'])
            ->update([
                'goal_signatures' => $data['goal_signatures'] ?? 100,
                'status' => $data['status'],
                'is_active' => $request->boolean('is_active'),
                'is_featured' => $request->boolean('is_featured'),
            ]);

        $exists = DB::table('petition_translations')
            ->where('slug', $data['slug'])
            ->where('locale', $data['locale'])
            ->where('petition_id', '!=', $data['id'])
            ->exists();

        if ($exists) {
            return back()->withErrors(['slug' => 'Slug already used']);
        }

        DB::table('petition_translations')
            ->updateOrInsert(
                [
                    'petition_id' => $data['id'],
                    'locale' => $data['locale'],
                ],
                [
                    'title' => $data['title'] ?? '',
                    'slug' => $data['slug'] ?? '',
                    'description' => sanitizePetitionHtml($data['text'] ?? ''),
                ]
            );

        return redirect()
            ->route('admin.petitions', ['select' => $data['id'], 'locale' => $data['locale']])
            ->with('success', 'saved');
    }

    public function bulkAction(Request $request)
    {
        $locale = $request->input('locale', 'en');

        $data = $request->validate([
            'action' => ['required', 'string'],
            'ids' => ['required', 'array'],
            'ids.*' => ['integer'],
        ]);

        $ids = $data['ids'];

        if (!$ids) {
            return response()->json(['ok' => false, 'msg' => 'no ids'], 400);
        }

        switch ($data['action']) {

            case 'publish':
                DB::table('petitions')->whereIn('id', $ids)->update([
                    'status' => 'published',
                ]);
                break;

            case 'unpublish':
                DB::table('petitions')->whereIn('id', $ids)->update([
                    'status' => 'draft',
                ]);
                break;

            case 'activate':
                DB::table('petitions')->whereIn('id', $ids)->update([
                    'is_active' => 1,
                ]);
                break;

            case 'deactivate':
                DB::table('petitions')->whereIn('id', $ids)->update([
                    'is_active' => 0,
                ]);
                break;

            case 'feature':
                DB::table('petitions')->whereIn('id', $ids)->update([
                    'is_featured' => 1,
                ]);
                break;

            case 'unfeature':
                DB::table('petitions')->whereIn('id', $ids)->update([
                    'is_featured' => 0,
                ]);
                break;

            case 'ban':
                DB::table('petitions')->whereIn('id', $ids)->update([
                    'status' => 'draft',
                    'is_active' => 0,
                    'is_featured' => 0,
                ]);
                break;

            default:
                return response()->json(['ok' => false, 'msg' => 'not implemented'], 400);
        }

        Cache::forget("petitions:index:{$locale}:page:1");

        for ($i = 1; $i <= 5; $i++) {
            Cache::forget("petitions:index:{$locale}:page:{$i}");
        }

        return response()->json(['ok' => true]);
    }
}
