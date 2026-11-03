<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Language;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
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
            'page' => max(1, (int) $request->query('page', 1)),
        ];

        $from = $this->resolveRange($filters['range']);

        $summary = $this->summaryStats($filters['locale'], $from);

        $columns = [];
        $results = null;

        if ($filters['type']) {
            [$columns, $results] = $this->cachedStat(
                $filters['type'],
                $filters['locale'],
                $filters['range'],
                $filters['page']
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

        $d = app(DashboardController::class)->getStats();
        $usersTotal = $d['users_real'];
        $petitionsTotal = max($d['petitions_total'], $d['petitions_published']);

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
            'users_total' => $usersTotal,
            'users_accounts' => $usersTotal + $d['users_synthetic'],
            'users_synthetic' => $d['users_synthetic'],
            'users_verified_percent' => $usersTotal > 0
                ? round(min($d['users_verified'], $usersTotal) / $usersTotal * 100, 1)
                : 0,

            'petitions_total' => $petitionsTotal,
            'petitions_publish_percent' => $petitionsTotal > 0
                ? round(($d['petitions_published'] / $petitionsTotal) * 100, 1)
                : 0,

            'signatures_total' => $d['signatures_total'],

            'users_new' => $recent['users_new'],
            'petitions_new' => $recent['petitions_new'],
            'signatures_new' => $recent['signatures_new'],
        ];
    }

    private function cachedStat(string $type, string $locale, string $range, int $page): array
    {
        // Unknown types have no query; return before caching so arbitrary strings create no entries.
        if ($this->resolveStat($type, $locale, $this->resolveRange($range))[1] === null) {
            return [[], null];
        }

        $key = 'admin:stat:type:'.$type.':'.($locale ?: 'all').':'.$range.':page:'.$page;

        $cached = Cache::remember($key, 600, function () use ($type, $locale, $range, $page) {
            $from = $this->resolveRange($range);
            [$columns, $query] = $this->resolveStat($type, $locale, $from);

            $items = (clone $query)->forPage($page, 25)->limit(26)->get();

            return [
                'columns' => $columns,
                'items' => $items->take(25)->all(),
                'hasMore' => $items->count() > 25,
            ];
        });

        $paginator = new Paginator($cached['items'], 25, $page);
        $paginator->hasMorePages = $cached['hasMore'];

        return [$cached['columns'], $paginator->withQueryString()];
    }

    private function resolveStat(string $type, string $locale, $from): array
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
            $q,
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
            $q,
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
            $q,
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
            $q,
        ];
    }
}
