@extends('admin.layouts.app')

@section('title', 'Petitions')

@section('content')

    @php
        $icoPublish = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none">
        <path d="M12 3v12" stroke="#2f6f2f" stroke-width="2" stroke-linecap="round"/>
        <path d="M7 8l5-5 5 5" stroke="#2f6f2f" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        <path d="M5 21h14" stroke="#777" stroke-width="2" stroke-linecap="round"/>
    </svg>';

        $icoUnpublish = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none">
        <path d="M12 21V9" stroke="#8a6d1e" stroke-width="2" stroke-linecap="round"/>
        <path d="M17 16l-5 5-5-5" stroke="#8a6d1e" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        <path d="M5 3h14" stroke="#777" stroke-width="2" stroke-linecap="round"/>
    </svg>';

        $icoStats = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none">
        <path d="M4 19V5" stroke="#555" stroke-width="2" stroke-linecap="round"/>
        <path d="M4 19h16" stroke="#555" stroke-width="2" stroke-linecap="round"/>
        <path d="M8 16v-5" stroke="#3f6fb6" stroke-width="2" stroke-linecap="round"/>
        <path d="M12 16v-8" stroke="#3f6fb6" stroke-width="2" stroke-linecap="round"/>
        <path d="M16 16v-3" stroke="#3f6fb6" stroke-width="2" stroke-linecap="round"/>
    </svg>';

        $icoX = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none">
        <path d="M6 6l12 12" stroke="#d23b2a" stroke-width="2" stroke-linecap="round"/>
        <path d="M18 6L6 18" stroke="#d23b2a" stroke-width="2" stroke-linecap="round"/>
    </svg>';
    @endphp

    <h1>petitions ({{ number_format($approxTotal) }})</h1>

    @if (session('success'))
        <div class="fc-success">{{ session('success') }}</div>
    @endif

    <x-admin.filter-box title="filter petitions" :action="route('admin.petitions')" :reset="route('admin.petitions')">
        <input type="hidden" name="locale" value="{{ $locale }}">

        <input class="fc-input" name="id" placeholder="ID" value="{{ $filters['id'] ?? '' }}" style="max-width:90px;">
        <input class="fc-input" name="title" placeholder="Title" value="{{ $filters['title'] ?? '' }}"
            style="max-width:220px;">

        <label>
            <input type="checkbox" name="featured" value="1"
                {{ ($filters['featured'] ?? '') !== '' ? 'checked' : '' }}>
            featured only
        </label>
    </x-admin.filter-box>

    <x-admin.list-table-box emptyText="no petitions found, try clearing filters" :p="$petitions" :bulk="[
        'banRoute' => route('admin.petitions.bulkBan'),
        'banLabel' => 'Banned',
        'banConfirm' => 'Ban selected petitions?',
    ]">
        <x-slot:thead>
            <tr style="border-bottom:1px solid #ccc;" onmouseover="this.style.background='#f7f7f7'"
                onmouseout="this.style.background=''">
                <th style="text-align:left;">id</th>
                <th style="width:26px;"></th>
                <th style="width:26px;">A</th>
                <th style="width:26px;">P</th>
                <th style="width:26px;">F</th>
                <th>Signatures</th>
                <th>Title</th>
                <th>Date</th>
            </tr>
        </x-slot:thead>

        <x-slot:tbody>
            @foreach ($petitions as $p)
                <tr style="border-bottom:1px solid #eee;">
                    <td>
                        <a href="{{ route('admin.petitions', array_merge(request()->query(), ['select' => $p->id])) }}">
                            {{ $p->id }}
                        </a>
                    </td>

                    <td style="text-align:left;">
                        <input type="checkbox" class="bulk-checkbox" value="{{ $p->id }}">
                    </td>

                    <td>
                        @if ($p->is_active)
                            <span style="color:#5c8f3a; font-weight:bold;">✔</span>
                        @else
                            <span style="color:#c00;">✖</span>
                        @endif
                    </td>

                    <td>
                        @if ($p->status === 'published')
                            <span style="color:#5c8f3a; font-weight:bold;">✔</span>
                        @else
                            <span style="color:#c00;">✖</span>
                        @endif
                    </td>

                    <td>
                        @if ($p->is_featured)
                            <span style="color:#5c8f3a; font-weight:bold;">✔</span>
                        @else
                            <span style="color:#c00;">✖</span>
                        @endif
                    </td>

                    <td>{{ $p->signature_count }} / {{ $p->goal_signatures }}</td>
                    <td>{{ $p->title }}</td>
                    <td>{{ $p->created_at }}</td>
                </tr>
            @endforeach
        </x-slot:tbody>

        <x-slot:footer>
            <div style="display:flex; gap:10px; align-items:flex-end;">

                <button type="button" class="bulk-action" data-action="unpublish" title="Unpublish"
                    style="width:52px; height:44px; border:1px solid #bbb; background:#fff;
                   display:flex; flex-direction:column; align-items:center; justify-content:center; gap:2px; cursor:pointer;">
                    {!! $icoUnpublish !!}
                    <div style="font-size:10px; color:#666;">Unpublish</div>
                </button>

                <button type="button" class="bulk-action" data-action="publish" title="Publish"
                    style="width:52px; height:44px; border:1px solid #bbb; background:#fff;
                   display:flex; flex-direction:column; align-items:center; justify-content:center; gap:2px; cursor:pointer;">
                    {!! $icoPublish !!}
                    <div style="font-size:10px; color:#666;">Publish</div>
                </button>

                <button type="button" class="bulk-action" data-action="stats" title="Stats"
                    style="width:52px; height:44px; border:1px solid #bbb; background:#fff;
                   display:flex; flex-direction:column; align-items:center; justify-content:center; gap:2px; cursor:pointer;">
                    {!! $icoStats !!}
                    <div style="font-size:10px; color:#666;">Stats</div>
                </button>

                <button type="button" class="bulk-action" data-action="unban" title="Unban"
                    style="width:52px; height:44px; border:1px solid #bbb; background:#fff;
                   display:flex; flex-direction:column; align-items:center; justify-content:center; gap:2px; cursor:pointer;">
                    {!! $icoPublish !!}
                    <div style="font-size:10px; color:#666;">Unban</div>
                </button>

                <button type="button" class="bulk-action" data-action="ban_ip" title="Ban IP"
                    style="width:52px; height:44px; border:1px solid #bbb; background:#fff;
                   display:flex; flex-direction:column; align-items:center; justify-content:center; gap:2px; cursor:pointer;">
                    {!! $icoUnpublish !!}
                    <div style="font-size:10px; color:#666;">Ban IP</div>
                </button>

                <button type="button" class="bulk-action" data-action="export" title="Export"
                    style="width:52px; height:44px; border:1px solid #bbb; background:#fff;
                   display:flex; flex-direction:column; align-items:center; justify-content:center; gap:2px; cursor:pointer;">
                    {!! $icoStats !!}
                    <div style="font-size:10px; color:#666;">Export</div>
                </button>

                <a href="{{ route('admin.petitions', request()->except('select')) }}" title="Clear Selection"
                    style="display:flex; flex-direction:column; align-items:center; justify-content:center;
                  gap:2px; width:44px; height:44px; border:1px solid #bbb; background:#fff;
                  text-decoration:none;">
                    {!! $icoX !!}
                    <div style="font-size:10px; color:#666;">Clear</div>
                </a>

            </div>
        </x-slot:footer>

    </x-admin.list-table-box>

    <x-admin.detail-panel title="petition">
        @if ($selectedPetition)
            <form method="post" action="{{ route('admin.petitions.save') }}">
                @csrf
                <input type="hidden" name="id" value="{{ $selectedPetition->id }}">
                <input type="hidden" name="locale" value="{{ $locale }}">

                <div class="fc-row">
                    <label>active</label>
                    <input type="checkbox" name="is_active" value="1"
                        {{ $selectedPetition->is_active ? 'checked' : '' }}>
                </div>

                <div class="fc-row">
                    <label>status</label>
                    <select class="fc-select" name="status">
                        <option value="draft" {{ ($selectedPetition->status ?? '') === 'draft' ? 'selected' : '' }}>draft
                        </option>
                        <option value="published"
                            {{ ($selectedPetition->status ?? '') === 'published' ? 'selected' : '' }}>published</option>
                    </select>
                </div>

                <div class="fc-row">
                    <label>featured</label>
                    <input type="checkbox" name="is_featured" value="1"
                        {{ $selectedPetition->is_featured ? 'checked' : '' }}>
                </div>

                <div class="fc-row">
                    <label>title</label>
                    <input class="fc-input" type="text" name="title"
                        value="{{ $selectedTranslation->title ?? '' }}">
                </div>

                <div class="fc-row">
                    <label>url slug</label>
                    <input class="fc-input" type="text" name="slug"
                        value="{{ $selectedTranslation->slug ?? '' }}">
                </div>

                <div class="fc-row">
                    <label>signature goal</label>
                    <input class="fc-input" type="number" name="goal_signatures"
                        value="{{ $selectedPetition->goal_signatures ?? 100 }}">
                </div>

                <div class="fc-row">
                    <label>text</label>
                    <textarea class="fc-input" name="text" rows="8">{{ $selectedTranslation->description ?? '' }}</textarea>
                </div>

                <div style="display:flex; justify-content:flex-end;">
                    <button class="fc-btn" type="submit">save</button>
                </div>
            </form>
        @else
            <div style="color:#777;">select a petition to edit</div>
        @endif
    </x-admin.detail-panel>

@endsection

@include('admin.partials.bulk-js', [
    'actionRoute' => route('admin.petitions.bulkAction'),
    'banRoute' => route('admin.petitions.bulkBan'),
    'noun' => 'petitions',
    'emptyMsg' => 'No petitions selected',
])
