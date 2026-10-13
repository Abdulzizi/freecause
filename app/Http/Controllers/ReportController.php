<?php

namespace App\Http\Controllers;

use App\Models\Petition;
use App\Models\Report;
use App\Models\Signature;
use App\Support\AppLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ReportController extends Controller
{
    private const TARGETS = [
        'petition' => Petition::class,
        'comment' => Signature::class,
    ];

    public function store(Request $request)
    {
        $data = $request->validate([
            'reportable_type' => ['required', Rule::in(array_keys(self::TARGETS))],
            'reportable_id' => ['required', 'integer'],
            'reason' => ['required', Rule::in(Report::reasons())],
            'details' => ['nullable', 'string', 'max:500'],
        ]);

        $subject = $this->resolve($data['reportable_type'], (int) $data['reportable_id']);

        if (! $subject) {
            toast(__('show.report_missing'), 'warning');

            return back();
        }

        $report = Report::create([
            'reportable_type' => $subject::class,
            'reportable_id' => $subject->getKey(),
            'reason' => $data['reason'],
            'details' => trim((string) ($data['details'] ?? '')) ?: null,
            'reporter_id' => auth()->id(),
            'ip' => $request->ip(),
        ]);

        AppLog::warning(
            'Content reported',
            sprintf(
                '%s #%d reported as "%s" by %s | report #%d | %s',
                $data['reportable_type'],
                $subject->getKey(),
                $data['reason'],
                auth()->id() ? 'user #'.auth()->id() : 'guest',
                $report->id,
                $request->headers->get('referer') ?? '-'
            ),
            'report'
        );

        toast(__('show.report_sent'), 'success');

        return back();
    }

    private function resolve(string $type, int $id): ?Model
    {
        if ($type === 'petition') {
            return Petition::query()
                ->where('id', $id)
                ->where(fn ($query) => $query
                    ->where('status', 'published')
                    ->orWhere('user_id', auth()->id() ?? 0))
                ->first();
        }

        return Signature::query()
            ->where('id', $id)
            ->where('confirmed', true)
            ->where('is_spam', false)
            ->first();
    }
}
