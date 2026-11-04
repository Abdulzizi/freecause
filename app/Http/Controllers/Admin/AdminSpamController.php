<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ReviewKeywordRescan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AdminSpamController extends Controller
{
    public function index(Request $request)
    {
        $filters = [
            'ip' => trim((string) $request->query('ip', '')),
            'type' => trim((string) $request->query('type', '')),
            'date_from' => trim((string) $request->query('date_from', '')),
            'date_to' => trim((string) $request->query('date_to', '')),
        ];

        $bannedCount = DB::table('banned_ips')->count();

        $bannedIps = DB::table('banned_ips')
            ->pluck('ip')
            ->toArray();

        $types = DB::table('spam_logs')
            ->select('type')
            ->distinct()
            ->orderBy('type')
            ->pluck('type')
            ->toArray();

        $q = DB::table('spam_logs')->orderByDesc('created_at');

        if ($filters['ip'] !== '') {
            $q->where('ip', 'like', $filters['ip'].'%');
        }

        if ($filters['type'] !== '') {
            $q->where('type', $filters['type']);
        }

        if ($filters['date_from'] !== '') {
            $q->whereDate('created_at', '>=', $filters['date_from']);
        }

        if ($filters['date_to'] !== '') {
            $q->whereDate('created_at', '<=', $filters['date_to']);
        }

        $logs = $q->paginate(50)->withQueryString();

        return view('admin.spam.index', compact(
            'bannedCount',
            'logs',
            'bannedIps',
            'filters',
            'types'
        ));
    }

    public function ban(Request $request)
    {
        $ip = $request->input('ip');

        if (! $ip) {
            return back();
        }

        if (! filter_var($ip, FILTER_VALIDATE_IP)) {
            return back()->with('error', 'Invalid IP address format.');
        }

        if ($ip === $request->ip()) {
            return back()->with('error', 'You cannot ban your own IP.');
        }

        DB::table('banned_ips')->updateOrInsert(
            ['ip' => $ip],
            [
                'reason' => 'manual ban from admin',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
        Cache::forget("banned_ip:{$ip}");

        return back()->with('success', 'IP banned');
    }

    public function unban(Request $request)
    {
        $ip = $request->input('ip');

        if (! $ip || ! filter_var($ip, FILTER_VALIDATE_IP)) {
            return back()->with('error', 'Invalid IP address format.');
        }

        DB::table('banned_ips')
            ->where('ip', $ip)
            ->delete();
        Cache::forget("banned_ip:{$ip}");

        return back()->with('success', 'IP unbanned');
    }

    public function clear()
    {
        DB::table('spam_logs')->where('type', '!=', ReviewKeywordRescan::LOG_TYPE)->delete();

        return back()->with('success', 'Spam logs cleared (petition review holds kept)');
    }
}
