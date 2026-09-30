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

    @php
        $activeFilterCount = collect($filters)->filter(fn($v) => $v !== '' && $v !== null && $v !== 'locale')->count();
    @endphp

    <x-admin.filter-box title="filter petitions" :action="route('admin.petitions')" :reset="route('admin.petitions')" :activeCount="$activeFilterCount">
        <input type="hidden" name="locale" value="{{ $locale }}">

        <input class="w-full! box-border! border! border-solid! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white! max-w-[90px]! max-md:max-w-none! max-md:flex-[1_1_140px]!" name="id" placeholder="ID" value="{{ $filters['id'] ?? '' }}">

        <input class="w-full! box-border! border! border-solid! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white! max-w-[220px]! max-md:max-w-none! max-md:flex-[1_1_140px]!" name="title" placeholder="Title" value="{{ $filters['title'] ?? '' }}">

        <input class="w-full! box-border! border! border-solid! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white! max-w-[100px]! max-md:max-w-none! max-md:flex-[1_1_140px]!" name="owner" placeholder="Owner ID" value="{{ $filters['owner'] ?? '' }}">

        <select class="w-full! box-border! border! border-solid! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white! max-w-[160px]! max-md:max-w-none! max-md:flex-[1_1_140px]!" name="category">
            <option value="">category...</option>
            @foreach ($categories as $cat)
                <option value="{{ $cat->id }}" {{ (string) ($filters['category'] ?? '') === (string) $cat->id ? 'selected' : '' }}>
                    {{ $cat->name ?? ('#' . $cat->id) }}
                </option>
            @endforeach
        </select>

        <select class="w-full! box-border! border! border-solid! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white! max-w-[130px]! max-md:max-w-none! max-md:flex-[1_1_140px]!" name="status">
            <option value="">status...</option>
            <option value="published" {{ ($filters['status'] ?? '') === 'published' ? 'selected' : '' }}>published</option>
            <option value="draft" {{ ($filters['status'] ?? '') === 'draft' ? 'selected' : '' }}>draft</option>
        </select>

        <select class="w-full! box-border! border! border-solid! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white! max-w-[120px]! max-md:max-w-none! max-md:flex-[1_1_140px]!" name="active">
            <option value="">active...</option>
            <option value="1" {{ ($filters['active'] ?? '') === '1' ? 'selected' : '' }}>active</option>
            <option value="0" {{ ($filters['active'] ?? '') === '0' ? 'selected' : '' }}>inactive</option>
        </select>

        <label>
            <input type="checkbox" name="featured" value="1"
                {{ ($filters['featured'] ?? '') !== '' ? 'checked' : '' }}>
            featured only
        </label>

        @if (!empty($activeLanguages))
            <select class="w-full! box-border! border! border-solid! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white! max-w-[160px]! max-md:max-w-none! max-md:flex-[1_1_140px]!" name="missing_locale">
                <option value="">missing locale...</option>
                @foreach ($activeLanguages as $lang)
                    <option value="{{ $lang }}" {{ ($filters['missing_locale'] ?? '') === $lang ? 'selected' : '' }}>
                        missing: {{ $lang }}
                    </option>
                @endforeach
            </select>
        @endif
    </x-admin.filter-box>


    <x-admin.list-table-box emptyText="no petitions found" :p="$petitions" :bulk="[
        // 'banRoute' => route('admin.petitions.bulkAction'),
        'banLabel' => 'Ban',
        'banConfirm' => 'Ban selected petitions?',
    ]">

        <x-slot:thead>
            <tr class="border-b! border-solid! border-[#ccc]!" onmouseover="this.style.background='#f7f7f7'"
                onmouseout="this.style.background=''">
                <th>ID</th>
                <th width="26" data-hide-mobile></th>
                <th width="26">A</th>
                <th width="26">P</th>
                <th width="26">F</th>
                <th data-hide-mobile>Signatures</th>
                <th>Title</th>
                <th data-hide-mobile>Locales</th>
                <th width="150" data-hide-mobile>Date</th>
            </tr>
        </x-slot:thead>

        <x-slot:tbody>
            @foreach ($petitions as $p)
                <tr class="border-b! border-solid! border-[#eee]!">

                    <td>
                        <a href="{{ route('admin.petitions', array_merge(request()->query(), ['select' => $p->id])) }}">
                            {{ $p->id }}
                        </a>
                    </td>

                    <td data-hide-mobile>
                        <input type="checkbox" class="bulk-checkbox" value="{{ $p->id }}">
                    </td>

                    {{-- ACTIVE --}}
                    <td>
                        {!! $p->is_active
                            ? '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#5c8f3a" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>'
                            : '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#c00" stroke-width="2.5" stroke-linecap="round"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>' !!}
                    </td>

                    {{-- PUBLISHED --}}
                    <td>
                        {!! $p->status === 'published'
                            ? '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#5c8f3a" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>'
                            : '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#c00" stroke-width="2.5" stroke-linecap="round"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>' !!}
                    </td>

                    {{-- FEATURED --}}
                    <td>
                        {!! $p->is_featured
                            ? '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#5c8f3a" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>'
                            : '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#c00" stroke-width="2.5" stroke-linecap="round"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>' !!}
                    </td>

                    <td data-hide-mobile>{{ $p->signature_count }} / {{ $p->goal_signatures }}</td>
                    <td>{{ $p->title }}</td>
                    <td data-hide-mobile>
                        @php
                            $petitionLocales = $p->translation_locales ? explode(',', $p->translation_locales) : [];
                        @endphp
                        @foreach ($activeLanguages as $lang)
                            @if (in_array($lang, $petitionLocales))
                                <span class="inline-block! bg-[#d4edda]! text-[#155724]! rounded-[3px]! px-[5px]! py-px! text-[10px]! m-px!">{{ $lang }}</span>
                            @else
                                <span class="inline-block! bg-[#f8d7da]! text-[#721c24]! rounded-[3px]! px-[5px]! py-px! text-[10px]! m-px!">{{ $lang }}</span>
                            @endif
                        @endforeach
                    </td>
                    <td data-hide-mobile>{{ $p->created_at }}</td>

                </tr>
            @endforeach
        </x-slot:tbody>


        <x-slot:footer>
            <div class="flex! gap-2! items-center! flex-wrap! max-md:gap-1.5! max-md:w-full! max-md:justify-end!">
                <div class="flex! gap-0.5! p-0.5! bg-[#f8f8f8]! border! border-solid! border-[#eee]! rounded! max-md:gap-px!">
                    <button type="button" class="w-[52px]! h-[44px]! border! border-solid! border-[#bbb]! bg-white! flex! flex-col! items-center! justify-center! gap-0.5! cursor-pointer! transition-colors! transition-opacity! duration-150! hover:bg-[#f7f7f7]! active:bg-[#dcdcdc]! disabled:opacity-35! disabled:pointer-events-none! max-md:w-10! max-md:h-[38px]! max-md:gap-px! max-md:[&_svg]:w-4! max-md:[&_svg]:h-4! bulk-action" data-action="publish" title="Publish">
                        {!! $icoPublish !!}
                        <span class="text-[10px]! text-[#666]! leading-none! max-md:hidden!">Publish</span>
                    </button>
                    <button type="button" class="w-[52px]! h-[44px]! border! border-solid! border-[#bbb]! bg-white! flex! flex-col! items-center! justify-center! gap-0.5! cursor-pointer! transition-colors! transition-opacity! duration-150! hover:bg-[#f7f7f7]! active:bg-[#dcdcdc]! disabled:opacity-35! disabled:pointer-events-none! max-md:w-10! max-md:h-[38px]! max-md:gap-px! max-md:[&_svg]:w-4! max-md:[&_svg]:h-4! bulk-action" data-action="unpublish" title="Unpublish">
                        {!! $icoUnpublish !!}
                        <span class="text-[10px]! text-[#666]! leading-none! max-md:hidden!">Unpublish</span>
                    </button>
                </div>

                <div class="flex! gap-0.5! p-0.5! bg-[#f8f8f8]! border! border-solid! border-[#eee]! rounded! max-md:gap-px!">
                    <button type="button" class="w-[52px]! h-[44px]! border! border-solid! border-[#bbb]! bg-white! flex! flex-col! items-center! justify-center! gap-0.5! cursor-pointer! transition-colors! transition-opacity! duration-150! hover:bg-[#f7f7f7]! active:bg-[#dcdcdc]! disabled:opacity-35! disabled:pointer-events-none! max-md:w-10! max-md:h-[38px]! max-md:gap-px! max-md:[&_svg]:w-4! max-md:[&_svg]:h-4! bulk-action" data-action="activate" title="Activate">
                        {!! $icoPublish !!}
                        <span class="text-[10px]! text-[#666]! leading-none! max-md:hidden!">Activate</span>
                    </button>
                    <button type="button" class="w-[52px]! h-[44px]! border! border-solid! border-[#bbb]! bg-white! flex! flex-col! items-center! justify-center! gap-0.5! cursor-pointer! transition-colors! transition-opacity! duration-150! hover:bg-[#f7f7f7]! active:bg-[#dcdcdc]! disabled:opacity-35! disabled:pointer-events-none! max-md:w-10! max-md:h-[38px]! max-md:gap-px! max-md:[&_svg]:w-4! max-md:[&_svg]:h-4! bulk-action" data-action="deactivate" title="Deactivate">
                        {!! $icoUnpublish !!}
                        <span class="text-[10px]! text-[#666]! leading-none! max-md:hidden!">Deactivate</span>
                    </button>
                </div>

                <div class="flex! gap-0.5! p-0.5! bg-[#f8f8f8]! border! border-solid! border-[#eee]! rounded! max-md:gap-px!">
                    <button type="button" class="w-[52px]! h-[44px]! border! border-solid! border-[#bbb]! bg-white! flex! flex-col! items-center! justify-center! gap-0.5! cursor-pointer! transition-colors! transition-opacity! duration-150! hover:bg-[#f7f7f7]! active:bg-[#dcdcdc]! disabled:opacity-35! disabled:pointer-events-none! max-md:w-10! max-md:h-[38px]! max-md:gap-px! max-md:[&_svg]:w-4! max-md:[&_svg]:h-4! bulk-action" data-action="feature" title="Feature">
                        {!! $icoFeature !!}
                        <span class="text-[10px]! text-[#666]! leading-none! max-md:hidden!">Feature</span>
                    </button>
                    <button type="button" class="w-[52px]! h-[44px]! border! border-solid! border-[#bbb]! bg-white! flex! flex-col! items-center! justify-center! gap-0.5! cursor-pointer! transition-colors! transition-opacity! duration-150! hover:bg-[#f7f7f7]! active:bg-[#dcdcdc]! disabled:opacity-35! disabled:pointer-events-none! max-md:w-10! max-md:h-[38px]! max-md:gap-px! max-md:[&_svg]:w-4! max-md:[&_svg]:h-4! bulk-action" data-action="unfeature" title="Unfeature">
                        {!! $icoFeature !!}
                        <span class="text-[10px]! text-[#666]! leading-none! max-md:hidden!">Unfeature</span>
                    </button>
                </div>

                <a href="{{ route('admin.petitions', request()->except('select')) }}"
                    title="Clear selection" class="w-[52px]! h-[44px]! border! border-solid! border-[#bbb]! bg-white! flex! flex-col! items-center! justify-center! gap-0.5! cursor-pointer! transition-colors! transition-opacity! duration-150! hover:bg-[#f7f7f7]! active:bg-[#dcdcdc]! disabled:opacity-35! disabled:pointer-events-none! max-md:w-10! max-md:h-[38px]! max-md:gap-px! max-md:[&_svg]:w-4! max-md:[&_svg]:h-4! no-underline! text-[#666]!">
                    {!! $icoX !!}
                    <span class="text-[10px]! text-[#666]! leading-none! max-md:hidden!">Clear</span>
                </a>
            </div>
        </x-slot:footer>

    </x-admin.list-table-box>


    <x-admin.detail-panel title="petition" :closeRoute="route('admin.petitions', request()->except('select'))">
        @if ($selectedPetition)
            <form method="post" action="{{ route('admin.petitions.save') }}" enctype="multipart/form-data">
                @csrf

                <input type="hidden" name="id" value="{{ $selectedPetition->id }}">
                <input type="hidden" name="locale" value="{{ $locale }}">

                <div class="grid! grid-cols-[160px_1fr]! gap-3! items-center! py-1.5! [&_label]:font-bold! [&_label]:text-sm! max-md:grid-cols-1! max-md:gap-1.5! max-md:[&_label]:text-xs!">
                    <label>active</label>
                    <input type="checkbox" name="is_active" value="1"
                        {{ $selectedPetition->is_active ? 'checked' : '' }}>
                </div>

                <div class="grid! grid-cols-[160px_1fr]! gap-3! items-center! py-1.5! [&_label]:font-bold! [&_label]:text-sm! max-md:grid-cols-1! max-md:gap-1.5! max-md:[&_label]:text-xs!">
                    <label>status</label>
                    <select class="w-full! box-border! border! border-solid! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white!" name="status">
                        <option value="draft" {{ $selectedPetition->status === 'draft' ? 'selected' : '' }}>draft</option>
                        <option value="published" {{ $selectedPetition->status === 'published' ? 'selected' : '' }}>
                            published
                        </option>
                    </select>
                </div>

                <div class="grid! grid-cols-[160px_1fr]! gap-3! items-center! py-1.5! [&_label]:font-bold! [&_label]:text-sm! max-md:grid-cols-1! max-md:gap-1.5! max-md:[&_label]:text-xs!">
                    <label>featured <span class="text-[11px]! text-[#888]!">(this locale)</span></label>
                    <input type="checkbox" name="is_featured" value="1"
                        {{ ($selectedTranslation->is_featured ?? false) ? 'checked' : '' }}>
                </div>

                <div class="grid! grid-cols-[160px_1fr]! gap-3! items-center! py-1.5! [&_label]:font-bold! [&_label]:text-sm! max-md:grid-cols-1! max-md:gap-1.5! max-md:[&_label]:text-xs!">
                    <label>owner (user id)</label>
                    <div>
                        <input class="w-full! box-border! border! border-solid! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white! max-w-[100px]!" type="number" name="user_id" value="{{ $selectedPetition->user_id }}"
                            placeholder="user ID">
                        @error('user_id') <span class="text-[#c00]! text-xs!">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="grid! grid-cols-[160px_1fr]! gap-3! items-center! py-1.5! [&_label]:font-bold! [&_label]:text-sm! max-md:grid-cols-1! max-md:gap-1.5! max-md:[&_label]:text-xs!">
                    <label>title</label>
                    <div>
                        <input class="w-full! box-border! border! border-solid! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white!" type="text" name="title" value="{{ $selectedTranslation->title ?? '' }}">
                        @error('title') <span class="text-[#c00]! text-xs!">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="grid! grid-cols-[160px_1fr]! gap-3! items-center! py-1.5! [&_label]:font-bold! [&_label]:text-sm! max-md:grid-cols-1! max-md:gap-1.5! max-md:[&_label]:text-xs!">
                    <label>slug</label>
                    <div>
                        <input class="w-full! box-border! border! border-solid! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white!" type="text" name="slug" value="{{ $selectedTranslation->slug ?? '' }}">
                        @error('slug') <span class="text-[#c00]! text-xs!">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="grid! grid-cols-[160px_1fr]! gap-3! items-center! py-1.5! [&_label]:font-bold! [&_label]:text-sm! max-md:grid-cols-1! max-md:gap-1.5! max-md:[&_label]:text-xs!">
                    <label>signature goal</label>
                    <div>
                        <input class="w-full! box-border! border! border-solid! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white!" type="number" name="goal_signatures"
                            value="{{ $selectedPetition->goal_signatures }}">
                        @error('goal_signatures') <span class="text-[#c00]! text-xs!">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="grid! grid-cols-[160px_1fr]! gap-3! items-center! py-1.5! [&_label]:font-bold! [&_label]:text-sm! max-md:grid-cols-1! max-md:gap-1.5! max-md:[&_label]:text-xs!">
                    <label>category</label>
                    <div>
                        <select class="w-full! box-border! border! border-solid! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white!" name="category_id">
                            <option value="">—</option>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->id }}"
                                    {{ (int) ($selectedPetition->category_id ?? 0) === (int) $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name ?? ('#' . $cat->id) }}
                                </option>
                            @endforeach
                        </select>
                        @error('category_id') <span class="text-[#c00]! text-xs!">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="grid! grid-cols-[160px_1fr]! gap-3! items-center! py-1.5! [&_label]:font-bold! [&_label]:text-sm! max-md:grid-cols-1! max-md:gap-1.5! max-md:[&_label]:text-xs!">
                    <label>tags</label>
                    <div>
                        <input class="w-full! box-border! border! border-solid! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white!" type="text" name="tags" value="{{ $selectedPetition->tags ?? '' }}"
                            placeholder="tag1, tag2">
                        @error('tags') <span class="text-[#c00]! text-xs!">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="grid! grid-cols-[160px_1fr]! gap-3! items-center! py-1.5! [&_label]:font-bold! [&_label]:text-sm! max-md:grid-cols-1! max-md:gap-1.5! max-md:[&_label]:text-xs!">
                    <label>youtube</label>
                    <div>
                        <input class="w-full! box-border! border! border-solid! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white!" type="text" name="youtube_url"
                            value="{{ $selectedPetition->youtube_url ?? '' }}">
                        @error('youtube_url') <span class="text-[#c00]! text-xs!">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="grid! grid-cols-[160px_1fr]! gap-3! items-center! py-1.5! [&_label]:font-bold! [&_label]:text-sm! max-md:grid-cols-1! max-md:gap-1.5! max-md:[&_label]:text-xs!">
                    <label>target</label>
                    <div>
                        <input class="w-full! box-border! border! border-solid! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white!" type="text" name="target" value="{{ $selectedPetition->target ?? '' }}">
                        @error('target') <span class="text-[#c00]! text-xs!">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="grid! grid-cols-[160px_1fr]! gap-3! items-center! py-1.5! [&_label]:font-bold! [&_label]:text-sm! max-md:grid-cols-1! max-md:gap-1.5! max-md:[&_label]:text-xs!">
                    <label>community</label>
                    <div>
                        <input class="w-full! box-border! border! border-solid! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white!" type="text" name="community"
                            value="{{ $selectedPetition->community ?? '' }}">
                        @error('community') <span class="text-[#c00]! text-xs!">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="grid! grid-cols-[160px_1fr]! gap-3! items-center! py-1.5! [&_label]:font-bold! [&_label]:text-sm! max-md:grid-cols-1! max-md:gap-1.5! max-md:[&_label]:text-xs!">
                    <label>community url</label>
                    <div>
                        <input class="w-full! box-border! border! border-solid! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white!" type="text" name="community_url"
                            value="{{ $selectedPetition->community_url ?? '' }}" placeholder="https://">
                        @error('community_url') <span class="text-[#c00]! text-xs!">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="grid! grid-cols-[160px_1fr]! gap-3! items-center! py-1.5! [&_label]:font-bold! [&_label]:text-sm! max-md:grid-cols-1! max-md:gap-1.5! max-md:[&_label]:text-xs!">
                    <label>location</label>
                    <div>
                        <input class="w-full! box-border! border! border-solid! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white!" type="text" name="city" value="{{ $selectedPetition->city ?? '' }}">
                        @error('city') <span class="text-[#c00]! text-xs!">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="grid! grid-cols-[160px_1fr]! gap-3! items-center! py-1.5! [&_label]:font-bold! [&_label]:text-sm! max-md:grid-cols-1! max-md:gap-1.5! max-md:[&_label]:text-xs!">
                    <label>date created</label>
                    <div>
                        <input class="w-full! box-border! border! border-solid! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white!" type="datetime-local" name="created_at"
                            value="{{ $selectedPetition->created_at ? \Illuminate\Support\Str::of($selectedPetition->created_at)->replace(' ', 'T')->limit(16, '') : '' }}">
                        @error('created_at') <span class="text-[#c00]! text-xs!">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="grid! grid-cols-[160px_1fr]! gap-3! items-center! py-1.5! [&_label]:font-bold! [&_label]:text-sm! max-md:grid-cols-1! max-md:gap-1.5! max-md:[&_label]:text-xs!">
                    <label>text</label>
                    <textarea class="w-full! box-border! border! border-solid! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white!" name="text" rows="8">{{ $selectedTranslation->description ?? '' }}</textarea>
                </div>

                {{-- Image management --}}
                <div class="flex! flex-col! items-start! gap-1.5! py-1.5! [&_label]:font-bold! [&_label]:text-sm! max-md:[&_label]:text-xs!">
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
                        <img src="{{ $imgSrc }}" alt="{{ $selectedTranslation->title ?? 'Cover image' }}" class="max-w-[220px]! max-h-[120px]! rounded! object-cover!">
                        <label class="text-[0.85em]!">
                            <input type="checkbox" name="remove_image" value="1"> remove image
                        </label>
                    @else
                        <span class="text-[#999]! text-[0.85em]!">no image</span>
                    @endif
                    <input class="w-full! box-border! border! border-solid! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white! mt-1!" type="file" name="cover_image" accept="image/*">
                    <span class="text-[#888]! text-[0.8em]!">upload new image to replace current (max 4 MB)</span>
                </div>

                <div class="flex! justify-end!">
                    <button class="border! border-solid! border-[#7d7d7d]! bg-[#e6e6e6]! px-2.5! py-1! rounded-[3px]! cursor-pointer! hover:bg-[#dcdcdc]! max-md:text-xs! max-md:px-3! max-md:py-1.5!" type="submit">save</button>
                </div>

            </form>
        @else
            <div class="text-[#777]!">select a petition to edit</div>
        @endif
    </x-admin.detail-panel>

@endsection


@include('admin.partials.bulk-js', [
    'actionRoute' => route('admin.petitions.bulkAction'),
    'noun' => 'petitions',
    'emptyMsg' => 'No petitions selected',
])
