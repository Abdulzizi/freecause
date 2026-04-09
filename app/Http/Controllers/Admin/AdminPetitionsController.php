<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\ApproxRows;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use App\Models\Language;
use Illuminate\Support\Facades\Log;
use Intervention\Image\Laravel\Facades\Image;

class AdminPetitionsController extends Controller
{
    use ApproxRows;

    public function index(Request $request)
    {
        $locale = $request->query('locale', 'en');

        $filters = [
            'id'             => trim((string) $request->query('id', '')),
            'title'          => trim((string) $request->query('title', '')),
            'featured'       => $request->query('featured', ''),
            'missing_locale' => trim((string) $request->query('missing_locale', '')),
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
                DB::raw('(SELECT GROUP_CONCAT(locale ORDER BY locale SEPARATOR ",") FROM petition_translations WHERE petition_id = petitions.id) as translation_locales'),
            ]);

        if ($filters['id'] !== '') {
            $q->where('petitions.id', (int) $filters['id']);
        }

        if ($filters['title'] !== '') {
            $safeTitle = preg_replace('/[+\-<>~*()"@]+/', ' ', $filters['title']);
            $safeTitle = trim($safeTitle);
            if ($safeTitle !== '') {
                $q->whereRaw(
                    "MATCH(pt.title) AGAINST(? IN BOOLEAN MODE) OR MATCH(pt_default.title) AGAINST(? IN BOOLEAN MODE)",
                    ['+' . $safeTitle . '*', '+' . $safeTitle . '*']
                );
            }
        }

        if ($filters['featured'] !== '') {
            $q->where('petitions.is_featured', 1);
        }

        if ($filters['missing_locale'] !== '') {
            $ml = $filters['missing_locale'];
            $q->whereNotExists(function ($sub) use ($ml) {
                $sub->select(DB::raw(1))
                    ->from('petition_translations')
                    ->whereColumn('petition_id', 'petitions.id')
                    ->where('locale', $ml);
            });
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

        return view('admin.petitions.index', compact(
            'petitions',
            'filters',
            'approxTotal',
            'selectedPetition',
            'selectedTranslation',
            'locale',
            'activeLanguages'
        ));
    }

    public function save(Request $request)
    {
        $data = $request->validate([
            'id'              => ['required', 'integer'],
            'locale'          => ['required', 'string'],
            'title'           => ['nullable', 'string'],
            'slug'            => ['nullable', 'string'],
            'text'            => ['nullable', 'string'],
            'goal_signatures' => ['nullable', 'integer'],
            'is_active'       => ['nullable'],
            'status'          => ['required', 'string'],
            'is_featured'     => ['nullable'],
            'user_id'         => ['nullable', 'integer', 'exists:users,id'],
            'cover_image'     => ['nullable', 'image', 'max:4096', 'mimes:jpg,jpeg,png,gif,webp'],
            'remove_image'    => ['nullable'],
        ]);

        $petitionUpdate = [
            'goal_signatures' => $data['goal_signatures'] ?? 100,
            'status'          => $data['status'],
            'is_active'       => $request->boolean('is_active'),
            'is_featured'     => $request->boolean('is_featured'),
        ];

        // Transfer petition ownership if a valid new user_id was provided
        if (!empty($data['user_id'])) {
            $petitionUpdate['user_id'] = (int) $data['user_id'];
        }

        // Handle cover image replacement
        if ($request->hasFile('cover_image')) {
            $path = $request->file('cover_image')->store('petitions', 'public');
            $abs  = Storage::disk('public')->path($path);
            Image::read($abs)->cover(1200, 630)->toJpeg(82)->save($abs);
            // Delete old image if it was a stored file
            $old = DB::table('petitions')->where('id', $data['id'])->value('cover_image');
            if ($old && !str_starts_with($old, 'http')) {
                Storage::disk('public')->delete($old);
            }
            $petitionUpdate['cover_image'] = $path;
        } elseif ($request->boolean('remove_image')) {
            $old = DB::table('petitions')->where('id', $data['id'])->value('cover_image');
            if ($old && !str_starts_with($old, 'http')) {
                Storage::disk('public')->delete($old);
            }
            $petitionUpdate['cover_image'] = null;
            $petitionUpdate['image_url']   = null;
        }

        DB::table('petitions')
            ->where('id', $data['id'])
            ->update($petitionUpdate);

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

        try {
            $locales = active_locales() ?: ["en"];
            foreach ($locales as $l) {
                for ($i = 1; $i <= 10; $i++) {
                    Cache::forget("petitions:index:{$l}:page:{$i}");
                }
                Cache::forget("home:pool:{$l}");
                Cache::forget("home:recent:{$l}");
                $slot = (int) floor(time() / 60);
                for ($s = 0; $s <= 2; $s++) {
                    Cache::forget("home:featured:{$l}:" . ($slot + $s));
                }
            }
        } catch (\Throwable $e) {
            Log::warning("Cache clear failed after petition save: " . $e->getMessage());
        }

        return redirect()
            ->route("admin.petitions", ["select" => $data["id"], "locale" => $data["locale"]])
            ->with("success", "saved");
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
                    Cache::forget("home:featured:{$l}:" . ($slot + $s));
                }
            }
        } catch (\Throwable $e) {
            Log::warning("Cache clear failed after petition bulk action: " . $e->getMessage());
        }

        return response()->json(['ok' => true]);
    }
}
