<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Language;
use App\Models\Petition;
use App\Models\PetitionTranslation;
use App\Support\ApproxRows;
use App\Support\CacheVersion;
use App\Support\ImageVariants;
use App\Support\Locale;
use App\Support\PetitionCache;
use App\Support\Slug;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
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
        $isMysql = $driver === 'mysql' || $driver === 'mariadb';
        $localesExpr = $isMysql
            ? 'GROUP_CONCAT(locale ORDER BY locale SEPARATOR ",")'
            : 'GROUP_CONCAT(locale, ",")';

        // Stage 1: id-only page query. The old whole-table GROUP_CONCAT
        // derived join materialized ~2x table every render; filters that need
        // translations run as EXISTS subqueries over the unique
        // (petition_id, locale) index instead.
        $idQuery = DB::table('petitions')->select('petitions.id');

        if ($filters['id'] !== '') {
            $idQuery->where('petitions.id', (int) $filters['id']);
        }

        if ($filters['title'] !== '') {
            // Leading-wildcard LIKE cannot use the fulltext index; scope the
            // search to the locales the editor is working with.
            $searchTerm = $filters['title'];
            $searchLocales = array_values(array_unique([$locale, $defaultLocale]));
            $idQuery->whereIn('petitions.id', function ($sub) use ($searchTerm, $searchLocales, $isMysql) {
                $sub->from('petition_translations')
                    ->select('petition_id')
                    ->whereIn('locale', $searchLocales);
                if ($isMysql) {
                    $sub->whereRaw('MATCH (title) AGAINST (? IN BOOLEAN MODE)', [$searchTerm]);
                } else {
                    $like = '%'.str_replace(['%', '_'], ['\%', '\_'], $searchTerm).'%';
                    $sub->where('title', 'like', $like);
                }
            });
        }

        if ($filters['owner'] !== '') {
            $idQuery->where('petitions.user_id', (int) $filters['owner']);
        }

        if ($filters['category'] !== '') {
            $idQuery->where('petitions.category_id', (int) $filters['category']);
        }

        if (in_array($filters['status'], ['draft', 'published'], true)) {
            $idQuery->where('petitions.status', $filters['status']);
        }

        if ($filters['active'] !== '') {
            $idQuery->where('petitions.is_active', $filters['active'] === '1' ? 1 : 0);
        }

        if ($filters['featured'] !== '') {
            $idQuery->where('petitions.is_featured', 1);
        }

        if ($filters['missing_locale'] !== '') {
            $ml = $filters['missing_locale'];
            $idQuery->whereNotExists(function ($sub) use ($ml) {
                $sub->select(DB::raw(1))
                    ->from('petition_translations as pt_ml')
                    ->whereColumn('pt_ml.petition_id', 'petitions.id')
                    ->where('pt_ml.locale', $ml);
            });
        }

        $idPage = $idQuery->orderByDesc('petitions.id')->simplePaginate(25)->withQueryString();

        // Stage 2: hydrate the 25 page rows (titles, slugs, locale chips)
        // with narrow whereIn queries — never the whole table.
        $pageIds = collect($idPage->items())->pluck('id')->all();

        if ($pageIds !== []) {
            $rows = DB::table('petitions')
                ->leftJoin('petition_translations as pt', function ($j) use ($locale) {
                    $j->on('pt.petition_id', '=', 'petitions.id')
                        ->where('pt.locale', '=', $locale);
                })
                ->leftJoin('petition_translations as pt_default', function ($j) use ($defaultLocale) {
                    $j->on('pt_default.petition_id', '=', 'petitions.id')
                        ->where('pt_default.locale', '=', $defaultLocale);
                })
                ->whereIn('petitions.id', $pageIds)
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
                ])
                ->get()
                ->keyBy('id');

            $chips = DB::table('petition_translations')
                ->select('petition_id', DB::raw($localesExpr.' as translation_locales'))
                ->whereIn('petition_id', $pageIds)
                ->groupBy('petition_id')
                ->pluck('translation_locales', 'petition_id');

            $idPage->setCollection(
                collect($pageIds)->map(fn ($id) => (function () use ($rows, $chips, $id) {
                    $row = $rows->get($id);
                    if ($row) {
                        $row->translation_locales = $chips[$id] ?? null;
                    }

                    return $row;
                })())->filter()->values()
            );
        }

        $petitions = $idPage;

        $approxTotal = $this->approxTableRows('petitions');

        $selectedId = $request->query('select');
        $selectedPetition = null;
        $selectedTranslation = null;
        $selectedDefaultTranslation = null;

        if ($selectedId) {
            $selectedPetition = DB::table('petitions')
                ->where('id', (int) $selectedId)
                ->first();

            $selectedTranslation = DB::table('petition_translations')
                ->where('petition_id', (int) $selectedId)
                ->where('locale', $locale)
                ->first();

            if (! $selectedTranslation && $locale !== $defaultLocale) {
                $selectedDefaultTranslation = DB::table('petition_translations')
                    ->where('petition_id', (int) $selectedId)
                    ->where('locale', $defaultLocale)
                    ->first();
            }
        }

        $localeOptions = Locale::options();
        $activeLanguages = array_keys($localeOptions);

        $missingTranslationCount = 0;
        if ($selectedPetition) {
            $existingLocales = DB::table('petition_translations')
                ->where('petition_id', $selectedPetition->id)
                ->pluck('locale')
                ->all();
            $missingTranslationCount = count(array_diff($activeLanguages, $existingLocales));
        }

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
            'selectedDefaultTranslation',
            'locale',
            'activeLanguages',
            'localeOptions',
            'categories',
            'missingTranslationCount'
        ));
    }

    public function save(Request $request)
    {
        $data = $request->validate([
            'id' => ['required', 'integer', 'exists:petitions,id'],
            'locale' => ['required', 'string', Rule::exists('languages', 'code')->where('is_active', 1)],
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
                ImageVariants::ensure($abs);
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

            // Featured flags: global petitions.is_featured is the single source
            // of truth for the admin list. Mirror it onto the edited locale's
            // translation, and when un-featuring clear is_featured in ALL
            // translations of the petition so admin and public stay in sync.
            $isFeatured = $request->boolean('is_featured');

            DB::transaction(function () use ($data, $petitionUpdate, $isFeatured) {
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
                            'is_featured' => $isFeatured,
                        ]
                    );

                if (! $isFeatured) {
                    DB::table('petition_translations')
                        ->where('petition_id', $data['id'])
                        ->update(['is_featured' => 0]);
                }
            });
        } catch (\Throwable $e) {
            if ($newImagePath) {
                Storage::disk('public')->delete($newImagePath);
                ImageVariants::purge($newImagePath);
            }

            throw $e;
        }

        if ($oldImageToDelete) {
            Storage::disk('public')->delete($oldImageToDelete);
            ImageVariants::purge($oldImageToDelete);
        }

        try {
            if ($petition = Petition::find($data['id'])) {
                PetitionCache::touch($petition);
            }

            CacheVersion::bump('home');
            CacheVersion::bump('list');
            CacheVersion::bump('sitemap');
        } catch (\Throwable $e) {
            Log::warning('Cache bump failed after petition save: '.$e->getMessage());
        }

        $viewUrl = null;
        try {
            $row = DB::table('petition_translations')
                ->where('petition_id', $data['id'])
                ->where('locale', $data['locale'])
                ->first();
            if ($row?->slug) {
                $viewUrl = route('petition.show', ['locale' => $data['locale'], 'slug' => $row->slug, 'id' => $data['id']]);
            }
        } catch (\Throwable $e) {
        }

        return redirect()
            ->route('admin.petitions', ['select' => $data['id'], 'locale' => $data['locale']])
            ->with('success', 'saved')
            ->with('view_url', $viewUrl);
    }

    public function create(Request $request)
    {
        // Mirrors save()'s rules: goal whitelist, title length, active-language
        // locales, category / goal typing.
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'locale' => ['required', 'string', Rule::exists('languages', 'code')->where('is_active', 1)],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'goal_signatures' => ['nullable', 'integer', 'in:50,100,1000,5000,10000,50000,100000,500000,1000000,10000000'],
        ]);

        $locale = $data['locale'];

        // Slugs are unique per (locale, slug) — dedupe against that scope.
        $slug = Slug::dedupe(
            $data['title'],
            fn (string $candidate) => DB::table('petition_translations')
                ->where('locale', $locale)
                ->where('slug', $candidate)
                ->exists(),
            'petition'
        );

        $petitionId = DB::transaction(function () use ($data, $locale, $slug) {
            $now = now();

            $id = DB::table('petitions')->insertGetId([
                'user_id' => admin_user()->id,
                'goal_signatures' => $data['goal_signatures'] ?? 1000,
                'status' => 'draft', // petitions always start unpublished
                'category_id' => $data['category_id'] ?? null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('petition_translations')->insert([
                'petition_id' => $id,
                'locale' => $locale,
                'title' => $data['title'],
                'slug' => $slug,
                'description' => '',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            return (int) $id;
        });

        try {
            if ($petition = Petition::find($petitionId)) {
                PetitionCache::touch($petition);
            }
        } catch (\Throwable $e) {
            Log::warning('Cache bump failed after petition create: '.$e->getMessage());
        }

        return redirect()
            ->route('admin.petitions', ['select' => $petitionId, 'locale' => $locale])
            ->with('success', 'petition #'.$petitionId.' created');
    }

    public function translateMissing(Request $request)
    {
        $data = $request->validate([
            'id' => ['required', 'integer', 'exists:petitions,id'],
            'locale' => ['nullable', 'string', Rule::exists('languages', 'code')->where('is_active', 1)],
        ]);

        $id = (int) $data['id'];
        $sourceLocale = $data['locale'] ?? default_locale();

        $source = DB::table('petition_translations')
            ->where('petition_id', $id)
            ->where('locale', $sourceLocale)
            ->first();

        if (! $source) {
            return response()->json(['ok' => false, 'error' => 'no source translation'], 404);
        }

        $existing = DB::table('petition_translations')
            ->where('petition_id', $id)
            ->pluck('locale')
            ->all();

        $targets = Language::where('is_active', 1)
            ->pluck('code')
            ->reject(fn (string $code) => in_array($code, $existing, true))
            ->values();

        if ($targets->isEmpty()) {
            return response()->json(['ok' => true, 'created' => 0, 'missing' => 0]);
        }

        // slug is unique per (locale, slug), so the source slug can be
        // reused verbatim for every new locale.
        $created = DB::transaction(function () use ($source, $targets, $id) {
            foreach ($targets as $code) {
                PetitionTranslation::updateOrCreate(
                    ['petition_id' => $id, 'locale' => $code],
                    [
                        'title' => $source->title,
                        'slug' => $source->slug,
                        'description' => $source->description,
                        'is_featured' => false, // drafts seed the copy, publishing stays manual
                    ]
                );
            }

            return count($targets);
        });

        try {
            if ($petition = Petition::find($id)) {
                PetitionCache::touch($petition);
            }

            CacheVersion::bump('list'); // also purges the edge listing cache
        } catch (\Throwable $e) {
            Log::warning('Cache bump failed after translate-missing: '.$e->getMessage());
        }

        return response()->json(['ok' => true, 'created' => $created, 'missing' => 0]);
    }

    public function bulkAction(Request $request)
    {
        $locale = $request->input('locale', 'en');

        $data = $request->validate([
            'action' => ['required', 'string', 'in:publish,unpublish,activate,deactivate,feature,unfeature,ban'],
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', 'distinct', 'exists:petitions,id'],
            'reason' => ['nullable', 'string', 'max:255'],
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
            PetitionCache::bump();
        } catch (\Throwable $e) {
            Log::warning('Cache bump failed after petition bulk action: '.$e->getMessage());
        }

        return response()->json(['ok' => true]);
    }

    public function reconcile(Request $request)
    {
        $data = $request->validate([
            'id' => ['required', 'integer', 'exists:petitions,id'],
        ]);
        $id = (int) $data['id'];

        $actualCount = DB::table('signatures')
            ->where('petition_id', $id)
            ->where('confirmed', 1) // canonical count = confirmed signatures only
            ->count();

        $updated = DB::table('petitions')
            ->where('id', $id)
            ->update(['signature_count' => $actualCount]);

        if ($petition = Petition::find($id)) {
            PetitionCache::touch($petition);
        }

        Log::info("Signature count reconciled for petition {$id}: {$actualCount}");

        return response()->json([
            'ok' => true,
            'petition_id' => $id,
            'actual_count' => $actualCount,
        ]);
    }

    public function reconcileAll(Request $request)
    {
        $total = Petition::count();

        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement(<<<'SQL'
                UPDATE petitions AS p
                LEFT JOIN (
                    SELECT petition_id, COUNT(*) AS actual_count
                    FROM signatures
                    WHERE confirmed = 1
                    GROUP BY petition_id
                ) AS s ON s.petition_id = p.id
                SET p.signature_count = COALESCE(s.actual_count, 0)
            SQL);
        } else {
            DB::table('petitions')->update([
                'signature_count' => DB::raw(
                    '(SELECT COUNT(*) FROM signatures WHERE signatures.petition_id = petitions.id AND signatures.confirmed = 1)'
                ),
            ]);
        }

        // Counts are derived from confirmed signatures; invalidate shared
        // listing caches once instead of dispatching one purge per petition.
        PetitionCache::bump();

        Log::info("All signature counts reconciled: {$total} petitions");

        return response()->json([
            'ok' => true,
            'total_petitions' => $total,
        ]);
    }
}
