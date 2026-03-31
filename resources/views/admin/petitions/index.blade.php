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

        $icoFeature = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none">
        <path d="M12 3l3 6 6 .5-4.5 4 1.5 6L12 17l-6 2.5 1.5-6L3 9.5 9 9l3-6z"
        stroke="#c69200" stroke-width="2" fill="none"/>
    </svg>';

        $icoBan = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none">
        <circle cx="12" cy="12" r="8" stroke="#c00" stroke-width="2"/>
        <path d="M8 8l8 8" stroke="#c00" stroke-width="2"/>
    </svg>';

        $icoX = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none">
        <path d="M6 6l12 12" stroke="#d23b2a" stroke-width="2" stroke-linecap="round"/>
        <path d="M18 6L6 18" stroke="#d23b2a" stroke-width="2" stroke-linecap="round"/>
    </svg>';
    @endphp

    <h1>petitions ({{ number_format($approxTotal) }})</h1>

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


    <x-admin.list-table-box emptyText="no petitions found" :p="$petitions" :bulk="[
        // 'banRoute' => route('admin.petitions.bulkAction'),
        'banLabel' => 'Ban',
        'banConfirm' => 'Ban selected petitions?',
    ]">

        <x-slot:thead>
            <tr style="border-bottom:1px solid #ccc;" onmouseover="this.style.background='#f7f7f7'"
                onmouseout="this.style.background=''">
                <th>ID</th>
                <th width="26"></th>
                <th width="26">A</th>
                <th width="26">P</th>
                <th width="26">F</th>
                <th>Signatures</th>
                <th>Title</th>
                <th width="150">Date</th>
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

                    <td>
                        <input type="checkbox" class="bulk-checkbox" value="{{ $p->id }}">
                    </td>

                    {{-- ACTIVE --}}
                    <td>
                        {!! $p->is_active
                            ? '<span style="color:#5c8f3a;font-weight:bold;">✔</span>'
                            : '<span style="color:#c00;">✖</span>' !!}
                    </td>

                    {{-- PUBLISHED --}}
                    <td>
                        {!! $p->status === 'published'
                            ? '<span style="color:#5c8f3a;font-weight:bold;">✔</span>'
                            : '<span style="color:#c00;">✖</span>' !!}
                    </td>

                    {{-- FEATURED --}}
                    <td>
                        {!! $p->is_featured
                            ? '<span style="color:#5c8f3a;font-weight:bold;">✔</span>'
                            : '<span style="color:#c00;">✖</span>' !!}
                    </td>

                    <td>{{ $p->signature_count }} / {{ $p->goal_signatures }}</td>
                    <td>{{ $p->title }}</td>
                    <td>{{ $p->created_at }}</td>

                </tr>
            @endforeach
        </x-slot:tbody>


        <x-slot:footer>
            <div style="display:flex; gap:10px; align-items:flex-end;">

                <button type="button" class="fc-icon-btn bulk-action" data-action="publish">
                    {!! $icoPublish !!}
                    <div style="font-size:10px; color:#666;">Publish</div>
                </button>

                <button type="button" class="fc-icon-btn bulk-action" data-action="unpublish">
                    {!! $icoUnpublish !!}
                    <div style="font-size:10px; color:#666;">Unpublish</div>
                </button>

                <button type="button" class="fc-icon-btn bulk-action" data-action="activate">
                    {!! $icoPublish !!}
                    <div style="font-size:10px; color:#666;">Activate</div>
                </button>

                <button type="button" class="fc-icon-btn bulk-action" data-action="deactivate">
                    {!! $icoUnpublish !!}
                    <div style="font-size:10px; color:#666;">Deactivate</div>
                </button>

                <button type="button" class="fc-icon-btn bulk-action" data-action="feature">
                    {!! $icoFeature !!}
                    <div style="font-size:10px; color:#666;">Feature</div>
                </button>

                <button type="button" class="fc-icon-btn bulk-action" data-action="unfeature">
                    {!! $icoFeature !!}
                    <div style="font-size:10px; color:#666;">Unfeature</div>
                </button>

                {{-- <button type="button" class="fc-icon-btn bulk-action" data-action="ban">
                    {!! $icoBan !!}
                    <div style="font-size:10px; color:#666;">Ban</div>
                </button> --}}

                <a href="{{ route('admin.petitions', request()->except('select')) }}" title="Clear Selection"
                    class="fc-icon-btn" style="text-decoration:none;">
                    {!! $icoX !!}
                    <div style="font-size:10px; color:#666;">Clear</div>
                </a>

            </div>
        </x-slot:footer>

    </x-admin.list-table-box>


    <x-admin.detail-panel title="petition">
        @if ($selectedPetition)
            <form method="post" action="{{ route('admin.petitions.save') }}" enctype="multipart/form-data">
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
                        <option value="draft" {{ $selectedPetition->status === 'draft' ? 'selected' : '' }}>draft</option>
                        <option value="published" {{ $selectedPetition->status === 'published' ? 'selected' : '' }}>
                            published
                        </option>
                    </select>
                </div>

                <div class="fc-row">
                    <label>featured</label>
                    <input type="checkbox" name="is_featured" value="1"
                        {{ $selectedPetition->is_featured ? 'checked' : '' }}>
                </div>

                <div class="fc-row">
                    <label>owner (user id)</label>
                    <input class="fc-input" type="number" name="user_id" value="{{ $selectedPetition->user_id }}"
                        placeholder="user ID" style="max-width:100px;">
                </div>

                <div class="fc-row">
                    <label>title</label>
                    <input class="fc-input" type="text" name="title" value="{{ $selectedTranslation->title ?? '' }}">
                </div>

                <div class="fc-row">
                    <label>slug</label>
                    <input class="fc-input" type="text" name="slug" value="{{ $selectedTranslation->slug ?? '' }}">
                </div>

                <div class="fc-row">
                    <label>signature goal</label>
                    <input class="fc-input" type="number" name="goal_signatures"
                        value="{{ $selectedPetition->goal_signatures }}">
                </div>

                <div class="fc-row">
                    <label>text</label>
                    <textarea class="fc-input" name="text" rows="8">{{ $selectedTranslation->description ?? '' }}</textarea>
                </div>

                {{-- Image management --}}
                <div class="fc-row" style="flex-direction:column; align-items:flex-start; gap:6px;">
                    <label>cover image</label>
                    @php
                        $imgSrc = null;
                        if (!empty($selectedPetition->cover_image)) {
                            $imgSrc = str_starts_with($selectedPetition->cover_image, 'http')
                                ? $selectedPetition->cover_image
                                : asset('storage/' . $selectedPetition->cover_image);
                        } elseif (!empty($selectedPetition->image_url)) {
                            $imgSrc = $selectedPetition->image_url;
                        }
                    @endphp
                    @if ($imgSrc)
                        <img src="{{ $imgSrc }}" alt="cover" style="max-width:220px; max-height:120px; border-radius:4px; object-fit:cover;">
                        <label style="font-size:0.85em;">
                            <input type="checkbox" name="remove_image" value="1"> remove image
                        </label>
                    @else
                        <span style="color:#999; font-size:0.85em;">no image</span>
                    @endif
                    <input class="fc-input" type="file" name="cover_image" accept="image/*" style="margin-top:4px;">
                    <span style="color:#888; font-size:0.8em;">upload new image to replace current (max 4 MB)</span>
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
    'noun' => 'petitions',
    'emptyMsg' => 'No petitions selected',
])
