<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class AdminSpamController extends Controller
{
    public function index()
    {
        $bannedCount = DB::table('banned_ips')->count();

        $bannedIps = DB::table('banned_ips')
            ->pluck('ip')
            ->toArray();

        $logs = DB::table('spam_logs')
            ->orderByDesc('created_at')
            ->paginate(50)
            ->withQueryString();

        return view('admin.spam.index', compact(
            'bannedCount',
            'logs',
            'bannedIps'
        ));
    }

    public function ban()
    {
        $ip = request('ip');

        if (!$ip) return back();

        if ($ip === request()->ip()) {
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

        return back()->with('success', 'IP banned');
    }

    public function unban()
    {
        $ip = request('ip');
        if (!$ip) return back();

        DB::table('banned_ips')
            ->where('ip', $ip)
            ->delete();

        return back()->with('success', 'IP unbanned');
    }

    public function clear()
    {
        DB::table('spam_logs')->delete();

        return back()->with('success', 'Spam logs cleared');
    }
}
