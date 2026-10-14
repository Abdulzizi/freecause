@extends('admin.layouts.app')

@section('title', 'Reports')

@section('content')

    <x-admin.page-header title="reports" :count="$reportTotal" />

    @if (session('success'))
        <x-blatui.alert tone="success" class="mt-1.5 mb-2.5">{{ session('success') }}</x-blatui.alert>
    @endif

    @if ($errors->any())
        <x-blatui.alert variant="destructive" class="mb-3">{{ $errors->first() }}</x-blatui.alert>
    @endif

    @php
        $activeFilterCount = collect($filters)->filter(fn ($value) => $value !== '' && $value !== null)->count();
    @endphp

    <x-admin.filter-box title="filter reports" :action="route('admin.reports')" :reset="route('admin.reports')" :activeCount="$activeFilterCount">

        <select name="status" class="fc-filter-field" data-admin-autosubmit>
            <option value="">All statuses</option>
            @foreach (\App\Models\Report::statuses() as $status)
                <option value="{{ $status }}" {{ ($filters['status'] ?? '') === $status ? 'selected' : '' }}>{{ $status }}</option>
            @endforeach
        </select>

        <select name="reportable_type" class="fc-filter-field" data-admin-autosubmit>
            <option value="">All types</option>
            @foreach ($types as $type)
                <option value="{{ $type }}" {{ ($filters['reportable_type'] ?? '') === $type ? 'selected' : '' }}>
                    {{ $typeLabels[$type] ?? \Illuminate\Support\Str::lower(class_basename($type)) }}
                </option>
            @endforeach
        </select>

    </x-admin.filter-box>

    <x-admin.data-table empty-text="no reports found, try clearing filters" :p="$reports" :emptyClearRoute="route('admin.reports')">

        <x-slot:thead>
            <tr data-admin-row-hover class="border-b border-border">
                <th class="w-[70px]">ID</th>
                <th>Reported subject</th>
                <th class="w-[100px]">Type</th>
                <th class="w-[130px]">Reason</th>
                <th class="w-[110px]">Status</th>
                <th class="w-[160px]">Date</th>
            </tr>
        </x-slot:thead>

        <x-slot:tbody>
            @foreach ($reports as $report)
                @php
                    $subject = $report->reportable;
                    $detailUrl = route('admin.reports', array_merge(request()->query(), ['select' => $report->id]));
                @endphp

                <tr data-admin-row-hover data-detail-row data-detail-href="{{ $detailUrl }}" class="border-b border-border">

                    <td>
                        <a href="{{ $detailUrl }}" class="no-underline text-ink">{{ $report->id }}</a>
                    </td>

                    <td>
                        @if (! $subject)
                            <span class="text-muted">deleted</span>
                        @elseif ($subject instanceof \App\Models\Petition)
                            {{ \Illuminate\Support\Str::limit($subject->translationOrFallback()?->title, 70) ?: ('petition #'.$report->reportable_id) }}
                        @elseif ($subject instanceof \App\Models\Signature)
                            {{ \Illuminate\Support\Str::limit($subject->text, 70) ?: ('comment #'.$report->reportable_id) }}
                        @else
                            {{ $report->reportable_type }} #{{ $report->reportable_id }}
                        @endif
                    </td>

                    <td>{{ $typeLabels[$report->reportable_type] ?? \Illuminate\Support\Str::lower(class_basename($report->reportable_type)) }}</td>

                    <td>{{ $report->reason }}</td>

                    <td>
                        <x-blatui.badge :tone="$report->status === 'resolved' ? 'success' : ($report->status === 'dismissed' ? 'neutral' : 'info')">{{ $report->status }}</x-blatui.badge>
                    </td>

                    <td class="whitespace-nowrap">{{ $report->created_at }}</td>

                </tr>
            @endforeach
        </x-slot:tbody>

    </x-admin.data-table>

    <x-admin.detail-panel title="report" :closeRoute="route('admin.reports', request()->except('select'))">

        @if ($selected)
            @php
                $selectedSubject = $selected->reportable;
            @endphp

            <x-admin.form-field label="id">
                <div class="text-ink">{{ $selected->id }}</div>
            </x-admin.form-field>

            <x-admin.form-field label="type">
                <div class="text-ink">{{ $typeLabels[$selected->reportable_type] ?? \Illuminate\Support\Str::lower(class_basename($selected->reportable_type)) }}</div>
            </x-admin.form-field>

            <x-admin.form-field label="reason">
                <div class="text-ink">{{ $selected->reason }}</div>
            </x-admin.form-field>

            <x-admin.form-field label="reported subject">
                @if (! $selectedSubject)
                    <div class="text-muted">deleted ({{ $selected->reportable_type }} #{{ $selected->reportable_id }})</div>
                @elseif ($selectedSubject instanceof \App\Models\Petition)
                    <div class="text-ink">{{ $selectedSubject->translationOrFallback()?->title ?: ('petition #'.$selected->reportable_id) }}</div>
                @elseif ($selectedSubject instanceof \App\Models\Signature)
                    <div class="text-ink whitespace-pre-wrap">{{ $selectedSubject->text ?: ('comment #'.$selected->reportable_id) }}</div>
                @else
                    <div class="text-ink">{{ $selected->reportable_type }} #{{ $selected->reportable_id }}</div>
                @endif
            </x-admin.form-field>

            <x-admin.form-field label="details">
                <div class="text-ink whitespace-pre-wrap">{{ $selected->details ?: '—' }}</div>
            </x-admin.form-field>

            <x-admin.form-field label="reporter">
                <div class="text-ink">{{ $selectedReporter?->name ?? 'guest' }}</div>
            </x-admin.form-field>

            <x-admin.form-field label="ip">
                <div class="text-ink">{{ $selected->ip ?: '—' }}</div>
            </x-admin.form-field>

            <x-admin.form-field label="created">
                <div class="text-ink">{{ $selected->created_at }}</div>
            </x-admin.form-field>

            <x-admin.form-field label="updated">
                <div class="text-ink">{{ $selected->updated_at }}</div>
            </x-admin.form-field>

            @if (admin_can('reports', 'edit'))
                <form method="post" action="{{ route('admin.reports.status') }}" class="m-0 mt-2 border-t border-border pt-3">
                    @csrf
                    <input type="hidden" name="id" value="{{ $selected->id }}">
                    <input type="hidden" name="select" value="{{ $selected->id }}">
                    @if (request('locale'))
                        <input type="hidden" name="locale" value="{{ request('locale') }}">
                    @endif

                    <x-admin.form-field label="status">
                        <select name="status" class="fc-filter-field">
                            @if (! in_array($selected->status, \App\Models\Report::statuses(), true))
                                <option value="{{ $selected->status }}" selected>{{ $selected->status }}</option>
                            @endif
                            @foreach (\App\Models\Report::statuses() as $status)
                                <option value="{{ $status }}" {{ $selected->status === $status ? 'selected' : '' }}>{{ $status }}</option>
                            @endforeach
                        </select>
                    </x-admin.form-field>

                    <div class="flex justify-end mt-1.5 fc-drawer-savebar">
                        <x-blatui.button type="submit">save</x-blatui.button>
                    </div>
                </form>
            @endif
        @else
            <x-admin.empty-state message="select a report to view" />
        @endif

    </x-admin.detail-panel>

@endsection
