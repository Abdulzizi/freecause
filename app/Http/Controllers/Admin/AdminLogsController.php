<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminLogsController extends Controller
{
    public function index(Request $request)
    {
        $filters = [
            'q' => trim($request->query('q', '')),
            'level' => $request->query('level'),
            'context' => trim($request->query('context', '')),
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

        if ($filters['context']) {
            $q->where('context', $filters['context']);
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

    public function export(Request $request): StreamedResponse
    {
        $filters = [
            'q'       => trim($request->query('q', '')),
            'level'   => $request->query('level'),
            'context' => trim($request->query('context', '')),
        ];

        $q = Log::query()->orderByDesc('id')->limit(10000);

        if ($filters['q']) {
            $q->where(function ($sub) use ($filters) {
                $sub->where('title', 'like', "%{$filters['q']}%")
                    ->orWhere('content', 'like', "%{$filters['q']}%");
            });
        }
        if ($filters['level'])   $q->where('level', $filters['level']);
        if ($filters['context']) $q->where('context', $filters['context']);

        return response()->streamDownload(function () use ($q) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['id', 'level', 'context', 'title', 'content', 'ip', 'created_at']);
            $q->chunk(500, function ($rows) use ($handle) {
                foreach ($rows as $row) {
                    fputcsv($handle, [
                        $row->id,
                        $row->level,
                        $row->context,
                        $row->title,
                        $row->content,
                        $row->ip,
                        $row->created_at,
                    ]);
                }
            });
            fclose($handle);
        }, 'logs-' . now()->format('Y-m-d') . '.csv', ['Content-Type' => 'text/csv']);
    }

    public function prune(Request $request)
    {
        $days = max(1, (int) $request->input('days', 90));
        $deleted = Log::where('created_at', '<', now()->subDays($days))->delete();

        return back()->with('success', "Deleted {$deleted} log entries older than {$days} days.");
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
