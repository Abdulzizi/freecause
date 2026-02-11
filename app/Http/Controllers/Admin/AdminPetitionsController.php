<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class AdminPetitionsController extends Controller
{
    public function index(Request $request)
    {
        $locale = $request->query('locale', 'en');

        $filters = [
            'id' => trim((string) $request->query('id', '')),
            'title' => trim((string) $request->query('title', '')),
            'featured' => $request->query('featured', ''),
        ];

        $q = DB::table('petitions')
            ->join('petition_translations as pt', function ($j) use ($locale) {
                $j->on('pt.petition_id', '=', 'petitions.id')
                    ->where('pt.locale', '=', $locale);
            })
            ->select([
                'petitions.id',
                'petitions.signature_count',
                'petitions.goal_signatures',
                'petitions.status',
                'petitions.is_active',
                'petitions.is_featured',
                'petitions.created_at',
                'pt.title',
                'pt.slug',
            ]);

        if ($filters['id'] !== '') {
            $q->where('petitions.id', (int) $filters['id']);
        }

        if ($filters['title'] !== '') {
            $q->where('pt.title', 'like', '%' . $filters['title'] . '%');
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

        DB::table('petition_translations')
            ->updateOrInsert(
                [
                    'petition_id' => $data['id'],
                    'locale' => $data['locale'],
                ],
                [
                    'title' => $data['title'] ?? '',
                    'slug' => $data['slug'] ?? '',
                    'description' => $data['text'] ?? '',
                ]
            );

        return redirect()
            ->route('admin.petitions', ['select' => $data['id'], 'locale' => $data['locale']])
            ->with('success', 'saved');
    }

    private function approxTableRows(string $table): int
    {
        return (int) Cache::remember("approx_rows:$table", 3600, function () use ($table) {
            $db = DB::getDatabaseName();

            $row = DB::table('information_schema.TABLES')
                ->select('TABLE_ROWS')
                ->where('TABLE_SCHEMA', $db)
                ->where('TABLE_NAME', $table)
                ->first();

            return $row ? (int) $row->TABLE_ROWS : 0;
        });
    }
}
