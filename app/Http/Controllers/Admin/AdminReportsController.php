<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Petition;
use App\Models\Report;
use App\Models\Signature;
use App\Models\User;
use App\Support\ApproxRows;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminReportsController extends Controller
{
    use ApproxRows;

    public function index(Request $request)
    {
        $filters = [
            'status' => trim((string) $request->query('status', '')),
            'reportable_type' => trim((string) $request->query('reportable_type', '')),
        ];

        $q = Report::query()->with('reportable')->orderByDesc('id');

        if (in_array($filters['status'], Report::statuses(), true)) {
            $q->where('status', $filters['status']);
        }

        if ($filters['reportable_type'] !== '') {
            $q->where('reportable_type', $filters['reportable_type']);
        }

        $reports = $q->paginate(50)->withQueryString();
        $reportTotal = $this->approxTableRows('reports');

        $selected = null;
        $selectedReporter = null;

        if ($request->query('select')) {
            $selected = Report::with('reportable')->find($request->query('select'));

            if ($selected?->reporter_id) {
                $selectedReporter = User::find($selected->reporter_id);
            }
        }

        $typeLabels = [
            Petition::class => 'petition',
            Signature::class => 'comment',
        ];

        $types = Report::query()
            ->select('reportable_type')
            ->distinct()
            ->orderBy('reportable_type')
            ->pluck('reportable_type');

        return view('admin.reports.index', compact(
            'reports',
            'filters',
            'reportTotal',
            'selected',
            'selectedReporter',
            'types',
            'typeLabels'
        ));
    }

    public function updateStatus(Request $request)
    {
        $data = $request->validate([
            'id' => ['required', 'integer'],
            'status' => ['required', Rule::in(Report::statuses())],
        ]);

        $report = Report::findOrFail($data['id']);
        $report->status = $data['status'];
        $report->save();

        $query = array_filter([
            'select' => $request->input('select', $report->id),
            'locale' => $request->input('locale'),
        ], fn ($value) => filled($value));

        return redirect()
            ->route('admin.reports', $query)
            ->with('success', 'saved');
    }
}
