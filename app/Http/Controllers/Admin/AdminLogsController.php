<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Log;
use App\Support\ApproxRows;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminLogsController extends Controller
{
    use ApproxRows;

    public function index(Request $request)
    {
        $filters = [
            'q' => trim($request->query('q', '')),
            'level' => $request->query('level'),
            'context' => trim($request->query('context', '')),
            'ip' => trim($request->query('ip', '')),
            'date_from' => trim($request->query('date_from', '')),
            'date_to' => trim($request->query('date_to', '')),
        ];

        $q = Log::query()->orderByDesc('id');

        if ($filters['q']) {
            // Prefix match on the title (and log text starts-with) only: a
            // leading-wildcard LIKE over longText/long tables is a full scan.
            $like = str_replace(['%', '_'], ['\%', '\_'], $filters['q']).'%';
            $q->where('title', 'like', $like);
        }

        if ($filters['level']) {
            $q->where('level', $filters['level']);
        }

        if ($filters['context']) {
            $q->where('context', $filters['context']);
        }

        if ($filters['ip']) {
            $q->where('ip', 'like', $filters['ip'].'%');
        }

        if ($filters['date_from']) {
            $q->whereDate('created_at', '>=', $filters['date_from']);
        }

        if ($filters['date_to']) {
            $q->whereDate('created_at', '<=', $filters['date_to']);
        }

        // simplePaginate: a full paginate() COUNT(*) over an unbounded logs
        // table ran on every view. The header count comes from the approx
        // table-rows estimate instead.
        $logs = $q->withTrashed()->orderByDesc('id')->simplePaginate(25)->withQueryString();
        $logTotal = $this->approxTableRows('logs');

        $selected = null;

        if ($request->query('select')) {
            $selected = Log::withTrashed()->find($request->query('select'));
        }

        return view('admin.logs.index', compact(
            'filters',
            'logs',
            'logTotal',
            'selected'
        ));
    }

    public function export(Request $request): StreamedResponse
    {
        $filters = [
            'q' => trim($request->query('q', '')),
            'level' => $request->query('level'),
            'context' => trim($request->query('context', '')),
        ];

        $q = Log::withTrashed()->orderByDesc('id')->limit(10000);

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

        // Collect the bounded id set first: a limit() on the streamed query is
        // overridden by chunk(), which would paginate past the 10k cap.
        $ids = $q->pluck('id')->all();

        return response()->streamDownload(function () use ($ids) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['id', 'level', 'context', 'title', 'content', 'ip', 'created_at']);
            Log::query()
                ->whereIn('id', $ids)
                ->orderByDesc('id')
                ->chunk(500, function ($rows) use ($handle) {
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
        }, 'logs-'.now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv']);
    }

    public function prune(Request $request)
    {
        $days = max(1, (int) $request->input('days', 90));
        $deleted = Log::where('created_at', '<', now()->subDays($days))->delete();

        return back()->with('success', "Deleted {$deleted} log entries older than {$days} days (restorable for 5 minutes).");
    }

    public function restoreAll()
    {
        $restored = Log::onlyTrashed()->where('deleted_at', '>=', now()->subMinutes(5))->restore();

        return response()->json(['ok' => true, 'restored' => $restored]);
    }

    public function bulkDelete(Request $request)
    {
        $ids = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer'],
        ])['ids'];

        $deleted = Log::whereIn('id', $ids)->delete();

        return response()->json(['ok' => true, 'deleted' => $deleted, 'restorable' => 1]);
    }

    public function restore(Request $request)
    {
        $ids = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer'],
        ])['ids'];

        $restored = Log::onlyTrashed()->whereIn('id', $ids)->restore();

        return response()->json(['ok' => true, 'restored' => $restored]);
    }
}
