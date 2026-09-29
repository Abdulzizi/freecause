<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Language;
use App\Support\ApproxRows;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

class AdminPetitionsController extends Controller
{
    use ApproxRows;

    public function index(Request $request)
    {
        $locale = $request->query('locale', 'en');

        $filters = [
            'id' => trim((string) $request->query('id', '')),
            'title' => trim((string) $request->query('title', '')),
            'owner' => trim((string) $request->query('owner', '')),
            'category' => trim((string) $request->query('category', '')),
            'status' => trim((string) $request->query('status', '')),
            'active' => $request->query('active', ''),
            'featured' => $request->query('featured', ''),
            'missing_locale' => trim((string) $request->query('missing_locale', '')),
        ];

        $defaultLocale = cache()->remember(
            'default_language',
            60,
            fn () => Language::where('is_default', 1)->value('code') ?? 'en'
        );

        $driver = DB::connection()->getDriverName();
        $localesExpr = ($driver === 'mysql' || $driver === 'mariadb')
            ? 'GROUP_CONCAT(locale ORDER BY locale SEPARATOR ",")'
            : 'GROUP_CONCAT(locale, ",")';

        $localesSub = DB::table('petition_translations')
            ->select('petition_id', DB::raw($localesExpr.' as translation_locales'))
            ->groupBy('petition_id');

        $q = DB::table('petitions')
            ->leftJoinSub($localesSub, 'pt_locales', 'pt_locales.petition_id', '=', 'petitions.id')
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
                'pt_locales.translation_locales',
            ]);

        if ($filters['id'] !== '') {
            $q->where('petitions.id', (int) $filters['id']);
        }

        if ($filters['title'] !== '') {
            $like = '%'.str_replace(['%', '_'], ['\%', '\_'], $filters['title']).'%';
            $q->where(function ($sub) use ($like) {
                $sub->where('pt.title', 'like', $like)
                    ->orWhere('pt_default.title', 'like', $like);
            });
        }

        if ($filters['owner'] !== '') {
            $q->where('petitions.user_id', (int) $filters['owner']);
        }

        if ($filters['category'] !== '') {
            $q->where('petitions.category_id', (int) $filters['category']);
        }

        if (in_array($filters['status'], ['draft', 'published'], true)) {
            $q->where('petitions.status', $filters['status']);
        }

        if ($filters['active'] !== '') {
            $q->where('petitions.is_active', $filters['active'] === '1' ? 1 : 0);
        }

        if ($filters['featured'] !== '') {
            $q->where('petitions.is_featured', 1);
        }

        if ($filters['missing_locale'] !== '') {
            $ml = $filters['missing_locale'];
            $q->leftJoin('petition_translations as pt_ml', function ($j) use ($ml) {
                $j->on('pt_ml.petition_id', '=', 'petitions.id')
                    ->where('pt_ml.locale', '=', $ml);
            })
                ->whereNull('pt_ml.petition_id');
        }

        $q->orderByDesc('petitions.id');

        $petitions = $q->simplePaginate(25)->withQueryString();

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

        $activeLanguages = Language::where('is_active', 1)->orderBy('code')->pluck('code')->toArray();

        $defaultLocale = default_locale();
        $categories = Category::query()
            ->select(['categories.id'])
            ->addSelect([
                'name' => DB::table('category_translations')
                    ->select('name')
                    ->whereColumn('category_id', 'categories.id')
                    ->whereIn('locale', [$locale, $defaultLocale])
                    ->orderByRaw('locale = ? desc', [$locale])
                    ->limit(1),
            ])
            ->where('categories.is_active', 1)
            ->orderBy('name')
            ->get();

        return view('admin.petitions.index', compact(
            'petitions',
            'filters',
            'approxTotal',
            'selectedPetition',
            'selectedTranslation',
            'locale',
            'activeLanguages',
            'categories'
        ));
    }

    public function save(Request $request)
    {
        $data = $request->validate([
            'id' => ['required', 'integer', 'exists:petitions,id'],
            'locale' => ['required', 'string', 'exists:languages,code'],
            'status' => ['required', 'in:draft,published'],
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255'],
            'text' => ['nullable', 'string'],
            'goal_signatures' => ['nullable', 'integer', 'in:50,100,1000,5000,10000,50000,100000,500000,1000000,10000000'],
            'is_active' => ['nullable'],
            'is_featured' => ['nullable'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'tags' => ['nullable', 'string', 'max:255'],
            'youtube_url' => ['nullable', 'string', 'max:200'],
            'target' => ['nullable', 'string', 'max:255'],
            'community' => ['nullable', 'string', 'max:255'],
            'community_url' => ['nullable', 'url', 'max:500'],
            'city' => ['nullable', 'string', 'max:120'],
            'created_at' => ['nullable', 'date'],
            'cover_image' => ['nullable', 'image', 'max:4096', 'mimes:jpg,jpeg,png,gif,webp'],
            'remove_image' => ['nullable'],
        ]);

        $slugTaken = DB::table('petition_translations')
            ->where('slug', $data['slug'])
            ->where('locale', $data['locale'])
            ->where('petition_id', '!=', $data['id'])
            ->exists();

        if ($slugTaken) {
            return back()->withErrors(['slug' => 'Slug already used'])->withInput();
        }

        $petitionUpdate = [
            'goal_signatures' => $data['goal_signatures'] ?? 100,
            'status' => $data['status'],
            'is_active' => $request->boolean('is_active'),
            'is_featured' => $request->boolean('is_featured'),
        ];

        foreach (['category_id', 'tags', 'youtube_url', 'target', 'community', 'community_url', 'city'] as $field) {
            if ($request->has($field)) {
                $value = $data[$field] ?? null;
                $petitionUpdate[$field] = ($value === '' ? null : $value);
            }
        }

        if ($request->has('created_at') && ! empty($data['created_at'])) {
            $petitionUpdate['created_at'] = $data['created_at'];
        }

        if (! empty($data['user_id'])) {
            $petitionUpdate['user_id'] = (int) $data['user_id'];
        }

        $newImagePath = null;
        $oldImageToDelete = null;

        try {
            if ($request->hasFile('cover_image')) {
                $newImagePath = $request->file('cover_image')->store('petitions', 'public');
                $abs = Storage::disk('public')->path($newImagePath);
                Image::read($abs)->cover(1200, 630)->toJpeg(82)->save($abs);
                $old = DB::table('petitions')->where('id', $data['id'])->value('cover_image');
                if ($old && ! str_starts_with($old, 'http')) {
                    $oldImageToDelete = $old;
                }
                $petitionUpdate['cover_image'] = $newImagePath;
            } elseif ($request->boolean('remove_image')) {
                $old = DB::table('petitions')->where('id', $data['id'])->value('cover_image');
                if ($old && ! str_starts_with($old, 'http')) {
                    $oldImageToDelete = $old;
                }
                $petitionUpdate['cover_image'] = null;
                $petitionUpdate['image_url'] = null;
            }

            DB::transaction(function () use ($data, $petitionUpdate, $request) {
                DB::table('petitions')
                    ->where('id', $data['id'])
                    ->update($petitionUpdate);

                DB::table('petition_translations')
                    ->updateOrInsert(
                        [
                            'petition_id' => $data['id'],
                            'locale' => $data['locale'],
                        ],
                        [
                            'title' => $data['title'],
                            'slug' => $data['slug'],
                            'description' => sanitizePetitionHtml($data['text'] ?? ''),
                            'is_featured' => $request->boolean('is_featured'),
                        ]
                    );
            });
        } catch (\Throwable $e) {
            if ($newImagePath) {
                Storage::disk('public')->delete($newImagePath);
            }

            throw $e;
        }

        if ($oldImageToDelete) {
            Storage::disk('public')->delete($oldImageToDelete);
        }

        try {
            $locales = active_locales() ?: ['en'];
            foreach ($locales as $l) {
                for ($i = 1; $i <= 10; $i++) {
                    Cache::forget("petitions:index:{$l}:page:{$i}");
                }
                Cache::forget("home:pool:{$l}");
                Cache::forget("home:recent:{$l}");
                $slot = (int) floor(time() / 60);
                for ($s = 0; $s <= 2; $s++) {
                    Cache::forget("home:featured:{$l}:".($slot + $s));
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Cache clear failed after petition save: '.$e->getMessage());
        }

        return redirect()
            ->route('admin.petitions', ['select' => $data['id'], 'locale' => $data['locale']])
            ->with('success', 'saved');
    }

    public function bulkAction(Request $request)
    {
        $locale = $request->input('locale', 'en');

        $data = $request->validate([
            'action' => ['required', 'string', 'in:publish,unpublish,activate,deactivate,feature,unfeature,ban'],
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', 'exists:petitions,id'],
        ]);

        $ids = $data['ids'];

        if (! $ids) {
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
                DB::table('petitions')->whereIn('id', $ids)->update(['is_featured' => 1]);
                // Only feature in the default locale — per-locale featuring is set individually in the edit panel
                $defaultLocale = Language::where('is_default', true)->value('code') ?? 'en';
                DB::table('petition_translations')
                    ->whereIn('petition_id', $ids)
                    ->where('locale', $defaultLocale)
                    ->update(['is_featured' => 1]);
                break;

            case 'unfeature':
                DB::table('petitions')->whereIn('id', $ids)->update(['is_featured' => 0]);
                // Remove from ALL locales — un-featuring is global
                DB::table('petition_translations')->whereIn('petition_id', $ids)->update(['is_featured' => 0]);
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

        try {
            $locales = active_locales() ?: ['en'];

            foreach ($locales as $l) {
                for ($i = 1; $i <= 10; $i++) {
                    Cache::forget("petitions:index:{$l}:page:{$i}");
                }

                Cache::forget("home:pool:{$l}");
                Cache::forget("home:recent:{$l}");

                $slot = (int) floor(time() / 60);
                for ($s = 0; $s <= 2; $s++) {
                    Cache::forget("home:featured:{$l}:".($slot + $s));
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Cache clear failed after petition bulk action: '.$e->getMessage());
        }

        return response()->json(['ok' => true]);
    }

    public function reconcile(Request $request)
    {
        $id = $request->input('id');

        if (! $id) {
            return response()->json(['ok' => false, 'error' => 'Petition ID required']);
        }

        $actualCount = DB::table('signatures')
            ->where('petition_id', $id)
            ->count();

        $updated = DB::table('petitions')
            ->where('id', $id)
            ->update(['signature_count' => $actualCount]);

        Log::info("Signature count reconciled for petition {$id}: {$actualCount}");

        return response()->json([
            'ok' => true,
            'petition_id' => $id,
            'actual_count' => $actualCount,
        ]);
    }

    public function reconcileAll(Request $request)
    {
        $petitions = DB::table('petitions')->select('id')->get();
        $total = 0;

        foreach ($petitions as $p) {
            $actualCount = DB::table('signatures')
                ->where('petition_id', $p->id)
                ->count();

            DB::table('petitions')
                ->where('id', $p->id)
                ->update(['signature_count' => $actualCount]);

            $total++;
        }

        Log::info("All signature counts reconciled: {$total} petitions");

        return response()->json([
            'ok' => true,
            'total_petitions' => $total,
        ]);
    }
}
