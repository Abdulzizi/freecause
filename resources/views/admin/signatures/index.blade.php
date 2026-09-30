@extends('admin.layouts.app')

@section('title', 'Signatures')

@section('content')

    @php
        $icoX = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                    <path d="M6 6l12 12" stroke="#d23b2a" stroke-width="2"/>
                    <path d="M18 6L6 18" stroke="#d23b2a" stroke-width="2"/>
                </svg>';

        $icoLink = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                        <path d="M10 13a5 5 0 0 1 0-7l2-2a5 5 0 0 1 7 7l-1 1" stroke="#444" stroke-width="2"/>
                        <path d="M14 11a5 5 0 0 1 0 7l-2 2a5 5 0 0 1-7-7l1-1" stroke="#444" stroke-width="2"/>
                    </svg>';
    @endphp

    <h1>signatures ({{ number_format($approxTotal) }})</h1>

    @php
        $activeFilterCount = collect($filters)->filter(fn($v) => $v !== '' && $v !== null)->count();
    @endphp

    <x-admin.filter-box title="filter signatures" :action="route('admin.signatures')" :reset="route('admin.signatures')" :activeCount="$activeFilterCount">

        <input class="w-full! box-border! border! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white! max-w-[120px]!" name="petition_id" placeholder="Petition ID" value="{{ $filters['petition_id'] ?? '' }}">

        <input class="w-full! box-border! border! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white! max-w-[100px]!" name="user_id" placeholder="User ID" value="{{ $filters['user_id'] ?? '' }}">

        <input class="w-full! box-border! border! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white! max-w-[220px]!" name="email" placeholder="Email" value="{{ $filters['email'] ?? '' }}">

        <input class="w-full! box-border! border! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white! max-w-[130px]!" name="ip" placeholder="IP" value="{{ $filters['ip'] ?? '' }}">

        @if ($hasText)
            <input class="w-full! box-border! border! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white! max-w-[220px]!" name="text" placeholder="Text" value="{{ $filters['text'] ?? '' }}">
        @endif

        {{-- BUG 3 FIX: locale options now come from $locales passed by controller --}}
        <select class="w-full! box-border! border! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white! max-w-[140px]!" name="locale">
            @foreach ($locales as $k => $label)
                <option value="{{ $k }}" {{ ($filters['locale'] ?? '') == $k ? 'selected' : '' }}>
                    {{ $label }}
                </option>
            @endforeach
        </select>

        <select class="w-full! box-border! border! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white! max-w-[320px]!" name="category_id">
            <option value="">(Category)</option>
            @foreach ($categories as $c)
                <option value="{{ $c->id }}" {{ ($filters['category_id'] ?? '') == $c->id ? 'selected' : '' }}>
                    {{ $c->name }}
                </option>
            @endforeach
        </select>

        @if ($hasConfirmed)
            <select class="w-full! box-border! border! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white! max-w-[160px]!" name="confirmed">
                <option value="">all signatures</option>
                <option value="1" {{ ($filters['confirmed'] ?? '') === '1' ? 'selected' : '' }}>confirmed</option>
                <option value="0" {{ ($filters['confirmed'] ?? '') === '0' ? 'selected' : '' }}>unconfirmed</option>
            </select>
        @endif

        <select class="w-full! box-border! border! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white! max-w-[140px]!" name="spam">
            <option value="">spam...</option>
            <option value="1" {{ ($filters['spam'] ?? '') === '1' ? 'selected' : '' }}>spam</option>
            <option value="0" {{ ($filters['spam'] ?? '') === '0' ? 'selected' : '' }}>not spam</option>
        </select>

        <input class="w-full! box-border! border! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white! max-w-[150px]!" type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}">
        <input class="w-full! box-border! border! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white! max-w-[150px]!" type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}">

    </x-admin.filter-box>

    <x-admin.list-table-box emptyText="no signatures found" :p="$signatures" :bulk="[
        'banRoute' => route('admin.signatures.bulkDelete'),
        'banLabel' => 'Delete',
        'banConfirm' => 'Delete selected signatures?',
    ]">

        <x-slot:thead>
            <tr class="border-b! border-[#ccc]!">
                <th width="70">ID</th>
                <th width="26" data-hide-mobile></th>
                <th width="26" data-hide-mobile></th>
                @if ($hasConfirmed)
                    <th width="26" title="Confirmed" data-hide-mobile>C</th>
                @endif
                <th>User</th>
                <th width="26" data-hide-mobile></th>
                <th>Title</th>
                @if ($hasText)
                    <th data-hide-mobile>Text</th>
                @endif
                <th width="160">Date</th>
            </tr>
        </x-slot:thead>

        <x-slot:tbody>
            @foreach ($signatures as $s)
                <tr class="border-b! border-[#eee]!">

                    <td>{{ $s->id }}</td>

                    <td data-hide-mobile>
                        <input type="checkbox" class="bulk-checkbox" value="{{ $s->id }}">
                    </td>

                    <td data-hide-mobile class="text-center!">
                        @if ($s->user_verified)
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#5c8f3a" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        @else
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#c00" stroke-width="2.5" stroke-linecap="round"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
                        @endif
                    </td>

                    @if ($hasConfirmed)
                        <td data-hide-mobile class="text-center!">
                            @if ($s->confirmed)
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#5c8f3a" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" title="Confirmed"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            @else
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#c00" stroke-width="2.5" stroke-linecap="round" title="Unconfirmed"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
                            @endif
                        </td>
                    @endif

                    <td>
                        @if ($s->user_id)
                            <a href="{{ route('admin.users', ['select' => $s->user_id]) }}">
                                {{ $s->user_name }}
                            </a>
                        @elseif($s->email)
                            <a href="mailto:{{ $s->email }}">
                                {{ $s->email }}
                            </a>
                        @else
                            {{ $s->name }}
                        @endif
                    </td>

                    <td data-hide-mobile class="text-center!">
                        @if ($s->petition_slug)
                            <a
                                href="{{ route('petition.show', [
                                    'locale' => $s->locale,
                                    'slug' => $s->petition_slug,
                                    'id' => $s->petition_id,
                                ]) }}">
                                {!! $icoLink !!}
                            </a>
                        @endif
                    </td>

                    <td>
                        <a href="{{ route('admin.petitions', ['id' => $s->petition_id]) }}">
                            {{ $s->petition_title ?: 'petition #' . $s->petition_id }}
                        </a>
                    </td>

                    @if ($hasText)
                        <td data-hide-mobile class="max-w-[300px]! overflow-hidden! text-ellipsis! whitespace-nowrap!">
                            {{ $s->text }}
                        </td>
                    @endif

                    <td>{{ $s->created_at }}</td>

                </tr>
            @endforeach
        </x-slot:tbody>

        <x-slot:footer>
            <div class="flex! gap-2! items-center! flex-wrap!">
                @if ($hasConfirmed)
                    <div class="flex! gap-0.5! p-0.5! bg-[#f8f8f8]! border! border-[#eee]! rounded!">
                        <button type="button" class="flex! flex-col! items-center! justify-center! gap-0.5! w-10! h-[38px]! md:w-[52px]! md:h-[44px]! border! border-[#bbb]! bg-white! cursor-pointer! transition-[background,opacity]! duration-150! hover:bg-[#f7f7f7]! active:bg-[#dcdcdc]! disabled:opacity-[0.35]! disabled:pointer-events-none! bulk-action" data-action="approve" title="Approve">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                                <path d="M5 13l4 4L19 7" stroke="#2f6f2f" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <span class="text-[10px]! text-[#666]! leading-none! hidden! md:inline!">Approve</span>
                        </button>

                        <button type="button" class="flex! flex-col! items-center! justify-center! gap-0.5! w-10! h-[38px]! md:w-[52px]! md:h-[44px]! border! border-[#bbb]! bg-white! cursor-pointer! transition-[background,opacity]! duration-150! hover:bg-[#f7f7f7]! active:bg-[#dcdcdc]! disabled:opacity-[0.35]! disabled:pointer-events-none! bulk-action" data-action="reject" title="Reject">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                                <path d="M6 6l12 12" stroke="#8a6d1e" stroke-width="2" stroke-linecap="round"/>
                                <path d="M18 6L6 18" stroke="#8a6d1e" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                            <span class="text-[10px]! text-[#666]! leading-none! hidden! md:inline!">Reject</span>
                        </button>
                    </div>
                @endif

                <button type="button" class="flex! flex-col! items-center! justify-center! gap-0.5! w-10! h-[38px]! md:w-[52px]! md:h-[44px]! border! border-[#bbb]! bg-white! cursor-pointer! transition-[background,opacity]! duration-150! hover:bg-[#f7f7f7]! active:bg-[#dcdcdc]! disabled:opacity-[0.35]! disabled:pointer-events-none! bulk-action" data-action="delete" title="Delete">
                    {!! $icoX !!}
                    <span class="text-[10px]! text-[#666]! leading-none! hidden! md:inline!">Delete</span>
                </button>

                <a href="{{ route('admin.signatures', request()->except('select')) }}"
                    title="Clear selection" class="flex! flex-col! items-center! justify-center! gap-0.5! w-10! h-[38px]! md:w-[52px]! md:h-[44px]! border! border-[#bbb]! bg-white! cursor-pointer! transition-[background,opacity]! duration-150! hover:bg-[#f7f7f7]! active:bg-[#dcdcdc]! text-[#666]! no-underline!">
                    {!! $icoX !!}
                    <span class="text-[10px]! text-[#666]! leading-none! hidden! md:inline!">Clear</span>
                </a>
            </div>
        </x-slot:footer>

    </x-admin.list-table-box>

    @include('admin.partials.bulk-js', [
        'actionRoute' => route('admin.signatures.bulkAction'),
        'noun' => 'signatures',
        'emptyMsg' => 'No signatures selected',
    ])

@endsection
