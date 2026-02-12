@extends('admin.layouts.app')

@section('title', 'Signatures')

@section('content')

    @php
        $icoX = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                <path d="M6 6l12 12" stroke="#d23b2a" stroke-width="2" stroke-linecap="round"/>
                <path d="M18 6L6 18" stroke="#d23b2a" stroke-width="2" stroke-linecap="round"/>
            </svg>';

        $icoMail = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                <path d="M4 6h16v12H4z" stroke="#777" stroke-width="2" />
                <path d="M4 7l8 6 8-6" stroke="#777" stroke-width="2" stroke-linejoin="round"/>
            </svg>';
    @endphp

    <h1>signatures ({{ number_format($approxTotal) }})</h1>

    @if (session('success'))
        <div class="fc-success">{{ session('success') }}</div>
    @endif

    <x-admin.filter-box title="filter signatures" :action="route('admin.signatures')" :reset="route('admin.signatures')">

        <input class="fc-input" name="petition_id" placeholder="Petition ID" value="{{ $filters['petition_id'] ?? '' }}"
            style="max-width:120px;">

        <input class="fc-input" name="email" placeholder="Email" value="{{ $filters['email'] ?? '' }}"
            style="max-width:220px;">

        @if($hasText)
            <input class="fc-input" name="text" placeholder="Text" value="{{ $filters['text'] ?? '' }}"
                style="max-width:220px;">
        @endif

        <select class="fc-select" name="category_id" style="max-width:260px;">
            <option value="">(Category)</option>
            @foreach($categories as $c)
                <option value="{{ $c->id }}" {{ ($filters['category_id'] ?? '') == $c->id ? 'selected' : '' }}>
                    {{ $c->name }} ({{ $c->locale }})
                </option>
            @endforeach
        </select>

    </x-admin.filter-box>

    <x-admin.list-table-box emptyText="no signatures found, try clearing filters" :p="$signatures" :bulk="[
            'banRoute' => route('admin.signatures.bulkDelete'),
            'banLabel' => 'Delete',
            'banConfirm' => 'Delete selected signatures?',
        ]">
        <x-slot:thead>
            <tr style="border-bottom:1px solid #ccc;" onmouseover="this.style.background='#f7f7f7'"
                onmouseout="this.style.background=''">
                <th style="text-align:left; width:70px;">ID</th>
                <th style="width:26px;"></th>
                <th style="width:26px;"></th>
                <th style="text-align:left;">User</th>
                <th style="width:26px;"></th>
                <th style="text-align:left;">Title</th>
                @if($hasText)
                    <th style="text-align:left;">Text</th>
                @endif
                <th style="text-align:left; width:160px;">Date</th>
            </tr>
        </x-slot:thead>

        <x-slot:tbody>
            @foreach ($signatures as $s)
                <tr style="border-bottom:1px solid #eee;">
                    <td>{{ $s->id }}</td>

                    <td style="text-align:left;">
                        <input type="checkbox" class="bulk-checkbox" value="{{ $s->id }}">
                    </td>

                    <td style="text-align:center;">
                        @if($s->user_verified)
                            <span class="fc-ok">✔</span>
                        @else
                            <span class="fc-no">✖</span>
                        @endif
                    </td>

                    <td style="white-space:nowrap;">
                        @if($s->user_id)
                            <a href="{{ route('admin.users', ['id' => $s->user_id]) }}"
                            style="text-decoration:none;">
                                {{ $s->user_name }}
                            </a>
                        @elseif(!empty($s->email))
                            <a href="mailto:{{ $s->email }}" style="text-decoration:none;">
                                {!! $icoMail !!} {{ $s->email }}
                            </a>
                        @else
                            {{ $s->name }}
                        @endif
                    </td>

                    <td style="text-align:center;">
                    @if($s->petition_slug)
                        <a href="{{ route('petition.show', [
                            'locale' => $s->locale,
                            'slug' => $s->petition_slug,
                            'id' => $s->petition_id,
                        ]) }}">
                            🔗

                        </a>
                    @endif
                    </td>

                    <td style="max-width:520px;">
                        <a href="{{ route('admin.petitions', ['id' => $s->petition_id]) }}" style="text-decoration:none;">
                                {{ $s->petition_title ?: ('petition #' . $s->petition_id) }}
                            </a>
                    </td>

                    @if($hasText)
                        <td style="max-width:420px;">{{ $s->text }}</td>
                    @endif

                    <td style="white-space:nowrap;">
                        {{ $s->created_at }}
                    </td>
                </tr>
            @endforeach
        </x-slot:tbody>

        <x-slot:footer>
            <div style="display:flex; gap:10px; align-items:flex-end;">
                <a href="{{ route('admin.signatures', request()->except('select')) }}" title="Clear Selection" style="display:flex; flex-direction:column; align-items:center; justify-content:center;
                   gap:2px; width:44px; height:44px; border:1px solid #bbb; background:#fff; text-decoration:none;">
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
