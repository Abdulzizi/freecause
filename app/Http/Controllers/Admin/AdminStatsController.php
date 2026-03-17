<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Language;
use App\Support\ApproxRows;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AdminStatsController extends Controller
{
    use ApproxRows;
    public function index(Request $request)
    {
        $filters = [
            'locale' => $request->query('locale', ''),
            'range'  => $request->query('range', '30d'),
            'type'   => $request->query('type', ''),
        ];

        $from = $this->resolveRange($filters['range']);

        $summary = $this->summaryStats($filters['locale'], $from);

        $columns = [];
        $results = null;

        if ($filters['type']) {
            [$columns, $results] = $this->resolveStat(
                $filters['type'],
                $filters['locale'],
                $from
            );
        }

        $languages = Language::where('is_active', 1)
            ->orderByDesc('is_default')
            ->get();

        return view('admin.stats.index', compact(
            'filters',
            'summary',
            'columns',
            'results',
            'languages'
        ));
    }

    private function resolveRange(string $range)
    {
        return match ($range) {
            '1d'  => now()->subDay(),
            '7d'  => now()->subDays(7),
            default => now()->subDays(30),
        };
    }

    private function summaryStats($locale, $from)
    {
        // Totals: use approximate row counts (fast, cached 1h) for display
        $usersTotal      = $this->approxTableRows('users');
        $petitionsTotal  = $this->approxTableRows('petitions');
        $signaturesTotal = $this->approxTableRows('signatures');

        // Verified/published ratios: cache for 10 minutes
        $usersVerified = Cache::remember('admin:stats:users_verified', 600, fn() =>
            DB::table('users')->where('verified', 1)->count()
        );
        $petitionsPublished = Cache::remember('admin:stats:petitions_published', 600, fn() =>
            DB::table('petitions')->where('status', 'published')->count()
        );

        // Recent counts: cache 5 min keyed by range + locale
        $rangeKey = $from->toDateString() . ':' . ($locale ?: 'all');
        $usersNew = Cache::remember("admin:stats:users_new:{$rangeKey}", 300, fn() =>
            DB::table('users')->where('created_at', '>=', $from)->count()
        );
        $petitionsNew = Cache::remember("admin:stats:petitions_new:{$rangeKey}", 300, fn() =>
            DB::table('petitions')->where('created_at', '>=', $from)->count()
        );
        $signaturesNew = Cache::remember("admin:stats:sigs_new:{$rangeKey}", 300, fn() =>
            DB::table('signatures')
                ->where('created_at', '>=', $from)
                ->when($locale, fn($q) => $q->where('locale', $locale))
                ->count()
        );

        return [
            'users_total' => $usersTotal,
            'users_verified_percent' => $usersTotal > 0
                ? round(($usersVerified / $usersTotal) * 100, 1)
                : 0,

            'petitions_total' => $petitionsTotal,
            'petitions_publish_percent' => $petitionsTotal > 0
                ? round(($petitionsPublished / $petitionsTotal) * 100, 1)
                : 0,

            'signatures_total' => $signaturesTotal,

            'users_new'      => $usersNew,
            'petitions_new'  => $petitionsNew,
            'signatures_new' => $signaturesNew,
        ];
    }

    private function resolveStat(string $type, string $locale, $from)
    {
        switch ($type) {

            case 'top_signers':
                return $this->topSigners($locale, $from);

            case 'top_petitioners':
                return $this->topPetitioners($locale, $from);

            case 'petitions_all':
                return $this->petitionsAll($locale, $from);

            case 'emails_verified':
                return $this->emailsVerified($locale, $from);

            default:
                return [[], null];
        }
    }

    private function topSigners($locale, $from)
    {
        $q = DB::table('signatures as s')
            ->leftJoin('users as u', 'u.id', '=', 's.user_id')
            ->select(
                DB::raw('COALESCE(u.email, s.email) as email'),
                DB::raw('COUNT(*) as total')
            )
            ->where('s.created_at', '>=', $from)
            ->when($locale, fn($q) => $q->where('s.locale', $locale))
            ->groupBy('email')
            ->orderByDesc('total');

        return [
            ['Email', 'Total Signatures'],
            $q->simplePaginate(25)->withQueryString()
        ];
    }

    private function topPetitioners($locale, $from)
    {
        $q = DB::table('petitions as p')
            ->join('users as u', 'u.id', '=', 'p.user_id')
            ->select(
                'u.email',
                DB::raw('COUNT(*) as total')
            )
            ->where('p.created_at', '>=', $from)
            ->groupBy('u.email')
            ->orderByDesc('total');

        return [
            ['Email', 'Total Petitions'],
            $q->simplePaginate(25)->withQueryString()
        ];
    }

    private function petitionsAll($locale, $from)
    {
        $q = DB::table('petitions')
            ->select('id', 'status', 'created_at')
            ->where('created_at', '>=', $from)
            ->orderByDesc('created_at');

        return [
            ['ID', 'Status', 'Created At'],
            $q->simplePaginate(25)->withQueryString()
        ];
    }

    private function emailsVerified($locale, $from)
    {
        $q = DB::table('users')
            ->where('verified', 1)
            ->where('created_at', '>=', $from)
            ->select('email');

        return [
            ['Verified Emails'],
            $q->simplePaginate(25)->withQueryString()
        ];
    }
}
