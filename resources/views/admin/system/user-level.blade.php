@extends('admin.layouts.app')

@section('title', 'User Levels')

@section('content')

    <h1 class="m-0! mb-2! text-lg!">user levels ({{ number_format($levels->total()) }})</h1>

    @if (session('success'))
        <div class="border! border-[#77c777]! bg-[#e9ffe9]! text-[#135f13]! px-1.5! py-1! rounded! my-1.5! mb-[15px]!">
            {{ session('success') }}
        </div>
    @endif


    <x-admin.list-table-box emptyText="no user levels found" :p="$levels">

        <x-slot:thead>
            <tr class="border-b! border-[#ccc]! h-11!">
                <th class="w-[70px]! text-left!">ID</th>
                <th class="w-10! text-left!"></th>
                <th class="text-left!">Name (system key)</th>
                <th class="text-right!">Visible Name</th>
                <th class="w-[90px]! text-right!">System</th>
                <th class="w-[90px]! text-right!">Users</th>
            </tr>
        </x-slot:thead>

        <x-slot:tbody>
            @foreach ($levels as $level)
                <tr class="border-b! border-[#eee]! h-11!">

                    <td class="text-left! align-middle!">
                        {{ $level->id }}
                    </td>

                    <td class="text-left! align-middle!">
                        @if (!$level->is_system && $level->users_count == 0)
                            <input type="checkbox" class="bulk-checkbox" value="{{ $level->id }}">
                        @endif
                    </td>

                    <td class="text-left! align-middle!">
                        <strong>{{ $level->name }}</strong>
                    </td>

                    <td class="text-right! align-middle!">
                        {{ $level->visible_name }}
                    </td>

                    <td class="text-right! align-middle!">
                        {!! $level->is_system
                            ? '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#5c8f3a" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>'
                            : '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#c00" stroke-width="2.5" stroke-linecap="round"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>' !!}
                    </td>

                    <td class="text-right! align-middle!">
                        {{ $level->users_count }}
                    </td>

                </tr>
            @endforeach
        </x-slot:tbody>

        <x-slot:footer>
            <div class="flex! gap-3! items-center!">

                <button type="button" class="flex! flex-col! items-center! justify-center! w-[52px]! h-11! border! border-[#bbb]! bg-white! gap-0.5! cursor-pointer! transition-colors! transition-opacity! hover:bg-[#f7f7f7]! active:bg-[#dcdcdc]! disabled:opacity-35! disabled:pointer-events-none! max-md:w-10! max-md:h-[38px]! max-md:gap-px! bulk-action" data-action="delete">

                    <svg class="max-md:w-4! max-md:h-4!" width="18" height="18" viewBox="0 0 24 24" fill="none">
                        <path d="M6 6l12 12" stroke="#d23b2a" stroke-width="2" stroke-linecap="round" />
                        <path d="M18 6L6 18" stroke="#d23b2a" stroke-width="2" stroke-linecap="round" />
                    </svg>

                    <div class="text-[11px]! text-[#666]! max-md:hidden!">
                        delete
                    </div>
                </button>

            </div>
        </x-slot:footer>

    </x-admin.list-table-box>

    <div class="inline-block! bg-[#e6e6e6]! border! border-[#9e9e9e]! border-b-0! px-2.5! py-[3px]! rounded-tl! rounded-tr! font-bold! text-sm! mt-[25px]! mr-0! mb-0! ml-0!">new level</div>
    <div class="border! border-[#9e9e9e]! rounded! p-2.5! mb-2.5! p-5!">

        <form method="post" action="{{ route('admin.system.user_levels.store') }}">
            @csrf

            <div class="grid! grid-cols-1! gap-1.5! py-1.5! md:grid-cols-[160px_1fr]! md:gap-3! md:items-center!">
                <label class="font-bold! text-sm!">Name (system key)</label>
                <input type="text" name="name" class="w-full! box-border! border! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white!" required>
            </div>

            <div class="grid! grid-cols-1! gap-1.5! py-1.5! md:grid-cols-[160px_1fr]! md:gap-3! md:items-center!">
                <label class="font-bold! text-sm!">Visible Name</label>
                <input type="text" name="visible_name" class="w-full! box-border! border! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white!" required>
            </div>

            <div class="flex! justify-end! mt-[15px]!">
                <button class="border! border-[#7d7d7d]! bg-[#e6e6e6]! px-3! py-1.5! rounded-[3px]! cursor-pointer! hover:bg-[#dcdcdc]! text-xs! md:py-1! md:px-2.5! md:text-sm!">
                    add
                </button>
            </div>
        </form>

    </div>

@endsection


@include('admin.partials.bulk-js', [
    'actionRoute' => route('admin.system.user_levels.delete'),
    'noun' => 'user levels',
    'emptyMsg' => 'No user levels selected',
])
