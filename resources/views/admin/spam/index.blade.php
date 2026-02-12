@extends('admin.layouts.app')

@section('title', 'Spam')

@section('content')

    <h1>spam</h1>

    <div class="fc-box" style="margin-bottom:10px;">
        <strong>{{ number_format($bannedCount) }}</strong> IP banned.
    </div>

    <x-admin.list-table-box empty-text="no blocked activity" :p="$logs">

        <x-slot:thead>
            <tr style="border-bottom:1px solid #ccc;">
                <th style="width:120px;">type</th>
                <th style="width:140px;">ip</th>
                <th>payload</th>
                <th style="width:160px;">date</th>
            </tr>
        </x-slot:thead>

        <x-slot:tbody>
            @forelse($logs as $log)
                <tr style="border-bottom:1px solid #eee;">
                    <td>{{ $log->type }}</td>
                    <td>{{ $log->ip }}</td>
                    <td style="max-width:520px;">
                        {{ \Illuminate\Support\Str::limit($log->payload, 120) }}
                    </td>
                    <td>{{ $log->created_at }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4">no blocked activity</td>
                </tr>
            @endforelse
        </x-slot:tbody>

    </x-admin.list-table-box>

@endsection
