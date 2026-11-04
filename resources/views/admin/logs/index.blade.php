@extends('admin.layouts.app')

@section('title', 'Logs')

@section('content')

    <x-admin.page-header title="logs" :count="$logTotal" />

    <x-admin.filter-box title="filter logs" :action="route('admin.logs')" :reset="route('admin.logs')">

        <input class="fc-filter-field" name="q" placeholder="type something..." value="{{ $filters['q'] ?? '' }}">

        <select name="level" class="fc-filter-field" data-admin-autosubmit>
            <option value="">All levels</option>
            <option value="info" {{ request('level')=='info'?'selected':'' }}>Info</option>
            <option value="warning" {{ request('level')=='warning'?'selected':'' }}>Warning</option>
            <option value="error" {{ request('level')=='error'?'selected':'' }}>Error</option>
        </select>

        <select name="context" class="fc-filter-field" data-admin-autosubmit>
            <option value="">All context</option>
            <option value="admin.audit" {{ request('context')=='admin.audit'?'selected':'' }}>Admin Audit</option>
            <option value="auth.register" {{ request('context')=='auth.register'?'selected':'' }}>Auth Register</option>
            <option value="auth.login" {{ request('context')=='auth.login'?'selected':'' }}>Auth Login</option>
            <option value="auth.google" {{ request('context')=='auth.google'?'selected':'' }}>Google OAuth</option>
            <option value="petition.sign" {{ request('context')=='petition.sign'?'selected':'' }}>Petition Signed</option>
        </select>

        <input class="fc-filter-field" name="ip" placeholder="IP" value="{{ $filters['ip'] ?? '' }}">
        <input class="fc-filter-field" type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}">
        <input class="fc-filter-field" type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}">
    </x-admin.filter-box>

    <div class="flex gap-2 mb-3 items-center flex-wrap">
        <x-blatui.button variant="outline" size="sm" :href="route('admin.logs.export', request()->query())">export CSV</x-blatui.button>

        <form method="post" action="{{ route('admin.logs.prune') }}" class="flex gap-1.5 items-center" data-admin-confirm="Delete all logs older than the selected number of days?">
            @csrf
            <select name="days" aria-label="days" class="fc-filter-field">
                <option value="30">30 days</option>
                <option value="60">60 days</option>
                <option value="90" selected>90 days</option>
                <option value="180">180 days</option>
            </select>
            <x-blatui.button variant="destructive" type="submit">prune old logs</x-blatui.button>
        </form>
    </div>

    <x-admin.data-table :p="$logs" :emptyClearRoute="route('admin.logs')" :bulk="[
        'deleteRoute' => route('admin.logs.bulkDelete'),
        'restoreRoute' => route('admin.logs.restore'),
        'noun' => 'logs',
        'emptyMsg' => 'No logs selected',
]">

        <x-slot:thead>
            <tr data-admin-row-hover class="border-b border-border">
                <th class="w-[70px]">ID</th>
                <th width="26" title="Bulk selection"></th>
                <th class="w-[80px]">Level</th>
                <th>Content preview</th>
                <th class="w-[160px]">Date</th>
            </tr>
        </x-slot:thead>

        <x-slot:tbody>
            @foreach ($logs as $log)
                <tr data-admin-row-hover class="border-b border-border hover:bg-surface-muted">

                    <td>
                        <a href="{{ route('admin.logs', array_merge(request()->query(), ['select' => $log->id])) }}" class="no-underline text-ink">
                            {{ $log->id }}
                        </a>
                    </td>

                    <td>
                        <input type="checkbox" class="bulk-checkbox" value="{{ $log->id }}">
                    </td>

                    <td class="text-left">
                        @if($log->level === 'error')
                            <span class="font-bold text-danger">ERROR</span>
                        @elseif($log->level === 'warning')
                            <span class="font-bold text-warning">WARNING</span>
                        @else
                            <span class="font-bold text-success">INFO</span>
                        @endif
                    </td>

                    <td>

                        {{ Str::limit($log->title . ' - ' . $log->content, 80) }}
                    </td>

                    <td class="whitespace-nowrap">
                        {{ $log->created_at }}
                    </td>

                </tr>
            @endforeach
        </x-slot:tbody>

        <x-slot:footer>
            @if (admin_can('logs', 'delete'))
                <x-blatui.button variant="destructive" size="sm" type="button" id="bulk-delete" title="Delete selected logs">
                    <x-lucide-trash-2 aria-hidden="true" />
                    <span class="max-md:hidden">delete selected</span>
                </x-blatui.button>
            @endif
            @if (admin_can('logs', 'delete'))
                <form method="post" action="{{ route('admin.logs.restoreAll') }}" data-admin-confirm="Restore log entries deleted in the last five minutes?">
                    @csrf
                    <x-blatui.button variant="outline" size="sm" type="submit" title="Restore soft-deleted entries (5-minute window)">
                        <span class="max-md:hidden">undo last delete</span>
                    </x-blatui.button>
                </form>
            @endif
        </x-slot:footer>
    </x-admin.data-table>

    @if($selected)
            <x-admin.section-panel title="log">

                <x-admin.form-field label="date">
                    <div>{{ $selected->created_at }}</div>
                </x-admin.form-field>

                <x-admin.form-field label="content">
                    <textarea class="w-full box-border border border-border rounded-sm px-2 py-1.5 text-sm bg-white min-h-[140px] resize-y font-mono" readonly>
                        {{ $selected->content }}
                    </textarea>
                </x-admin.form-field>

            </x-admin.section-panel>
    @endif

@endsection
