<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Log;
use Illuminate\Http\Request;

class AdminLogsController extends Controller
{
    public function index(Request $request)
    {
        $filters = [
            'q' => trim($request->query('q', '')),
            'level' => $request->query('level'),
        ];

        $q = Log::query()->orderByDesc('id');

        if ($filters['q']) {
            $q->where(function ($sub) use ($filters) {
                $sub->where('title', 'like', "%{$filters['q']}%")
                    ->orWhere('content', 'like', "%{$filters['q']}%");
            });
        }

        if ($filters['level']) {
            $q->where('level', $filters['level']);
        }

        $logs = $q->paginate(25)->withQueryString();

        $selected = null;

        if ($request->query('select')) {
            $selected = Log::find($request->query('select'));
        }

        return view('admin.logs.index', compact(
            'filters',
            'logs',
            'selected'
        ));
    }

    public function bulkDelete(Request $request)
    {
        $ids = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer'],
        ])['ids'];

        Log::whereIn('id', $ids)->delete();

        return response()->json(['ok' => true]);
    }
}
