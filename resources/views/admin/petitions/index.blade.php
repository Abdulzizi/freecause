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

        $icoLink = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none">
        <path d="M10 13a5 5 0 0 1 0-7l2-2a5 5 0 0 1 7 7l-1 1" stroke="#444" stroke-width="2"/>
        <path d="M14 11a5 5 0 0 1 0 7l-2 2a5 5 0 0 1-7-7l1-1" stroke="#444" stroke-width="2"/>
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


    <x-admin.list-table-box emptyText="no petitions found" :p="$petitions" :bulk="[]">

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
                <th width="26" data-hide-mobile></th>
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
                        @if (admin_can('petitions', 'edit'))
                        <button type="button"
                            class="px-1.5! py-0.5! rounded! border! text-[10px]! cursor-pointer! {{ $p->is_active ? 'border-[#b7d7b7]! bg-[#e8f5e8]! text-[#2f6f2f]!' : 'border-[#e2baba]! bg-[#fff0f0]! text-[#900]!' }}"
                            data-petition-toggle data-url="{{ route('admin.petitions.bulkAction') }}"
                            data-id="{{ $p->id }}" data-action="{{ $p->is_active ? 'deactivate' : 'activate' }}"
                            data-kind="active" aria-pressed="{{ $p->is_active ? 'true' : 'false' }}">
                            {{ $p->is_active ? 'Active' : 'Inactive' }}
                        </button>
                        @else
                            <span class="text-xs! {{ $p->is_active ? 'text-[#2f6f2f]!' : 'text-[#900]!' }}">{{ $p->is_active ? 'Active' : 'Inactive' }}</span>
                        @endif
                    </td>

                    {{-- PUBLISHED --}}
                    <td>
                        {!! $p->status === 'published'
                            ? '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#5c8f3a" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>'
                            : '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#c00" stroke-width="2.5" stroke-linecap="round"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>' !!}
                    </td>

                    {{-- FEATURED --}}
                    <td>
                        @if (admin_can('petitions', 'edit'))
                        <button type="button"
                            class="px-1.5! py-0.5! rounded! border! text-[10px]! cursor-pointer! {{ $p->is_featured ? 'border-[#b7d7b7]! bg-[#e8f5e8]! text-[#2f6f2f]!' : 'border-[#e2baba]! bg-[#fff0f0]! text-[#900]!' }}"
                            data-petition-toggle data-url="{{ route('admin.petitions.bulkAction') }}"
                            data-id="{{ $p->id }}" data-action="{{ $p->is_featured ? 'unfeature' : 'feature' }}"
                            data-kind="featured" aria-pressed="{{ $p->is_featured ? 'true' : 'false' }}">
                            {{ $p->is_featured ? 'Featured' : 'Feature' }}
                        </button>
                        @else
                            <span class="text-xs! {{ $p->is_featured ? 'text-[#2f6f2f]!' : 'text-[#900]!' }}">{{ $p->is_featured ? 'Featured' : 'Not featured' }}</span>
                        @endif
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
                    <td data-hide-mobile class="text-center!">
                        @php $petitionLocales = $p->translation_locales ? explode(',', $p->translation_locales) : []; @endphp
                        @if (in_array($locale, $petitionLocales, true) && $p->slug)
                            <a href="{{ route('petition.show', ['locale' => $locale, 'slug' => $p->slug, 'id' => $p->id]) }}"
                                target="_blank" rel="noopener" title="View petition on site">{!! $icoLink !!}</a>
                        @endif
                    </td>

                </tr>
            @endforeach
        </x-slot:tbody>


        <x-slot:footer>
            <div class="flex! gap-2! items-center! flex-wrap! max-md:gap-1.5! max-md:w-full! max-md:justify-end!">
                @if (admin_can('petitions', 'edit'))
                    <button type="button" data-petition-bulk-ban
                        data-url="{{ route('admin.petitions.bulkAction') }}"
                        class="flex! flex-col! items-center! justify-center! gap-0.5! w-[52px]! h-[44px]! border! border-[#bbb]! bg-white! cursor-pointer! transition-colors! hover:bg-[#fff0f0]! text-[#900]! max-md:w-10! max-md:h-[38px]!">
                        {!! $icoBan !!}
                        <span class="text-[10px]! leading-none! max-md:hidden!">Ban</span>
                    </button>
                @endif
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
            @php
                $translationSource = $selectedTranslation ?? $selectedDefaultTranslation;
                $isCreatingTranslation = ! $selectedTranslation;
            @endphp

            <div class="mb-3! border-b! border-[#eee]! pb-2!">
                <form method="get" action="{{ route('admin.petitions') }}"
                    class="flex! items-center! gap-2! flex-wrap! mb-1!">
                    @foreach (request()->except('locale') as $key => $value)
                        @if (! is_array($value))
                            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                        @endif
                    @endforeach
                    <label for="petition-translation-locale" class="font-bold! text-xs!">Translation locale</label>
                    <select id="petition-translation-locale" name="locale"
                        class="border! border-[#bdbdbd]! rounded! px-2! py-1! text-xs! bg-white!">
                        @foreach ($localeOptions as $localeCode => $localeLabel)
                            <option value="{{ $localeCode }}" {{ $locale === $localeCode ? 'selected' : '' }}>
                                {{ $localeLabel }}
                            </option>
                        @endforeach
                    </select>
                    <button type="submit" class="border! border-[#bbb]! bg-white! px-2! py-1! rounded! text-xs! cursor-pointer!">
                        Open locale
                    </button>
                </form>
                @if ($isCreatingTranslation)
                    <div class="text-xs! text-[#8a6d1e]!">
                        Create translation{{ $selectedDefaultTranslation ? ' from ' . ($localeOptions[$selectedDefaultTranslation->locale] ?? $selectedDefaultTranslation->locale) : '' }}.
                    </div>
                @endif
                @if ($translationSource?->slug)
                    <a href="{{ route('petition.show', ['locale' => $translationSource->locale, 'slug' => $translationSource->slug, 'id' => $selectedPetition->id]) }}"
                        target="_blank" rel="noopener" class="text-xs! text-[#1a57c8]!">
                        View petition on site ({{ $localeOptions[$translationSource->locale] ?? $translationSource->locale }})
                    </a>
                @endif
            </div>

            @if (admin_can('petitions', 'edit'))
                <button type="button" class="mb-2! border! border-[#bbb]! bg-white! px-2! py-1! rounded! text-xs! cursor-pointer!"
                    data-reconcile-one
                    data-url="{{ route('admin.petitions.reconcile') }}"
                    data-id="{{ $selectedPetition->id }}">
                    Reconcile signature count for this petition
                </button>
            @endif

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
                        {{ ($selectedTranslation->is_featured ?? $selectedDefaultTranslation?->is_featured ?? false) ? 'checked' : '' }}>
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
                        <input class="w-full! box-border! border! border-solid! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white!" type="text" name="title" value="{{ $translationSource->title ?? '' }}">
                        @error('title') <span class="text-[#c00]! text-xs!">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="grid! grid-cols-[160px_1fr]! gap-3! items-center! py-1.5! [&_label]:font-bold! [&_label]:text-sm! max-md:grid-cols-1! max-md:gap-1.5! max-md:[&_label]:text-xs!">
                    <label>slug</label>
                    <div>
                        <input class="w-full! box-border! border! border-solid! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white!" type="text" name="slug" value="{{ $translationSource->slug ?? '' }}">
                        @error('slug') <span class="text-[#c00]! text-xs!">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="grid! grid-cols-[160px_1fr]! gap-3! items-center! py-1.5! [&_label]:font-bold! [&_label]:text-sm! max-md:grid-cols-1! max-md:gap-1.5! max-md:[&_label]:text-xs!">
                    <label>signature goal</label>
                    <div>
                        <select class="w-full! box-border! border! border-solid! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white!" name="goal_signatures">
                            @foreach ([50, 100, 1000, 5000, 10000, 50000, 100000, 500000, 1000000, 10000000] as $goalOption)
                                <option value="{{ $goalOption }}" {{ (int) $selectedPetition->goal_signatures === $goalOption ? 'selected' : '' }}>
                                    {{ number_format($goalOption) }}
                                </option>
                            @endforeach
                        </select>
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
                    <textarea class="w-full! box-border! border! border-solid! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white! fc-editor" data-ckeditor="rich" name="text" rows="8">{{ $translationSource->description ?? '' }}</textarea>
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
                        @php $wv = \App\Support\ImageVariants::urls($selectedPetition->cover_image); @endphp
                        @if ($wv)
                        <picture>
                            <source type="image/webp" srcset="{{ $wv['400'] }} 400w, {{ $wv['800'] }} 800w" sizes="240px">
                            <img src="{{ $imgSrc }}" alt="{{ $selectedTranslation->title ?? 'Cover image' }}" class="max-w-[220px]! max-h-[120px]! rounded! object-cover!" loading="lazy" decoding="async">
                        </picture>
                        @else
                        <img src="{{ $imgSrc }}" alt="{{ $selectedTranslation->title ?? 'Cover image' }}" class="max-w-[220px]! max-h-[120px]! rounded! object-cover!" loading="lazy" decoding="async">
                        @endif
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

@push('scripts')
    <script>
        document.addEventListener('click', async function (event) {
            const toggle = event.target.closest('[data-petition-toggle]');
            if (toggle) {
                const action = toggle.dataset.action;
                toggle.disabled = true;
                try {
                    const response = await fetch(toggle.dataset.url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                        },
                        body: JSON.stringify({ action, ids: [toggle.dataset.id] }),
                    });
                    const result = await response.json();
                    if (!response.ok || !result.ok) throw new Error(result.msg || 'Could not update petition.');

                    const enabled = ['activate', 'feature'].includes(action);
                    const isActive = toggle.dataset.kind === 'active';
                    toggle.dataset.action = isActive
                        ? (enabled ? 'deactivate' : 'activate')
                        : (enabled ? 'unfeature' : 'feature');
                    toggle.setAttribute('aria-pressed', enabled ? 'true' : 'false');
                    toggle.textContent = isActive
                        ? (enabled ? 'Active' : 'Inactive')
                        : (enabled ? 'Featured' : 'Feature');
                    toggle.classList.toggle('border-[#b7d7b7]!', enabled);
                    toggle.classList.toggle('bg-[#e8f5e8]!', enabled);
                    toggle.classList.toggle('text-[#2f6f2f]!', enabled);
                    toggle.classList.toggle('border-[#e2baba]!', !enabled);
                    toggle.classList.toggle('bg-[#fff0f0]!', !enabled);
                    toggle.classList.toggle('text-[#900]!', !enabled);
                    window.toast?.success?.('Petition updated.');
                } catch (error) {
                    window.toast?.error?.(error.message || 'Could not update petition.');
                } finally {
                    toggle.disabled = false;
                }
                return;
            }

            const ban = event.target.closest('[data-petition-bulk-ban]');
            if (ban) {
                const ids = Array.from(document.querySelectorAll('.bulk-checkbox:checked'))
                    .filter((checkbox) => !checkbox.disabled)
                    .map((checkbox) => checkbox.value);
                if (!ids.length) {
                    window.toast?.warning?.('Select at least one petition to ban.');
                    return;
                }
                const reason = window.prompt('Ban reason (optional):');
                if (reason === null) return;
                if (!window.confirm('Ban ' + ids.length + ' selected petition(s)? This will unpublish and deactivate them.')) return;

                ban.disabled = true;
                try {
                    const response = await fetch(ban.dataset.url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                        },
                        body: JSON.stringify({ action: 'ban', ids, reason: reason.trim() || null }),
                    });
                    const result = await response.json();
                    if (!response.ok || !result.ok) throw new Error(result.msg || 'Could not ban selected petitions.');
                    window.toast?.success?.('Selected petitions were unpublished and deactivated.');
                    window.setTimeout(() => window.location.reload(), 700);
                } catch (error) {
                    window.toast?.error?.(error.message || 'Could not ban selected petitions.');
                    ban.disabled = false;
                }
                return;
            }

            const reconcile = event.target.closest('[data-reconcile-one]');
            if (!reconcile) return;
            if (!window.confirm('Recalculate the confirmed signature count for this petition?')) return;
            reconcile.disabled = true;
            try {
                const response = await fetch(reconcile.dataset.url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    },
                    body: JSON.stringify({ id: reconcile.dataset.id }),
                });
                const result = await response.json();
                if (!response.ok || !result.ok) throw new Error(result.error || 'Could not reconcile petition count.');
                window.toast?.success?.('Signature count reconciled: ' + result.actual_count + '.');
            } catch (error) {
                window.toast?.error?.(error.message || 'Could not reconcile petition count.');
            } finally {
                reconcile.disabled = false;
            }
        });
    </script>
@endpush
