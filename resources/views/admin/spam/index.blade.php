@extends('admin.layouts.app')

@section('title', 'Spam')

@section('content')

    <h1>spam</h1>

    <div class="border! border-[#9e9e9e]! rounded! p-[10px]! mb-[15px]!">
        <strong>{{ number_format($bannedCount) }}</strong> IP banned.
    </div>

    <x-admin.filter-box title="filter spam" :action="route('admin.spam')" :reset="route('admin.spam')">
        <input class="w-full! box-border! border! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white! max-w-[150px]!" name="ip" placeholder="IP" value="{{ $filters['ip'] ?? '' }}">

        <select class="w-full! box-border! border! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white! max-w-[160px]!" name="type">
            <option value="">type...</option>
            @foreach ($types as $t)
                <option value="{{ $t }}" {{ ($filters['type'] ?? '') === $t ? 'selected' : '' }}>{{ $t }}</option>
            @endforeach
        </select>

        <input class="w-full! box-border! border! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white! max-w-[150px]!" type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}">
        <input class="w-full! box-border! border! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white! max-w-[150px]!" type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}">
    </x-admin.filter-box>

    <form method="post" action="{{ route('admin.spam.clear') }}" onsubmit="return confirmClear()" class="mb-[15px]!">
        @csrf
        <button class="border! border-[#7d7d7d]! bg-[#dc3545]! text-white! px-[10px]! py-1! rounded-[3px]! cursor-pointer!">
            clear spam logs
        </button>
    </form>

    <x-admin.list-table-box empty-text="no blocked activity" :p="$logs">

        <x-slot:thead>
            <tr class="border-b! border-[#ccc]!">
                <th class="w-[100px]!">type</th>
                <th class="w-[150px]!">ip</th>
                <th>payload</th>
                <th class="w-[160px]!">date</th>
                <th class="w-[160px]!">action</th>
            </tr>
        </x-slot:thead>

        <x-slot:tbody>
            @forelse($logs as $log)
                <tr
                    class="border-b! border-[#eee]! @if (in_array($log->ip, $bannedIps)) bg-[#fff3f3]! @endif">

                    <td>{{ $log->type }}</td>

                    <td>
                        <strong>{{ $log->ip }}</strong>
                    </td>

                    <td
                        class="max-w-[320px]! whitespace-nowrap! overflow-hidden! text-ellipsis!">
                        {{ \Illuminate\Support\Str::limit($log->payload, 80) }}
                    </td>

                    <td>{{ $log->created_at }}</td>

                    <td class="flex! gap-1.5! flex-wrap!">

                        <button type="button" onclick="togglePayload({{ $log->id }})" class="border! border-[#7d7d7d]! bg-[#6c757d]! text-white! px-[10px]! py-1! rounded-[3px]! cursor-pointer!">
                            view
                        </button>

                        @if (in_array($log->ip, $bannedIps))
                            <form method="post" action="{{ route('admin.spam.unban') }}"
                                onsubmit="return confirmUnban('{{ $log->ip }}')">
                                @csrf
                                <input type="hidden" name="ip" value="{{ $log->ip }}">
                                <button class="border! border-[#7d7d7d]! bg-[#dc3545]! text-white! px-[10px]! py-1! rounded-[3px]! cursor-pointer!">
                                    unban
                                </button>
                            </form>
                        @else
                            <form method="post" action="{{ route('admin.spam.ban') }}"
                                onsubmit="return confirmBan('{{ $log->ip }}')">
                                @csrf
                                <input type="hidden" name="ip" value="{{ $log->ip }}">
                                <button class="border! border-[#7d7d7d]! bg-[#e6e6e6]! px-[10px]! py-1! rounded-[3px]! cursor-pointer! hover:bg-[#dcdcdc]!">
                                    ban
                                </button>
                            </form>
                        @endif

                    </td>
                </tr>

                <tr id="payload-{{ $log->id }}" style="display:none;" class="bg-[#f8f9fa]!">
                    <td colspan="5" class="p-[15px]!">

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
                            class="bg-[#1e1e1e]! rounded-md! border! border-[#333]! mt-2.5! relative!">
                            <button type="button" onclick="copyPayload({{ $log->id }})"
                                class="absolute! top-2! right-2! bg-[#444]! text-white! border-none! py-1! px-2! text-xs! cursor-pointer! rounded!">
                                copy
                            </button>

                            <pre id="payload-content-{{ $log->id }}"
                                class="m-0! p-[18px]! text-[#d4d4d4]! text-[13px]! leading-[1.5]! whitespace-pre-wrap! break-words! overflow-auto! max-h-[400px]!">{{ $payloadFormatted }}</pre>
                        </div>


                    </td>
                </tr>

            @empty
                <tr>
                    <td colspan="5" class="text-center!">
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
