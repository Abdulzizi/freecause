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

        <input class="fc-input" name="petition_id" placeholder="Petition ID" value="{{ $filters['petition_id'] ?? '' }}"
            style="max-width:120px;">

        <input class="fc-input" name="user_id" placeholder="User ID" value="{{ $filters['user_id'] ?? '' }}"
            style="max-width:100px;">

        <input class="fc-input" name="email" placeholder="Email" value="{{ $filters['email'] ?? '' }}"
            style="max-width:220px;">

        <input class="fc-input" name="ip" placeholder="IP" value="{{ $filters['ip'] ?? '' }}"
            style="max-width:130px;">

        @if ($hasText)
            <input class="fc-input" name="text" placeholder="Text" value="{{ $filters['text'] ?? '' }}"
                style="max-width:220px;">
        @endif

        {{-- BUG 3 FIX: locale options now come from $locales passed by controller --}}
        <select class="fc-select" name="locale" style="max-width:140px;">
            @foreach ($locales as $k => $label)
                <option value="{{ $k }}" {{ ($filters['locale'] ?? '') == $k ? 'selected' : '' }}>
                    {{ $label }}
                </option>
            @endforeach
        </select>

        <select class="fc-select" name="category_id" style="max-width:320px;">
            <option value="">(Category)</option>
            @foreach ($categories as $c)
                <option value="{{ $c->id }}" {{ ($filters['category_id'] ?? '') == $c->id ? 'selected' : '' }}>
                    {{ $c->name }}
                </option>
            @endforeach
        </select>

        @if ($hasConfirmed)
            <select class="fc-select" name="confirmed" style="max-width:160px;">
                <option value="">all signatures</option>
                <option value="1" {{ ($filters['confirmed'] ?? '') === '1' ? 'selected' : '' }}>confirmed</option>
                <option value="0" {{ ($filters['confirmed'] ?? '') === '0' ? 'selected' : '' }}>unconfirmed</option>
            </select>
        @endif

        <select class="fc-select" name="spam" style="max-width:140px;">
            <option value="">spam...</option>
            <option value="1" {{ ($filters['spam'] ?? '') === '1' ? 'selected' : '' }}>spam</option>
            <option value="0" {{ ($filters['spam'] ?? '') === '0' ? 'selected' : '' }}>not spam</option>
        </select>

        <input class="fc-input" type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}"
            style="max-width:150px;">
        <input class="fc-input" type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}"
            style="max-width:150px;">

    </x-admin.filter-box>

    <x-admin.list-table-box emptyText="no signatures found" :p="$signatures" :bulk="[
        'banRoute' => route('admin.signatures.bulkDelete'),
        'banLabel' => 'Delete',
        'banConfirm' => 'Delete selected signatures?',
    ]">

        <x-slot:thead>
            <tr style="border-bottom:1px solid #ccc;">
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
                <tr style="border-bottom:1px solid #eee;">

                    <td>{{ $s->id }}</td>

                    <td data-hide-mobile>
                        <input type="checkbox" class="bulk-checkbox" value="{{ $s->id }}">
                    </td>

                    <td data-hide-mobile style="text-align:center;">
                        @if ($s->user_verified)
                            <span class="fc-ok">✔</span>
                        @else
                            <span class="fc-no">✖</span>
                        @endif
                    </td>

                    @if ($hasConfirmed)
                        <td data-hide-mobile style="text-align:center;">
                            @if ($s->confirmed)
                                <span class="fc-ok" title="Confirmed">✔</span>
                            @else
                                <span class="fc-no" title="Unconfirmed">✖</span>
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

                    <td data-hide-mobile style="text-align:center;">
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
                        <td data-hide-mobile style="max-width:300px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                            {{ $s->text }}
                        </td>
                    @endif

                    <td>{{ $s->created_at }}</td>

                </tr>
            @endforeach
        </x-slot:tbody>

        <x-slot:footer>
            <div class="fc-toolbar-actions">
                @if ($hasConfirmed)
                    <div class="fc-action-group">
                        <button type="button" class="fc-icon-btn bulk-action" data-action="approve" title="Approve">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                                <path d="M5 13l4 4L19 7" stroke="#2f6f2f" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <span class="fc-btn-label">Approve</span>
                        </button>

                        <button type="button" class="fc-icon-btn bulk-action" data-action="reject" title="Reject">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                                <path d="M6 6l12 12" stroke="#8a6d1e" stroke-width="2" stroke-linecap="round"/>
                                <path d="M18 6L6 18" stroke="#8a6d1e" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                            <span class="fc-btn-label">Reject</span>
                        </button>
                    </div>
                @endif

                <button type="button" class="fc-icon-btn bulk-action" data-action="delete" title="Delete">
                    {!! $icoX !!}
                    <span class="fc-btn-label">Delete</span>
                </button>

                <a href="{{ route('admin.signatures', request()->except('select')) }}"
                    title="Clear selection" class="fc-icon-btn fc-clear-btn">
                    {!! $icoX !!}
                    <span class="fc-btn-label">Clear</span>
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
