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

    <x-admin.filter-box title="filter signatures" :action="route('admin.signatures')" :reset="route('admin.signatures')">

        <input class="fc-input" name="petition_id" placeholder="Petition ID" value="{{ $filters['petition_id'] ?? '' }}"
            style="max-width:120px;">

        <input class="fc-input" name="email" placeholder="Email" value="{{ $filters['email'] ?? '' }}"
            style="max-width:220px;">

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

    </x-admin.filter-box>

    <x-admin.list-table-box emptyText="no signatures found" :p="$signatures" :bulk="[
        'banRoute' => route('admin.signatures.bulkDelete'),
        'banLabel' => 'Delete',
        'banConfirm' => 'Delete selected signatures?',
    ]">

        <x-slot:thead>
            <tr style="border-bottom:1px solid #ccc;">
                <th width="70">ID</th>
                <th width="26"></th>
                <th width="26"></th>
                <th>User</th>
                <th width="26"></th>
                <th>Title</th>
                @if ($hasText)
                    <th>Text</th>
                @endif
                <th width="160">Date</th>
            </tr>
        </x-slot:thead>

        <x-slot:tbody>
            @foreach ($signatures as $s)
                <tr style="border-bottom:1px solid #eee;">

                    <td>{{ $s->id }}</td>

                    <td>
                        <input type="checkbox" class="bulk-checkbox" value="{{ $s->id }}">
                    </td>

                    <td style="text-align:center;">
                        @if ($s->user_verified)
                            <span class="fc-ok">✔</span>
                        @else
                            <span class="fc-no">✖</span>
                        @endif
                    </td>

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

                    <td style="text-align:center;">
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
                        <td style="max-width:300px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                            {{ $s->text }}
                        </td>
                    @endif

                    <td>{{ $s->created_at }}</td>

                </tr>
            @endforeach
        </x-slot:tbody>

        <x-slot:footer>
            <div style="display:flex; gap:10px; align-items:flex-end;">

                <a href="{{ route('admin.signatures', request()->except('select')) }}" title="Clear Selection"
                    style="display:flex;
                        flex-direction:column;
                        align-items:center;
                        justify-content:center;
                        gap:2px;
                        width:44px;
                        height:44px;
                        border:1px solid #bbb;
                        background:#fff;
                        text-decoration:none;">
                    {!! $icoX !!}
                    <div style="font-size:10px; color:#666;">Clear</div>
                </a>

            </div>
        </x-slot:footer>

    </x-admin.list-table-box>

    @include('admin.partials.bulk-js', [
        'banRoute' => route('admin.signatures.bulkDelete'),
        'noun' => 'signatures',
        'emptyMsg' => 'No signatures selected',
    ])

@endsection
