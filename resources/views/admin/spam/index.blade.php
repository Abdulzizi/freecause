@extends('admin.layouts.app')

@section('title', 'Spam')

@section('content')

    <x-admin.page-header title="spam" />

    <div class="border border-border rounded p-[10px] mb-[15px]">
        <strong>{{ number_format($bannedCount) }}</strong> IP banned.
    </div>

    <x-admin.filter-box title="filter spam" :action="route('admin.spam')" :reset="route('admin.spam')">
        <input class="fc-filter-field" name="ip" placeholder="IP" value="{{ $filters['ip'] ?? '' }}">

        <select class="fc-filter-field" name="type" data-admin-autosubmit>
            <option value="">type...</option>
            @foreach ($types as $t)
                <option value="{{ $t }}" {{ ($filters['type'] ?? '') === $t ? 'selected' : '' }}>{{ $t }}</option>
            @endforeach
        </select>

        <input class="fc-filter-field" type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}">
        <input class="fc-filter-field" type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}">
    </x-admin.filter-box>

    <form method="post" action="{{ route('admin.spam.clear') }}" data-admin-confirm="You are about to permanently delete all spam logs. Continue?" class="mb-[15px]">
        @csrf
        <x-blatui.button variant="destructive" type="submit">clear spam logs</x-blatui.button>
    </form>

    <x-admin.data-table empty-text="no blocked activity" :p="$logs" :emptyClearRoute="route('admin.spam')">

        <x-slot:thead>
            <tr data-admin-row-hover class="border-b border-border">
                <th class="w-[100px]">type</th>
                <th class="w-[150px]">ip</th>
                <th>payload</th>
                <th class="w-[160px]">date</th>
                <th class="fc-row-actions-cell">actions</th>
            </tr>
        </x-slot:thead>

        <x-slot:tbody>
            @forelse($logs as $log)
                <tr data-admin-row-hover
                    class="border-b border-border @if (in_array($log->ip, $bannedIps)) bg-danger/5 @endif">

                    <td>{{ $log->type }}</td>

                    <td>
                        <strong>{{ $log->ip }}</strong>
                    </td>

                    <td
                        class="max-w-[320px] whitespace-nowrap overflow-hidden text-ellipsis">
                        {{ \Illuminate\Support\Str::limit($log->payload, 80) }}
                    </td>

                    <td>{{ $log->created_at }}</td>

                    <td class="fc-row-actions-cell">

                        <x-admin.row-actions>
                            <x-blatui.dropdown-menu-item type="button" data-payload-toggle="{{ $log->id }}">View payload</x-blatui.dropdown-menu-item>

                            @if (in_array($log->ip, $bannedIps))
                                <form method="post" action="{{ route('admin.spam.unban') }}"
                                    data-admin-confirm="Unban this IP? {{ $log->ip }}">
                                    @csrf
                                    <input type="hidden" name="ip" value="{{ $log->ip }}">
                                    <x-blatui.dropdown-menu-item type="submit" variant="destructive">Unban IP</x-blatui.dropdown-menu-item>
                                </form>
                            @else
                                <form method="post" action="{{ route('admin.spam.ban') }}"
                                    data-admin-confirm="Are you sure you want to ban this IP? {{ $log->ip }} This will block access immediately.">
                                    @csrf
                                    <input type="hidden" name="ip" value="{{ $log->ip }}">
                                    <x-blatui.dropdown-menu-item type="submit" variant="destructive">Ban IP</x-blatui.dropdown-menu-item>
                                </form>
                            @endif
                        </x-admin.row-actions>

                    </td>
                </tr>

                <tr id="payload-{{ $log->id }}" style="display:none;" class="bg-[#f8f9fa]">
                    <td colspan="5" class="p-[15px]">

                        <strong>full payload:</strong>

                        @php
                            $payload = $log->payload;
                            $decoded = json_decode($payload, true);

                            if (json_last_error() === JSON_ERROR_NONE) {
                                $payloadFormatted = json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                            } else {
                                $payloadFormatted = $payload;
                            }
                        @endphp

                        <div
                            class="bg-[#1e1e1e] rounded-md border border-[#333] mt-2.5 relative">
                            <x-blatui.button variant="outline" size="sm" type="button" class="absolute top-2 right-2" data-payload-copy="{{ $log->id }}">copy</x-blatui.button>

                            <pre id="payload-content-{{ $log->id }}"
                                class="m-0 p-[18px] text-[#d4d4d4] text-[13px] leading-[1.5] whitespace-pre-wrap break-words overflow-auto max-h-[400px]">{{ $payloadFormatted }}</pre>
                        </div>

                    </td>
                </tr>

            @empty
                <tr>
                    <td colspan="5" class="text-center">
                        no blocked activity
                    </td>
                </tr>
            @endforelse
        </x-slot:tbody>

    </x-admin.data-table>

@endsection
