<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Language;
use App\Support\ApproxRows;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AdminSignaturesController extends Controller
{
    use ApproxRows;

    public function index(Request $request)
    {
        $filters = [
            'petition_id' => trim((string) $request->query('petition_id', '')),
            'email'       => trim((string) $request->query('email', '')),
            'text'        => trim((string) $request->query('text', '')),
            'category_id' => trim((string) $request->query('category_id', '')),
            'locale'      => trim((string) $request->query('locale', '')),
        ];

        // $hasText = Schema::hasColumn('signatures', 'text');
        // $hasConfirmed = Schema::hasColumn('signatures', 'confirmed');

        $hasText = cache()->remember(
            'schema:signatures:has_text',
            3600,
            fn() => Schema::hasColumn('signatures', 'text')
        );
        $hasConfirmed = cache()->remember(
            'schema:signatures:has_confirmed',
            3600,
            fn() => Schema::hasColumn('signatures', 'confirmed')
        );

        $catLocale = $filters['locale'] !== '' ? $filters['locale'] : 'en';

        $categories = DB::table('category_translations as ct')
            ->join('categories as c', 'c.id', '=', 'ct.category_id')
            ->where('ct.locale', $catLocale)
            ->select([
                'c.id',
                'ct.name',
            ])
            ->orderBy('c.id')
            ->get();

        $q = DB::table('signatures as s')
            ->join('petitions as p', 'p.id', '=', 's.petition_id')
            ->leftJoin('users as u', 'u.id', '=', 's.user_id')
            ->leftJoin('petition_translations as pt', function ($j) {
                $j->on('pt.petition_id', '=', 'p.id')
                    ->on('pt.locale', '=', 's.locale');
            })
            ->select(array_filter([
                's.id',
                's.petition_id',
                's.user_id',
                's.name',
                's.email',
                's.locale',
                $hasText ? 's.text' : null,
                $hasConfirmed ? 's.confirmed' : null,
                's.created_at',
                'p.category_id',
                DB::raw("COALESCE(pt.title, '') as petition_title"),
                'pt.slug as petition_slug',
                'u.name as user_name',
                'u.email as user_email',
                'u.verified as user_verified',
            ]));

        if ($filters['petition_id'] !== '') {
            $q->where('s.petition_id', (int) $filters['petition_id']);
        }

        if ($filters['email'] !== '') {
            $q->where('s.email', 'like', $filters['email'] . '%');
        }

        if ($hasText && $filters['text'] !== '') {
            $q->where('s.text', 'like', $filters['text'] . '%');
        }

        if ($filters['category_id'] !== '') {
            $q->where('p.category_id', (int) $filters['category_id']);
        }

        if ($filters['locale'] !== '') {
            $q->where('s.locale', $filters['locale']);
        }

        $q->orderByDesc('s.id');

        $signatures = $q->simplePaginate(25)->withQueryString();
        $approxTotal = $this->approxTableRows('signatures');

        $languages = Language::where('is_active', true)
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get();

        $locales = ['' => '(Locale)'];
        foreach ($languages as $lang) {
            $locales[$lang->code] = $lang->name;
        }

        return view('admin.signatures.index', compact(
            'signatures',
            'filters',
            'categories',
            'approxTotal',
            'hasText',
            'hasConfirmed',
            'locales'
        ));
    }

    public function bulkDelete(Request $request)
    {
        $data = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer'],
        ]);

        DB::table('signatures')
            ->whereIn('id', $data['ids'])
            ->delete();

        return response()->json(['ok' => true]);
    }
}
