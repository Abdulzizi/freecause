<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class AdminSpamController extends Controller
{
    public function index()
    {
        $bannedCount = DB::table('banned_ips')->count();

        $logs = DB::table('spam_logs')
            ->orderByDesc('created_at')
            ->paginate(50)
            ->withQueryString();

        return view('admin.spam.index', compact(
            'bannedCount',
            'logs'
        ));
    }
}
