@extends('admin.layouts.app')

@section('title', 'User Levels')

@section('content')

    <x-admin.page-header title="user levels" :count="$levels->total()" />

    @if (session('success'))
        <div class="border! border-[#77c777]! bg-[#e9ffe9]! text-[#135f13]! px-1.5! py-1! rounded! my-1.5! mb-[15px]!">
            {{ session('success') }}
        </div>
    @endif


    <x-admin.data-table emptyText="no user levels found" :p="$levels">

        <x-slot:thead>
            <tr data-admin-row-hover class="border-b! border-[#ccc]! h-11!">
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
                <tr class="border-b! border-[#eee]! h-11!" data-admin-row-hover>

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

                <x-ui.button type="button" variant="danger" size="sm" class="fc-abtn bulk-action" data-action="delete">

                    <svg class="max-md:w-4! max-md:h-4!" width="18" height="18" viewBox="0 0 24 24" fill="none">
                        <path d="M6 6l12 12" stroke="#d23b2a" stroke-width="2" stroke-linecap="round" />
                        <path d="M18 6L6 18" stroke="#d23b2a" stroke-width="2" stroke-linecap="round" />
                    </svg>

                    <div class="text-[11px]! text-[#666]! max-md:hidden!">
                        delete
                    </div>
                </x-ui.button>

            </div>
        </x-slot:footer>

    </x-admin.data-table>

    <x-admin.section-panel title="new level">

        <form method="post" action="{{ route('admin.system.user_levels.store') }}">
            @csrf

            <x-admin.form-field label="Name (system key)">
                <x-ui.input name="name" required class="w-full! box-border! border! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white!" />
            </x-admin.form-field>

            <x-admin.form-field label="Visible Name">
                <x-ui.input name="visible_name" required class="w-full! box-border! border! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white!" />
            </x-admin.form-field>

            <div class="flex! justify-end! mt-[15px]!">
                <x-ui.button variant="secondary" size="sm">add</x-ui.button>
            </div>
        </form>

    </x-admin.section-panel>

@endsection


@include('admin.partials.bulk-js', [
    'actionRoute' => route('admin.system.user_levels.delete'),
    'noun' => 'user levels',
    'emptyMsg' => 'No user levels selected',
])
