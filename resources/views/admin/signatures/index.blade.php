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

    <x-admin.page-header title="signatures" :count="$approxTotal" />

    @php
        $activeFilterCount = collect($filters)->filter(fn($v) => $v !== '' && $v !== null)->count();
    @endphp

    <x-admin.filter-box title="filter signatures" :action="route('admin.signatures')" :reset="route('admin.signatures')" :activeCount="$activeFilterCount">

        <input class="fc-filter-field" name="petition_id" placeholder="Petition ID" value="{{ $filters['petition_id'] ?? '' }}">

        <input class="fc-filter-field" name="user_id" placeholder="User ID" value="{{ $filters['user_id'] ?? '' }}">

        <input class="fc-filter-field" name="email" placeholder="Email" value="{{ $filters['email'] ?? '' }}">

        <input class="fc-filter-field" name="ip" placeholder="IP" value="{{ $filters['ip'] ?? '' }}">

        @if ($hasText)
            <input class="fc-filter-field" name="text" placeholder="Text" value="{{ $filters['text'] ?? '' }}">
        @endif

        {{-- BUG 3 FIX: locale options now come from $locales passed by controller --}}
        <select data-admin-autosubmit class="fc-filter-field" name="locale">
            @foreach ($locales as $k => $label)
                <option value="{{ $k }}" {{ ($filters['locale'] ?? '') == $k ? 'selected' : '' }}>
                    {{ $label }}
                </option>
            @endforeach
        </select>

        <select data-admin-autosubmit class="fc-filter-field" name="category_id">
            <option value="">(Category)</option>
            @foreach ($categories as $c)
                <option value="{{ $c->id }}" {{ ($filters['category_id'] ?? '') == $c->id ? 'selected' : '' }}>
                    {{ $c->name }}
                </option>
            @endforeach
        </select>

        @if ($hasConfirmed)
            <select data-admin-autosubmit class="fc-filter-field" name="confirmed">
                <option value="">all signatures</option>
                <option value="1" {{ ($filters['confirmed'] ?? '') === '1' ? 'selected' : '' }}>confirmed</option>
                <option value="0" {{ ($filters['confirmed'] ?? '') === '0' ? 'selected' : '' }}>unconfirmed</option>
            </select>
        @endif

        <select data-admin-autosubmit class="fc-filter-field" name="spam">
            <option value="">spam...</option>
            <option value="1" {{ ($filters['spam'] ?? '') === '1' ? 'selected' : '' }}>spam</option>
            <option value="0" {{ ($filters['spam'] ?? '') === '0' ? 'selected' : '' }}>not spam</option>
        </select>

        <input class="fc-filter-field" type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}">
        <input class="fc-filter-field" type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}">

    </x-admin.filter-box>

    <x-admin.data-table emptyText="no signatures found" :p="$signatures" :emptyClearRoute="route('admin.signatures')" :bulk="[]">

        <x-slot:thead>
            <tr class="border-b! border-[#ccc]!">
                <th width="70">ID</th>
                <th width="26" data-hide-mobile title="Bulk selection" aria-label="Bulk selection"></th>
                <th width="26" data-hide-mobile title="User email verified" aria-label="User email verified">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#666" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                </th>
                @if ($hasConfirmed)
                    <th width="26" title="Signature confirmed" data-hide-mobile>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#666" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                    </th>
                @endif
                <th>User</th>
                <th width="26" data-hide-mobile title="Open petition on site">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#666" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 13a5 5 0 0 1 0-7l2-2a5 5 0 0 1 7 7l-1 1"/><path d="M14 11a5 5 0 0 1 0 7l-2 2a5 5 0 0 1-7-7l1-1"/></svg>
                </th>
                <th>Title</th>
                @if ($hasText)
                    <th data-hide-mobile>Text</th>
                @endif
                <th width="160">Date</th>
                @if ($hasConfirmed && admin_can('signatures', 'edit'))
                    <th data-hide-mobile>Actions</th>
                @endif
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
                        <td data-hide-mobile class="text-center!" data-signature-status>
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

                    @if ($hasConfirmed && admin_can('signatures', 'edit'))
                        <td data-hide-mobile>
                            <div class="flex! items-center! gap-1!" data-signature-actions data-confirmed="{{ $s->confirmed ? '1' : '0' }}">
                                <button type="button" data-signature-action="approve"
                                    data-url="{{ route('admin.signatures.action', ['signature' => $s->id]) }}"
                                    @disabled($s->confirmed)
                                    title="{{ $s->confirmed ? 'Already confirmed' : 'Approve signature' }}" class="fc-abtn">
                                    Approve
                                </button>
                                <button type="button" data-signature-action="reject"
                                    data-url="{{ route('admin.signatures.action', ['signature' => $s->id]) }}"
                                    @disabled(! $s->confirmed)
                                    title="{{ ! $s->confirmed ? 'Already rejected' : 'Reject signature' }}" class="fc-abtn">
                                    Reject
                                </button>
                            </div>
                        </td>
                    @endif

                </tr>
            @endforeach
        </x-slot:tbody>

        <x-slot:footer>
            <div class="flex! gap-2! items-center! flex-wrap!">
                @if ($hasConfirmed)
                    <div class="flex! gap-0.5! p-0.5! bg-[#f8f8f8]! border! border-[#eee]! rounded!">
                        <button type="button" class="fc-abtn bulk-action" data-action="approve" title="Approve">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                                <path d="M5 13l4 4L19 7" stroke="#2f6f2f" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <span class="hidden! md:inline!">Approve</span>
                        </button>

                        <button type="button" class="fc-abtn bulk-action" data-action="reject" title="Reject">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                                <path d="M6 6l12 12" stroke="#8a6d1e" stroke-width="2" stroke-linecap="round"/>
                                <path d="M18 6L6 18" stroke="#8a6d1e" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                            <span class="hidden! md:inline!">Reject</span>
                        </button>
                    </div>
                @endif

                @if (admin_can('signatures', 'delete'))
                    <button type="button" class="fc-abtn bulk-action" data-action="delete" title="Delete">
                        {!! $icoX !!}
                        <span class="hidden! md:inline!">Delete</span>
                    </button>
                @endif

                <a href="{{ route('admin.signatures', request()->except('select')) }}"
                    title="Clear selection" class="fc-abtn fc-abtn--neutral">
                    {!! $icoX !!}
                    <span class="hidden! md:inline!">Clear</span>
                </a>
            </div>
        </x-slot:footer>

    </x-admin.data-table>

    @include('admin.partials.bulk-js', [
        'actionRoute' => route('admin.signatures.bulkAction'),
        'noun' => 'signatures',
        'emptyMsg' => 'No signatures selected',
    ])

@endsection
