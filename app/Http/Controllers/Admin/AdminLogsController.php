<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminLogBulkRequest;
use App\Models\Log;
use App\Support\ApproxRows;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminLogsController extends Controller
{
    use ApproxRows;

    public function index(Request $request)
    {
        $filters = $this->filters($request);

        $q = $this->filteredQuery(Log::query(), $filters);

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
        $q = $this->filteredQuery(Log::withTrashed(), $this->filters($request))
            ->orderByDesc('id')
            ->limit(10000);

        $ids = $q->pluck('id')->all();

        return response()->streamDownload(function () use ($ids) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['id', 'level', 'context', 'title', 'content', 'ip', 'created_at']);
            Log::withTrashed()
                ->whereIn('id', $ids)
                ->orderByDesc('id')
                ->chunk(500, function ($rows) use ($handle) {
                    foreach ($rows as $row) {
                        fputcsv($handle, [
                            $row->id,
                            csv_safe($row->level),
                            csv_safe($row->context),
                            csv_safe($row->title),
                            csv_safe($row->content),
                            csv_safe($row->ip),
                            $row->created_at,
                        ]);
                    }
                });
            fclose($handle);
        }, 'logs-'.now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv']);
    }

    private function filters(Request $request): array
    {
        return [
            'q' => trim($request->query('q', '')),
            'level' => $request->query('level'),
            'context' => trim($request->query('context', '')),
            'ip' => trim($request->query('ip', '')),
            'date_from' => trim($request->query('date_from', '')),
            'date_to' => trim($request->query('date_to', '')),
        ];
    }

    private function filteredQuery(Builder $q, array $filters): Builder
    {
        if ($filters['q']) {

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

        return $q;
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

        return back()->with(
            'success',
            $restored
                ? "Restored {$restored} recently deleted log entries."
                : 'No recently deleted log entries were eligible for restoration.'
        );
    }

    public function bulkDelete(AdminLogBulkRequest $request)
    {
        $ids = $request->validated()['ids'];

        $deleted = Log::whereIn('id', $ids)->delete();

        return response()->json(['ok' => true, 'deleted' => $deleted, 'restorable' => 1]);
    }

    public function restore(AdminLogBulkRequest $request)
    {
        $ids = $request->validated()['ids'];

        $restored = Log::onlyTrashed()->whereIn('id', $ids)->restore();

        return response()->json(['ok' => true, 'restored' => $restored]);
    }
}
