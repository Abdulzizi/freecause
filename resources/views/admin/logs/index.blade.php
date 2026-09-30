@extends('admin.layouts.app')

@section('title', 'Logs')

@section('content')

    <h1 class="m-0! mb-2! text-lg!">logs ({{ number_format($logs->total()) }})</h1>

    <x-admin.filter-box title="filter logs" :action="route('admin.logs')" :reset="route('admin.logs')">

        <input class="w-full! box-border! border! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white! max-w-[260px]!" name="q" placeholder="type something..." value="{{ $filters['q'] ?? '' }}">

        <select name="level" class="w-full! box-border! border! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white! max-w-[150px]!">
            <option value="">All levels</option>
            <option value="info" {{ request('level')=='info'?'selected':'' }}>Info</option>
            <option value="warning" {{ request('level')=='warning'?'selected':'' }}>Warning</option>
            <option value="error" {{ request('level')=='error'?'selected':'' }}>Error</option>
        </select>

        <select name="context" class="w-full! box-border! border! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white! max-w-[160px]!">
            <option value="">All context</option>
            <option value="admin.audit" {{ request('context')=='admin.audit'?'selected':'' }}>Admin Audit</option>
            <option value="auth.register" {{ request('context')=='auth.register'?'selected':'' }}>Auth Register</option>
            <option value="auth.login" {{ request('context')=='auth.login'?'selected':'' }}>Auth Login</option>
            <option value="auth.google" {{ request('context')=='auth.google'?'selected':'' }}>Google OAuth</option>
            <option value="petition.sign" {{ request('context')=='petition.sign'?'selected':'' }}>Petition Signed</option>
        </select>

        <input class="w-full! box-border! border! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white! max-w-[130px]!" name="ip" placeholder="IP" value="{{ $filters['ip'] ?? '' }}">
        <input class="w-full! box-border! border! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white! max-w-[150px]!" type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}">
        <input class="w-full! box-border! border! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white! max-w-[150px]!" type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}">
    </x-admin.filter-box>

    <div class="flex! gap-2! mb-3! items-center! flex-wrap!">
        <a class="border! border-[#7d7d7d]! bg-[#e6e6e6]! px-3! py-1.5! rounded-[3px]! cursor-pointer! hover:bg-[#dcdcdc]! text-xs! md:py-1! md:px-2.5! md:text-sm! no-underline!" href="{{ route('admin.logs.export', request()->query()) }}">
            export CSV
        </a>

        <form method="post" action="{{ route('admin.logs.prune') }}" class="flex! gap-1.5! items-center!"
            onsubmit="return confirm('Delete all logs older than the selected number of days?')">
            @csrf
            <select name="days" class="w-full! box-border! border! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white! max-w-[140px]!">
                <option value="30">30 days</option>
                <option value="60">60 days</option>
                <option value="90" selected>90 days</option>
                <option value="180">180 days</option>
            </select>
            <button class="border! border-[#7d7d7d]! bg-[#e6e6e6]! px-3! py-1.5! rounded-[3px]! cursor-pointer! hover:bg-[#dcdcdc]! text-xs! md:py-1! md:px-2.5! md:text-sm! bg-[#c00]! text-white!" type="submit">prune old logs</button>
        </form>
    </div>

    <x-admin.list-table-box :p="$logs" :bulk="[
            // 'banRoute' => route('admin.logs.bulkDelete'),
            // 'banLabel' => 'Delete',
            // 'banConfirm' => 'Delete selected logs?',
]">

        <x-slot:thead>
            <tr class="border-b! border-[#ccc]!">
                <th class="w-[70px]!">ID</th>
                <th class="w-[80px]!">Level</th>
                <th>Content preview</th>
                <th class="w-[160px]!">Date</th>
            </tr>
        </x-slot:thead>

        <x-slot:tbody>
            @foreach ($logs as $log)
                <tr class="border-b! border-[#eee]! hover:bg-[#f7f7f7]!">

                    <td>
                        <a href="{{ route('admin.logs', array_merge(request()->query(), ['select' => $log->id])) }}" class="no-underline! text-black!">
                            {{ $log->id }}
                        </a>
                    </td>

                    <td class="text-left!">
                        @if($log->level === 'error')
                            <span class="font-bold! text-[#b00020]!">ERROR</span>
                        @elseif($log->level === 'warning')
                            <span class="font-bold! text-[#c67c00]!">WARNING</span>
                        @else
                            <span class="font-bold! text-[#2f6f2f]!">INFO</span>
                        @endif
                    </td>

                    <td>
                        {{-- {{ Str::limit($log->title, 80) }} --}}
                        {{ Str::limit($log->title . ' - ' . $log->content, 80) }}
                    </td>

                    <td class="whitespace-nowrap!">
                        {{ $log->created_at }}
                    </td>

                </tr>
            @endforeach
        </x-slot:tbody>

    </x-admin.list-table-box>

    @if($selected)
            <div class="inline-block! bg-[#e6e6e6]! border! border-[#9e9e9e]! border-b-0! px-2.5! py-[3px]! rounded-tl! rounded-tr! font-bold! text-sm! mt-2.5! mr-0! mb-0! ml-0!">log</div>
            <div class="border! border-[#9e9e9e]! rounded! p-2.5! mb-2.5!">

                <div class="grid! grid-cols-1! gap-1.5! py-1.5! md:grid-cols-[160px_1fr]! md:gap-3! md:items-center!">
                    <label class="font-bold! text-sm!">date</label>
                    <div>{{ $selected->created_at }}</div>
                </div>

                <div class="grid! grid-cols-1! gap-1.5! py-1.5! md:grid-cols-[160px_1fr]! md:gap-3! md:items-center!">
                    <label class="font-bold! text-sm!">content</label>
                    <textarea class="w-full! box-border! border! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white! min-h-[140px]! resize-y! font-[Consolas,_Courier_New,_monospace]! text-[13px]!" readonly>
                        {{ $selected->content }}
                    </textarea>
                </div>

            </div>
    @endif

@endsection
