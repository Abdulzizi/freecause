@extends('admin.layouts.app')

@section('title', 'Logs')

@section('content')

    <h1>logs ({{ number_format($logs->total()) }})</h1>

    <x-admin.filter-box title="filter logs" :action="route('admin.logs')" :reset="route('admin.logs')">

        <input class="fc-input" name="q" placeholder="type something..." value="{{ $filters['q'] ?? '' }}" style="max-width:260px;">

        <select name="level" class="fc-input" style="max-width:150px;">
            <option value="">All levels</option>
            <option value="info" {{ request('level')=='info'?'selected':'' }}>Info</option>
            <option value="warning" {{ request('level')=='warning'?'selected':'' }}>Warning</option>
            <option value="error" {{ request('level')=='error'?'selected':'' }}>Error</option>
        </select>

        <select name="context" class="fc-input" style="max-width:150px;">
            <option value="">All context</option>
            <option value="auth.register">Auth Register</option>
            <option value="auth.login">Auth Login</option>
            <option value="auth.google">Google OAuth</option>
            <option value="auth.facebook">Facebook OAuth</option>
        </select>
    </x-admin.filter-box>

    <x-admin.list-table-box :p="$logs" :bulk="[
            // 'banRoute' => route('admin.logs.bulkDelete'),
            // 'banLabel' => 'Delete',
            // 'banConfirm' => 'Delete selected logs?',
]">

        <x-slot:thead>
            <tr style="border-bottom:1px solid #ccc;">
                <th style="width:70px;">ID</th>
                <th style="width:80px;">Level</th>
                <th>Content preview</th>
                <th style="width:160px;">Date</th>
            </tr>
        </x-slot:thead>

        <x-slot:tbody>
            @foreach ($logs as $log)
                <tr style="border-bottom:1px solid #eee;" onmouseover="this.style.background='#f7f7f7'" onmouseout="this.style.background=''">

                    <td>
                        <a href="{{ route('admin.logs', array_merge(request()->query(), ['select' => $log->id])) }}" style="text-decoration:none; color:#000;">
                            {{ $log->id }}
                        </a>
                    </td>

                    <td style="text-align:left;">
                        @if($log->level === 'error')
                            <span style="color:#b00020;font-weight:bold;">ERROR</span>
                        @elseif($log->level === 'warning')
                            <span style="color:#c67c00;font-weight:bold;">WARNING</span>
                        @else
                            <span style="color:#2f6f2f;font-weight:bold;">INFO</span>
                        @endif
                    </td>

                    <td>
                        {{-- {{ Str::limit($log->title, 80) }} --}}
                        {{ Str::limit($log->title . ' - ' . $log->content, 80) }}
                    </td>

                    <td style="white-space:nowrap;">
                        {{ $log->created_at }}
                    </td>

                </tr>
            @endforeach
        </x-slot:tbody>

    </x-admin.list-table-box>

    @if($selected)
            <div class="fc-tab">log</div>
            <div class="fc-box">

                <div class="fc-row">
                    <label>date</label>
                    <div>{{ $selected->created_at }}</div>
                </div>

                <div class="fc-row">
                    <label>content</label>
                    <textarea class="fc-textarea" readonly>
                        {{ $selected->content }}
                    </textarea>
                </div>

            </div>
    @endif

@endsection
