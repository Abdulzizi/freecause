<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Language;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AdminStatsController extends Controller
{
    public function index(Request $request)
    {
        $filters = [
            'locale' => $request->query('locale', ''),
            'range' => $request->query('range', '30d'),
            'type' => $request->query('type', ''),
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
            '1d' => now()->subDay(),
            '7d' => now()->subDays(7),
            default => now()->subDays(30),
        };
    }

    private function summaryStats($locale, $from)
    {
        $totals = DB::table('users')
            ->selectRaw('
                COUNT(*) as users_total,
                SUM(verified) as users_verified,
                (SELECT COUNT(*) FROM petitions) as petitions_total,
                (SELECT COUNT(*) FROM petitions WHERE status = "published") as petitions_published,
                (SELECT COUNT(*) FROM signatures) as signatures_total
            ')
            ->first();

        // Recent counts: cache 5 min keyed by range + locale
        $rangeKey = $from->toDateString().':'.($locale ?: 'all');
        $recent = Cache::remember("admin:stats:recent:{$rangeKey}", 300, function () use ($from, $locale) {
            return [
                'users_new' => DB::table('users')->where('created_at', '>=', $from)->count(),
                'petitions_new' => DB::table('petitions')->where('created_at', '>=', $from)->count(),
                'signatures_new' => DB::table('signatures')
                    ->where('created_at', '>=', $from)
                    ->when($locale, fn ($q) => $q->where('locale', $locale))
                    ->count(),
            ];
        });

        return [
            'users_total' => $totals->users_total,
            'users_verified_percent' => $totals->users_total > 0
                ? round(($totals->users_verified / $totals->users_total) * 100, 1)
                : 0,

            'petitions_total' => $totals->petitions_total,
            'petitions_publish_percent' => $totals->petitions_total > 0
                ? round(($totals->petitions_published / $totals->petitions_total) * 100, 1)
                : 0,

            'signatures_total' => $totals->signatures_total,

            'users_new' => $recent['users_new'],
            'petitions_new' => $recent['petitions_new'],
            'signatures_new' => $recent['signatures_new'],
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
            ->when($locale, fn ($q) => $q->where('s.locale', $locale))
            ->groupBy(DB::raw('COALESCE(u.email, s.email)'))
            ->orderByDesc('total');

        return [
            ['Email', 'Total Signatures'],
            $q->simplePaginate(25)->withQueryString(),
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
            $q->simplePaginate(25)->withQueryString(),
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
            $q->simplePaginate(25)->withQueryString(),
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
            $q->simplePaginate(25)->withQueryString(),
        ];
    }
}
