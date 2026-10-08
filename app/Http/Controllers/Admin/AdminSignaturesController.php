<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Language;
use App\Services\PetitionStats;
use App\Support\ApproxRows;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AdminSignaturesController extends Controller
{
    use ApproxRows;

    public function index(Request $request)
    {
        $filters = [
            'petition_id' => trim((string) $request->query('petition_id', '')),
            'user_id' => trim((string) $request->query('user_id', '')),
            'email' => trim((string) $request->query('email', '')),
            'ip' => trim((string) $request->query('ip', '')),
            'text' => trim((string) $request->query('text', '')),
            'category_id' => trim((string) $request->query('category_id', '')),
            'locale' => trim((string) $request->query('locale', '')),
            'confirmed' => $request->query('confirmed', ''),
            'spam' => $request->query('spam', ''),
            'date_from' => trim((string) $request->query('date_from', '')),
            'date_to' => trim((string) $request->query('date_to', '')),
        ];

        // $hasText = Schema::hasColumn('signatures', 'text');
        // $hasConfirmed = Schema::hasColumn('signatures', 'confirmed');

        $hasText = cache()->remember(
            'schema:signatures:has_text',
            3600,
            fn () => Schema::hasColumn('signatures', 'text')
        );
        $hasConfirmed = cache()->remember(
            'schema:signatures:has_confirmed',
            3600,
            fn () => Schema::hasColumn('signatures', 'confirmed')
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

        $hasActiveFilters = false;
        foreach ($filters as $filterValue) {
            if ((string) $filterValue !== '') {
                $hasActiveFilters = true;
                break;
            }
        }

        $useSignatureFirst = ! $hasActiveFilters
            && in_array(DB::connection()->getDriverName(), ['mysql', 'mariadb'], true);

        $q = DB::table('signatures as s');

        if ($useSignatureFirst) {
            $q->fromRaw('signatures as s force index (primary) straight_join petitions as p on p.id = s.petition_id');
        } else {
            $q->join('petitions as p', 'p.id', '=', 's.petition_id');
        }

        $q->leftJoin('users as u', 'u.id', '=', 's.user_id')
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

        if ($filters['user_id'] !== '') {
            $q->where('s.user_id', (int) $filters['user_id']);
        }

        if ($filters['email'] !== '') {
            $q->where('s.email', 'like', $filters['email'].'%');
        }

        if ($filters['ip'] !== '') {
            $q->where('s.ip_address', 'like', $filters['ip'].'%');
        }

        if ($hasText && $filters['text'] !== '') {
            $q->where('s.text', 'like', $filters['text'].'%');
        }

        if ($filters['category_id'] !== '') {
            $q->where('p.category_id', (int) $filters['category_id']);
        }

        if ($filters['locale'] !== '') {
            $q->where('s.locale', $filters['locale']);
        }

        if ($hasConfirmed && $filters['confirmed'] !== '') {
            $q->where('s.confirmed', (int) $filters['confirmed']);
        }

        if ($filters['spam'] !== '') {
            $q->where('s.is_spam', (int) $filters['spam']);
        }

        if ($filters['date_from'] !== '') {
            $q->whereDate('s.created_at', '>=', $filters['date_from']);
        }

        if ($filters['date_to'] !== '') {
            $q->whereDate('s.created_at', '<=', $filters['date_to']);
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

    public function bulkAction(Request $request)
    {
        $data = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', 'exists:signatures,id'],
            'action' => ['required', 'string', 'in:approve,reject,delete'],
        ]);

        $ids = $data['ids'];

        $permission = $data['action'] === 'delete' ? 'delete' : 'edit';
        if (! admin_can('signatures', $permission)) {
            return response()->json(['ok' => false, 'error' => "You do not have permission to {$permission} signatures."], 403);
        }

        $petitionIds = DB::table('signatures')->whereIn('id', $ids)->pluck('petition_id')->unique()->all();

        DB::transaction(function () use ($data, $ids, $petitionIds) {
            match ($data['action']) {
                'approve' => DB::table('signatures')->whereIn('id', $ids)->update(['confirmed' => 1]),
                'reject' => DB::table('signatures')->whereIn('id', $ids)->update(['confirmed' => 0]),
                'delete' => DB::table('signatures')->whereIn('id', $ids)->delete(),
            };

            PetitionStats::recalculateMany($petitionIds);
        });

        return response()->json(['ok' => true]);
    }

    public function action(Request $request, int $signature)
    {
        $data = $request->validate([
            'action' => ['required', 'string', 'in:approve,reject'],
        ]);

        $targetConfirmed = $data['action'] === 'approve' ? 1 : 0;
        $result = DB::transaction(function () use ($signature, $targetConfirmed) {
            $row = DB::table('signatures')->where('id', $signature)->lockForUpdate()->first();

            if (! $row) {
                return ['status' => 'missing'];
            }

            if ((int) $row->confirmed === $targetConfirmed) {
                return ['status' => $targetConfirmed ? 'confirmed' : 'rejected'];
            }

            DB::table('signatures')->where('id', $signature)->update(['confirmed' => $targetConfirmed]);
            PetitionStats::recalculateMany([(int) $row->petition_id]);

            return ['status' => 'updated'];
        });

        if ($result['status'] === 'missing') {
            return response()->json(['ok' => false, 'msg' => 'Signature not found'], 404);
        }

        if ($result['status'] === 'confirmed') {
            return response()->json(['ok' => false, 'msg' => 'Already confirmed'], 409);
        }

        if ($result['status'] === 'rejected') {
            return response()->json(['ok' => false, 'msg' => 'Already rejected'], 409);
        }

        return response()->json(['ok' => true]);
    }
}
