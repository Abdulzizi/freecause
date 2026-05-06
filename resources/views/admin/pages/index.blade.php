@extends('admin.layouts.app')

@section('title', 'Pages')

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

        $icoX = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none">
<path d="M6 6l12 12" stroke="#d23b2a" stroke-width="2" stroke-linecap="round"/>
<path d="M18 6L6 18" stroke="#d23b2a" stroke-width="2" stroke-linecap="round"/>
</svg>';

        $icoLink = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none">
<path d="M10 13a5 5 0 0 1 0-7l2-2a5 5 0 0 1 7 7l-1 1" stroke="#444" stroke-width="2"/>
<path d="M14 11a5 5 0 0 1 0 7l-2 2a5 5 0 0 1-7-7l1-1" stroke="#444" stroke-width="2"/>
</svg>';
    @endphp


    <h1>pages ({{ number_format($approxTotal) }})</h1>


    <x-admin.filter-box title="filter pages" :action="route('admin.pages')" :reset="route('admin.pages')">

        <input class="fc-input" name="id" placeholder="ID" value="{{ $filters['id'] ?? '' }}" style="max-width:90px;">

        <input class="fc-input" name="title" placeholder="Title" value="{{ $filters['title'] ?? '' }}"
            style="max-width:220px;">

        <select class="fc-select" name="locale" style="max-width:160px;">
            @foreach ($locales as $k => $label)
                <option value="{{ $k }}" {{ ($filters['locale'] ?? '') === $k ? 'selected' : '' }}>
                    {{ $label }}
                </option>
            @endforeach
        </select>

    </x-admin.filter-box>


    <x-admin.list-table-box emptyText="no pages found" :p="$pages">

        <x-slot:thead>
            <tr style="border-bottom:1px solid #ccc;">
                <th width="70">ID</th>
                <th width="26"></th>
                <th width="26">P</th>
                <th width="26"></th>
                <th style="text-align: start">Title</th>
                <th>Slug</th>
                <th width="90">Locale</th>
            </tr>
        </x-slot:thead>

        <x-slot:tbody>
            @foreach ($pages as $p)
                <tr style="border-bottom:1px solid #eee;">

                    <td style="text-align: center">
                        <a
                            href="{{ route('admin.pages', array_merge(request()->query(), ['select' => $p->page_id, 'locale' => $p->locale])) }}">
                            {{ $p->page_id }}
                        </a>
                    </td>

                    <td>
                        <input type="checkbox" class="bulk-checkbox" value="{{ $p->page_id }}">
                    </td>

                    <td style="text-align:center;">
                        @if ($p->published)
                            <span class="fc-ok">✔</span>
                        @else
                            <span class="fc-no">✖</span>
                        @endif
                    </td>

                    <td style="text-align:center;">
                        <a href="{{ $p->client_url }}" target="_blank" title="Open page">
                            {!! $icoLink !!}
                        </a>
                    </td>

                    <td>{{ $p->title }}</td>
                    <td style="text-align: center">{{ $p->slug }}</td>
                    <td style="text-align: center">{{ $p->locale }}</td>

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

                <button type="button" class="fc-icon-btn bulk-action" data-action="delete">
                    {!! $icoX !!}
                    <div style="font-size:10px; color:#666;">Delete</div>
                </button>

                <a href="{{ route('admin.pages', request()->except('select')) }}" title="Clear Selection"
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


    <x-admin.detail-panel title="page">

        @if ($selectedPage)
            <form method="POST" action="{{ route('admin.pages.save') }}">
                @csrf

                <input type="hidden" name="page_id" value="{{ $selectedPage->page_id }}">
                <input type="hidden" name="locale" value="{{ $selectedPage->locale }}">

                <div class="fc-row">
                    <label>published</label>
                    <input type="checkbox" name="published" value="1" {{ $selectedPage->published ? 'checked' : '' }}>
                </div>

                <div class="fc-row">
                    <label>title</label>
                    <input class="fc-input" name="title" value="{{ $selectedPage->title }}">
                </div>

                <div class="fc-row">
                    <label>slug</label>
                    <input class="fc-input" name="slug" value="{{ $selectedPage->slug }}">
                </div>

                <div class="fc-row">
                    <label>content</label>
                    <textarea id="content" name="content" class="fc-textarea fc-editor" rows="12">{{ $selectedPage->content }}</textarea>
                </div>

                <div style="display:flex; justify-content:flex-end;">
                    <button class="fc-btn">save</button>
                </div>

            </form>
        @else
            <div style="color:#777;">select a page to edit</div>
        @endif

    </x-admin.detail-panel>


    @include('admin.partials.bulk-js', [
        'actionRoute' => route('admin.pages.bulkAction'),
        'noun' => 'pages',
        'emptyMsg' => 'No pages selected',
    ])

@endsection
