<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminStatsController extends Controller
{
    public function index(Request $request)
    {
        $filters = [
            'locale' => $request->query('locale', ''),
            'range'  => $request->query('range', '30d'),
            'type'   => $request->query('type', ''),
        ];

        $from = $this->resolveRange($filters['range']);

        $results = collect();
        $columns = [];

        if ($filters['type']) {
            [$columns, $results] = $this->resolveStat(
                $filters['type'],
                $filters['locale'],
                $from
            );
        }

        return view('admin.stats.index', compact(
            'filters',
            'results',
            'columns'
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

    private function resolveStat(string $type, string $locale, $from)
    {
        switch ($type) {

            case 'top_signers':
                return $this->topSigners($locale, $from);

            case 'top_petitioners':
                return $this->topPetitioners($locale, $from);

            case 'petitions_all':
                return $this->petitionsAll($locale, $from);

            case 'petitions_verified':
                return $this->petitionsVerified($locale, $from);

            case 'emails_all':
                return $this->emailsAll($locale, $from);

            case 'emails_verified':
                return $this->emailsVerified($locale, $from);

            default:
                return [[], collect()];
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
            ->groupBy('email')
            ->orderByDesc('total')
            ->limit(20);

        if ($locale) {
            $q->where('s.locale', $locale);
        }

        return [
            ['Email', 'Total'],
            $q->get()
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
            ->orderByDesc('total')
            ->limit(20);

        return [
            ['Email', 'Total'],
            $q->get()
        ];
    }

    private function petitionsAll($locale, $from)
    {
        $q = DB::table('petitions')
            ->select('id', 'created_at', 'status')
            ->where('created_at', '>=', $from)
            ->orderByDesc('created_at');

        return [
            ['ID', 'Created At', 'Status'],
            $q->limit(200)->get()
        ];
    }

    private function petitionsVerified($locale, $from)
    {
        $q = DB::table('petitions')
            ->where('status', 'published')
            ->where('created_at', '>=', $from)
            ->orderByDesc('created_at');

        return [
            ['ID', 'Created At'],
            $q->select('id', 'created_at')->limit(200)->get()
        ];
    }

    private function emailsAll($locale, $from)
    {
        $q = DB::table('signatures')
            ->select('email')
            ->whereNotNull('email')
            ->where('created_at', '>=', $from)
            ->distinct();

        if ($locale) {
            $q->where('locale', $locale);
        }

        return [
            ['Email'],
            $q->limit(500)->get()
        ];
    }

    private function emailsVerified($locale, $from)
    {
        $q = DB::table('users')
            ->where('verified', 1)
            ->where('created_at', '>=', $from)
            ->select('email');

        return [
            ['Email'],
            $q->limit(500)->get()
        ];
    }
}
