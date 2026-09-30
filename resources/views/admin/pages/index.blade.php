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


    @php
        $activeFilterCount = collect($filters)->filter(fn($v) => $v !== '' && $v !== null)->count();
    @endphp

    <x-admin.filter-box title="filter pages" :action="route('admin.pages')" :reset="route('admin.pages')" :activeCount="$activeFilterCount">

        <input class="w-full! box-border! border! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white! max-w-[90px]!" name="id" placeholder="ID" value="{{ $filters['id'] ?? '' }}">

        <input class="w-full! box-border! border! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white! max-w-[220px]!" name="title" placeholder="Title" value="{{ $filters['title'] ?? '' }}">

        <select class="w-full! box-border! border! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white! max-w-[160px]!" name="locale">
            @foreach ($locales as $k => $label)
                <option value="{{ $k }}" {{ ($filters['locale'] ?? '') === $k ? 'selected' : '' }}>
                    {{ $label }}
                </option>
            @endforeach
        </select>

    </x-admin.filter-box>


    <x-admin.list-table-box emptyText="no pages found" :p="$pages">

        <x-slot:thead>
            <tr class="border-b! border-[#ccc]!">
                <th width="70">ID</th>
                <th width="26" data-hide-mobile></th>
                <th width="26">P</th>
                <th width="26" data-hide-mobile></th>
                <th class="text-start!">Title</th>
                <th data-hide-mobile>Slug</th>
                <th class="w-[90px]!" data-hide-mobile>Locale</th>
            </tr>
        </x-slot:thead>

        <x-slot:tbody>
            @foreach ($pages as $p)
                <tr class="border-b! border-[#eee]!">

                    <td class="text-center!">
                        <a
                            href="{{ route('admin.pages', array_merge(request()->query(), ['select' => $p->page_id, 'locale' => $p->locale])) }}">
                            {{ $p->page_id }}
                        </a>
                    </td>

                    <td data-hide-mobile>
                        <input type="checkbox" class="bulk-checkbox" value="{{ $p->page_id }}">
                    </td>

                    <td class="text-center!">
                        @if ($p->published)
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#5c8f3a" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        @else
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#c00" stroke-width="2.5" stroke-linecap="round"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
                        @endif
                    </td>

                    <td data-hide-mobile class="text-center!">
                        <a href="{{ $p->client_url }}" target="_blank" title="Open page">
                            {!! $icoLink !!}
                        </a>
                    </td>

                    <td>{{ $p->title }}</td>
                    <td data-hide-mobile class="text-center!">{{ $p->slug }}</td>
                    <td data-hide-mobile class="text-center!">{{ $p->locale }}</td>

                </tr>
            @endforeach
        </x-slot:tbody>


        <x-slot:footer>
            <div class="flex! gap-2! items-center! flex-wrap!">
                <div class="flex! gap-0.5! p-0.5! bg-[#f8f8f8]! border! border-[#eee]! rounded!">
                    <button type="button" class="flex! flex-col! items-center! justify-center! gap-0.5! w-10! h-[38px]! md:w-[52px]! md:h-[44px]! border! border-[#bbb]! bg-white! cursor-pointer! transition-[background,opacity]! duration-150! hover:bg-[#f7f7f7]! active:bg-[#dcdcdc]! disabled:opacity-[0.35]! disabled:pointer-events-none! bulk-action" data-action="publish" title="Publish">
                        {!! $icoPublish !!}
                        <span class="text-[10px]! text-[#666]! leading-none! hidden! md:inline!">Publish</span>
                    </button>

                    <button type="button" class="flex! flex-col! items-center! justify-center! gap-0.5! w-10! h-[38px]! md:w-[52px]! md:h-[44px]! border! border-[#bbb]! bg-white! cursor-pointer! transition-[background,opacity]! duration-150! hover:bg-[#f7f7f7]! active:bg-[#dcdcdc]! disabled:opacity-[0.35]! disabled:pointer-events-none! bulk-action" data-action="unpublish" title="Unpublish">
                        {!! $icoUnpublish !!}
                        <span class="text-[10px]! text-[#666]! leading-none! hidden! md:inline!">Unpublish</span>
                    </button>
                </div>

                <button type="button" class="flex! flex-col! items-center! justify-center! gap-0.5! w-10! h-[38px]! md:w-[52px]! md:h-[44px]! border! border-[#bbb]! bg-white! cursor-pointer! transition-[background,opacity]! duration-150! hover:bg-[#f7f7f7]! active:bg-[#dcdcdc]! disabled:opacity-[0.35]! disabled:pointer-events-none! bulk-action" data-action="delete" title="Delete">
                    {!! $icoX !!}
                    <span class="text-[10px]! text-[#666]! leading-none! hidden! md:inline!">Delete</span>
                </button>

                <a href="{{ route('admin.pages', request()->except('select')) }}"
                    title="Clear selection" class="flex! flex-col! items-center! justify-center! gap-0.5! w-10! h-[38px]! md:w-[52px]! md:h-[44px]! border! border-[#bbb]! bg-white! cursor-pointer! transition-[background,opacity]! duration-150! hover:bg-[#f7f7f7]! active:bg-[#dcdcdc]! text-[#666]! no-underline!">
                    {!! $icoX !!}
                    <span class="text-[10px]! text-[#666]! leading-none! hidden! md:inline!">Clear</span>
                </a>
            </div>
        </x-slot:footer>

    </x-admin.list-table-box>


    <x-admin.detail-panel title="page" :closeRoute="route('admin.pages', request()->except('select'))">

        @if ($selectedPage)
            <form method="POST" action="{{ route('admin.pages.save') }}">
                @csrf

                <input type="hidden" name="page_id" value="{{ $selectedPage->page_id }}">
                <input type="hidden" name="locale" value="{{ $selectedPage->locale }}">

                <div class="grid! grid-cols-1! gap-1.5! items-start! py-1.5! md:grid-cols-[160px_1fr]! md:gap-3! md:items-center!">
                    <label class="font-bold! text-xs! md:text-sm!">published</label>
                    <input type="checkbox" name="published" value="1" {{ $selectedPage->published ? 'checked' : '' }}>
                </div>

                <div class="grid! grid-cols-1! gap-1.5! items-start! py-1.5! md:grid-cols-[160px_1fr]! md:gap-3! md:items-center!">
                    <label class="font-bold! text-xs! md:text-sm!">title</label>
                    <input class="w-full! box-border! border! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white!" name="title" value="{{ $selectedPage->title }}">
                </div>

                <div class="grid! grid-cols-1! gap-1.5! items-start! py-1.5! md:grid-cols-[160px_1fr]! md:gap-3! md:items-center!">
                    <label class="font-bold! text-xs! md:text-sm!">slug</label>
                    <input class="w-full! box-border! border! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white!" name="slug" value="{{ $selectedPage->slug }}">
                </div>

                <div class="grid! grid-cols-1! gap-1.5! items-start! py-1.5! md:grid-cols-[160px_1fr]! md:gap-3! md:items-center!">
                    <label class="font-bold! text-xs! md:text-sm!">content</label>
                    <textarea id="content" name="content" class="w-full! box-border! border! border-[#bdbdbd]! rounded! px-2! py-1.5! bg-white! min-h-[140px]! resize-y! font-mono! text-[13px]! fc-editor" rows="12">{{ $selectedPage->content }}</textarea>
                </div>

                <div class="flex! justify-end!">
                    <button class="border! border-[#7d7d7d]! bg-[#e6e6e6]! px-[10px]! py-1! rounded-[3px]! cursor-pointer! hover:bg-[#dcdcdc]!">save</button>
                </div>

            </form>
        @else
            <div class="text-[#777]!">select a page to edit</div>
        @endif

    </x-admin.detail-panel>


    @include('admin.partials.bulk-js', [
        'actionRoute' => route('admin.pages.bulkAction'),
        'noun' => 'pages',
        'emptyMsg' => 'No pages selected',
    ])

@endsection
