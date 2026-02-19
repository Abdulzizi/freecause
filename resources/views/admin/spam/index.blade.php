@extends('admin.layouts.app')

@section('title', 'Spam')

@section('content')

    <h1>spam</h1>

    <div class="fc-box" style="margin-bottom:15px;">
        <strong>{{ number_format($bannedCount) }}</strong> IP banned.
    </div>

    <form method="post" action="{{ route('admin.spam.clear') }}" onsubmit="return confirmClear()" style="margin-bottom:15px;">
        @csrf
        <button class="fc-btn" style="background:#dc3545;">
            clear spam logs
        </button>
    </form>

    <x-admin.list-table-box empty-text="no blocked activity" :p="$logs">

        <x-slot:thead>
            <tr style="border-bottom:1px solid #ccc;">
                <th style="width:100px;">type</th>
                <th style="width:150px;">ip</th>
                <th>payload</th>
                <th style="width:160px;">date</th>
                <th style="width:160px;">action</th>
            </tr>
        </x-slot:thead>

        <x-slot:tbody>
            @forelse($logs as $log)
                <tr
                    style="border-bottom:1px solid #eee;
                    @if (in_array($log->ip, $bannedIps)) background:#fff3f3; @endif">

                    <td>{{ $log->type }}</td>

                    <td>
                        <strong>{{ $log->ip }}</strong>
                    </td>

                    <td
                        style="
                            max-width:320px;
                            white-space:nowrap;
                            overflow:hidden;
                            text-overflow:ellipsis;">
                        {{ \Illuminate\Support\Str::limit($log->payload, 80) }}
                    </td>

                    <td>{{ $log->created_at }}</td>

                    <td style="display:flex; gap:6px; flex-wrap:wrap;">

                        <button type="button" onclick="togglePayload({{ $log->id }})" class="fc-btn"
                            style="background:#6c757d;">
                            view
                        </button>

                        @if (in_array($log->ip, $bannedIps))
                            <form method="post" action="{{ route('admin.spam.unban') }}"
                                onsubmit="return confirmUnban('{{ $log->ip }}')">
                                @csrf
                                <input type="hidden" name="ip" value="{{ $log->ip }}">
                                <button class="fc-btn" style="background:#dc3545;">
                                    unban
                                </button>
                            </form>
                        @else
                            <form method="post" action="{{ route('admin.spam.ban') }}"
                                onsubmit="return confirmBan('{{ $log->ip }}')">
                                @csrf
                                <input type="hidden" name="ip" value="{{ $log->ip }}">
                                <button class="fc-btn">
                                    ban
                                </button>
                            </form>
                        @endif

                    </td>
                </tr>

                <tr id="payload-{{ $log->id }}" style="display:none; background:#f8f9fa;">
                    <td colspan="5" style="padding:15px;">

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
                            style="background:#1e1e1e;border-radius:6px;border:1px solid #333;margin-top:10px;position:relative;">
                            <button type="button" onclick="copyPayload({{ $log->id }})"
                                style="position:absolute;top:8px;right:8px;background:#444;color:#fff;border:none;padding:4px 8px;font-size:12px;cursor:pointer;border-radius:4px;">
                                copy
                            </button>

                            <pre id="payload-content-{{ $log->id }}"
                                style="margin:0;padding:18px;color:#d4d4d4;font-size:13px;line-height:1.5;white-space:pre-wrap;word-break:break-word;overflow:auto;max-height:400px;">{{ $payloadFormatted }}</pre>
                        </div>


                    </td>
                </tr>

            @empty
                <tr>
                    <td colspan="5" style="text-align:center;">
                        no blocked activity
                    </td>
                </tr>
            @endforelse
        </x-slot:tbody>

    </x-admin.list-table-box>

    <script>
        function togglePayload(id) {
            var row = document.getElementById('payload-' + id);
            row.style.display = (row.style.display === 'none') ? '' : 'none';
        }

        function confirmBan(ip) {
            return confirm(
                "are you sure you want to ban this ip?\n\n" +
                ip +
                "\n\nthis will block access immediately."
            );
        }

        function confirmUnban(ip) {
            return confirm("unban this ip?\n\n" + ip);
        }

        function confirmClear() {
            return confirm(
                "you are about to permanently delete all spam logs.\n\n" +
                "continue?"
            );
        }

        function copyPayload(id) {
            var text = document.getElementById('payload-content-' + id).innerText;
            navigator.clipboard.writeText(text);
            alert('payload copied');
        }
    </script>

@endsection
