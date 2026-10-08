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

    <x-admin.page-header title="petitions" :count="$approxTotal">
        @if (admin_can('petitions', 'edit'))
            <button type="button" class="fc-abtn" data-petition-create-toggle aria-haspopup="dialog">
                new petition
            </button>
        @endif
    </x-admin.page-header>

    @if (admin_can('petitions', 'edit'))
        <x-admin.modal id="petition-create-modal" title="create petition">
            <form data-petition-create-form method="post" action="{{ route('admin.petitions.create') }}" style="display: none;">
                @csrf

                <x-admin.form-field label="title">
                    <input name="title" required maxlength="255" class="fc-filter-field" placeholder="petition title">
                </x-admin.form-field>

                <x-admin.form-field label="locale">
                    <select name="locale" class="fc-filter-field">
                        @foreach ($localeOptions as $code => $label)
                            <option value="{{ $code }}" {{ $locale === $code ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </x-admin.form-field>

                <x-admin.form-field label="category">
                    <select name="category_id" class="fc-filter-field">
                        <option value="">—</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name ?? ('#'.$cat->id) }}</option>
                        @endforeach
                    </select>
                </x-admin.form-field>

                <x-admin.form-field label="goal">
                    <select name="goal_signatures" class="fc-filter-field">
                        @foreach ([50, 100, 1000, 5000, 10000, 50000, 100000, 500000, 1000000, 10000000] as $goalOption)
                            <option value="{{ $goalOption }}" {{ $goalOption === 1000 ? 'selected' : '' }}>
                                {{ number_format($goalOption) }}
                            </option>
                        @endforeach
                    </select>
                </x-admin.form-field>

                <div class="fc-filter-actions">
                    <button type="submit" class="fc-abtn">create</button>
                    <button type="button" class="fc-abtn fc-abtn--neutral" data-admin-modal-close>cancel</button>
                </div>
            </form>
        </x-admin.modal>
    @endif

    @php
        $activeFilterCount = collect($filters)->filter(fn($v) => $v !== '' && $v !== null && $v !== 'locale')->count();
    @endphp

    <x-admin.filter-box title="filter petitions" :action="route('admin.petitions')" :reset="route('admin.petitions')" :activeCount="$activeFilterCount">
        <input type="hidden" name="locale" value="{{ $locale }}">

        <input class="fc-filter-field" name="id" placeholder="ID" value="{{ $filters['id'] ?? '' }}">

        <input class="fc-filter-field" name="title" placeholder="Title" value="{{ $filters['title'] ?? '' }}">

        <input class="fc-filter-field" name="owner" placeholder="Owner ID" value="{{ $filters['owner'] ?? '' }}">

        <select data-admin-autosubmit class="fc-filter-field" name="category">
            <option value="">category...</option>
            @foreach ($categories as $cat)
                <option value="{{ $cat->id }}" {{ (string) ($filters['category'] ?? '') === (string) $cat->id ? 'selected' : '' }}>
                    {{ $cat->name ?? ('#' . $cat->id) }}
                </option>
            @endforeach
        </select>

        <select data-admin-autosubmit class="fc-filter-field" name="status">
            <option value="">any status</option>
            <option value="published" {{ ($filters['status'] ?? '') === 'published' ? 'selected' : '' }}>published</option>
            <option value="draft" {{ ($filters['status'] ?? '') === 'draft' ? 'selected' : '' }}>draft</option>
        </select>

        <select data-admin-autosubmit class="fc-filter-field" name="active">
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
            <select data-admin-autosubmit class="fc-filter-field" name="missing_locale">
                <option value="">missing locale...</option>
                @foreach ($activeLanguages as $lang)
                    <option value="{{ $lang }}" {{ ($filters['missing_locale'] ?? '') === $lang ? 'selected' : '' }}>
                        missing: {{ $lang }}
                    </option>
                @endforeach
            </select>
        @endif
    </x-admin.filter-box>


    <x-admin.data-table emptyText="no petitions found" :p="$petitions" :emptyClearRoute="route('admin.petitions')" :bulk="[]">

        <x-slot:thead>
            <tr class="border-b! border-solid! border-[#ccc]!" data-admin-row-hover>
                <th>ID</th>
                <th width="26"></th>
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
                <tr class="border-b! border-solid! border-[#eee]! {{ ($p->status === 'draft' || ! $p->is_active) ? 'fc-row-muted' : '' }}" data-admin-row-hover data-detail-row data-detail-href="{{ route('admin.petitions', array_merge(request()->query(), ['select' => $p->id])) }}">

                    <td>
                        <a href="{{ route('admin.petitions', array_merge(request()->query(), ['select' => $p->id])) }}">
                            {{ $p->id }}
                        </a>
                    </td>

                    <td>
                        @if (admin_can('petitions', 'edit'))
                            <input type="checkbox" class="bulk-checkbox" value="{{ $p->id }}" aria-label="Select petition {{ $p->id }}">
                        @endif
                    </td>

                    {{-- ACTIVE --}}
                    <td>
                        @if (admin_can('petitions', 'edit'))
                        <button type="button" class="fc-abtn {{ $p->is_active ? 'fc-abtn--success' : 'fc-abtn--danger' }}"
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
                        <button type="button" class="fc-abtn {{ $p->is_featured ? 'fc-abtn--success' : '' }}"
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
                    <td>{{ $p->title }}@if ($p->status === 'draft') <span class="ml-1! inline-block! bg-[#e0e0e0]! text-[#555]! text-[10px]! uppercase! tracking-wide! rounded-[3px]! px-[4px]! py-px!">draft</span>@endif</td>
                    <td data-hide-mobile>
                        @php
                            $petitionLocales = $p->translation_locales ? explode(',', $p->translation_locales) : [];
                        @endphp
                        @foreach ($activeLanguages as $lang)
                            @if (in_array($lang, $petitionLocales))
                                <span class="inline-block! bg-[#d4edda]! text-[#155724]! rounded-[3px]! px-[5px]! py-px!  m-px!">{{ $lang }}</span>
                            @else
                                <span class="inline-block! bg-[#f8d7da]! text-[#721c24]! rounded-[3px]! px-[5px]! py-px!  m-px!">{{ $lang }}</span>
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
                        class="fc-abtn fc-abtn--danger">
                        {!! $icoBan !!}
                        <span class="max-md:hidden!">Ban</span>
                    </button>
                @endif
                @if (admin_can('petitions', 'edit'))
                    <div class="flex! gap-1! items-center!">
                        <button type="button" class="fc-abtn bulk-action" data-action="publish" title="Publish">
                            {!! $icoPublish !!}
                            <span class="max-md:hidden!">Publish</span>
                        </button>
                        <button type="button" class="fc-abtn bulk-action" data-action="unpublish" title="Unpublish">
                            {!! $icoUnpublish !!}
                            <span class="max-md:hidden!">Unpublish</span>
                        </button>
                    </div>

                    <div class="flex! gap-1! items-center!">
                        <button type="button" class="fc-abtn bulk-action" data-action="activate" title="Activate">
                            {!! $icoPublish !!}
                            <span class="max-md:hidden!">Activate</span>
                        </button>
                        <button type="button" class="fc-abtn bulk-action" data-action="deactivate" title="Deactivate">
                            {!! $icoUnpublish !!}
                            <span class="max-md:hidden!">Deactivate</span>
                        </button>
                    </div>

                    <div class="flex! gap-1! items-center!">
                        <button type="button" class="fc-abtn bulk-action" data-action="feature" title="Feature">
                            {!! $icoFeature !!}
                            <span class="max-md:hidden!">Feature</span>
                        </button>
                        <button type="button" class="fc-abtn bulk-action" data-action="unfeature" title="Unfeature">
                            {!! $icoFeature !!}
                            <span class="max-md:hidden!">Unfeature</span>
                        </button>
                    </div>
                @endif

                <a href="{{ route('admin.petitions', request()->except('select')) }}"
                    title="Clear selection" class="fc-abtn fc-abtn--neutral">
                    {!! $icoX !!}
                    <span class="max-md:hidden!">Clear</span>
                </a>
            </div>
        </x-slot:footer>

    </x-admin.data-table>


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
                    <select id="petition-translation-locale" name="locale" class="fc-filter-field py-1! text-xs!">
                        @foreach ($localeOptions as $localeCode => $localeLabel)
                            <option value="{{ $localeCode }}" {{ $locale === $localeCode ? 'selected' : '' }}>
                                {{ $localeLabel }}
                            </option>
                        @endforeach
                    </select>
                    <button type="submit" class="fc-abtn">
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
                <button type="button"
                    data-reconcile-one
                    data-url="{{ route('admin.petitions.reconcile') }}"
                    data-id="{{ $selectedPetition->id }}" class="fc-abtn">
                    Reconcile signature count for this petition
                </button>

                @if ($missingTranslationCount > 0)
                    <button type="button"
                        data-translate-missing
                        data-url="{{ route('admin.petitions.translateMissing') }}"
                        data-id="{{ $selectedPetition->id }}"
                        data-source="{{ $translationSource?->locale ?? $locale }}"
                        data-count="{{ $missingTranslationCount }}" class="fc-abtn">
                        translate to missing locales ({{ $missingTranslationCount }})
                    </button>
                @endif
            @endif

            @if (admin_can('petitions', 'edit'))
                <form method="post" action="{{ route('admin.petitions.save') }}" enctype="multipart/form-data">
                @csrf

                <input type="hidden" name="id" value="{{ $selectedPetition->id }}">
                <input type="hidden" name="locale" value="{{ $locale }}">

                <div class="grid! grid-cols-[160px_1fr]! gap-3! items-center! py-1.5! [&_label]:font-bold! [&_label]:text-sm! max-md:grid-cols-1! max-md:gap-1.5! max-md:[&_label]:text-xs!">
                    <label for="petition-active">active</label>
                    <span class="fc-switch">
                        <input id="petition-active" type="checkbox" name="is_active" value="1"
                            {{ $selectedPetition->is_active ? 'checked' : '' }}>
                        <span class="fc-switch__track" aria-hidden="true"></span>
                    </span>
                </div>

                <x-admin.form-field label="status">
                    <select class="fc-filter-field" name="status">
                        <option value="draft" {{ $selectedPetition->status === 'draft' ? 'selected' : '' }}>draft</option>
                        <option value="published" {{ $selectedPetition->status === 'published' ? 'selected' : '' }}>
                            published
                        </option>
                    </select>
                </x-admin.form-field>

                <div class="grid! grid-cols-[160px_1fr]! gap-3! items-center! py-1.5! [&_label]:font-bold! [&_label]:text-sm! max-md:grid-cols-1! max-md:gap-1.5! max-md:[&_label]:text-xs!">
                    <label for="petition-featured">featured <span class="text-[#888]!">(this locale)</span></label>
                    <span class="fc-switch">
                        <input id="petition-featured" type="checkbox" name="is_featured" value="1"
                            {{ ($selectedTranslation->is_featured ?? $selectedDefaultTranslation?->is_featured ?? false) ? 'checked' : '' }}>
                        <span class="fc-switch__track" aria-hidden="true"></span>
                    </span>
                </div>

                <x-admin.form-field label="owner (user id)">
                    <x-ui.input name="user_id" type="number" :value="$selectedPetition->user_id" class="fc-filter-field" placeholder="user ID" />
                    @error('user_id') <span class="text-[#c00]! text-xs!">{{ $message }}</span> @enderror
                </x-admin.form-field>

                <x-admin.form-field label="title">
<x-ui.input name="title" data-slug-source :value="$translationSource->title ?? ''" class="w-full! box-border! border! border-solid! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white!" />
                        @error('title') <span class="text-[#c00]! text-xs!">{{ $message }}</span> @enderror
</x-admin.form-field>

                <x-admin.form-field label="slug">
<x-ui.input name="slug" data-slug-target :value="$translationSource->slug ?? ''" class="w-full! box-border! border! border-solid! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white!" />
                        @error('slug') <span class="text-[#c00]! text-xs!">{{ $message }}</span> @enderror
</x-admin.form-field>

                @if ($selectedPetition)
                    @php
                        $existingSlugs = \Illuminate\Support\Facades\DB::table('petition_translations')
                            ->where('petition_id', $selectedPetition->id)
                            ->where('locale', '!=', $locale)
                            ->pluck('slug')
                            ->filter()
                            ->values()
                            ->all();
                    @endphp
                    <script type="application/json" id="existing-slugs">{!! json_encode($existingSlugs) !!}</script>
                @endif

                <x-admin.form-field label="signature goal">
<select class="fc-filter-field" name="goal_signatures">
                            @foreach ([50, 100, 1000, 5000, 10000, 50000, 100000, 500000, 1000000, 10000000] as $goalOption)
                                <option value="{{ $goalOption }}" {{ (int) $selectedPetition->goal_signatures === $goalOption ? 'selected' : '' }}>
                                    {{ number_format($goalOption) }}
                                </option>
                            @endforeach
                        </select>
                        @error('goal_signatures') <span class="text-[#c00]! text-xs!">{{ $message }}</span> @enderror
</x-admin.form-field>

                <x-admin.form-field label="category">
<select class="fc-filter-field" name="category_id">
                            <option value="">—</option>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->id }}"
                                    {{ (int) ($selectedPetition->category_id ?? 0) === (int) $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name ?? ('#' . $cat->id) }}
                                </option>
                            @endforeach
                        </select>
                        @error('category_id') <span class="text-[#c00]! text-xs!">{{ $message }}</span> @enderror
</x-admin.form-field>

                <x-admin.form-field label="tags">
<x-ui.input name="tags" :value="$selectedPetition->tags ?? ''" placeholder="tag1, tag2" class="w-full! box-border! border! border-solid! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white!" />
                        @error('tags') <span class="text-[#c00]! text-xs!">{{ $message }}</span> @enderror
</x-admin.form-field>

                <x-admin.form-field label="youtube">
<x-ui.input name="youtube_url" :value="$selectedPetition->youtube_url ?? ''" class="w-full! box-border! border! border-solid! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white!" />
                        @error('youtube_url') <span class="text-[#c00]! text-xs!">{{ $message }}</span> @enderror
</x-admin.form-field>

                <x-admin.form-field label="target">
<x-ui.input name="target" :value="$selectedPetition->target ?? ''" class="w-full! box-border! border! border-solid! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white!" />
                        @error('target') <span class="text-[#c00]! text-xs!">{{ $message }}</span> @enderror
</x-admin.form-field>

                <x-admin.form-field label="community">
<x-ui.input name="community" :value="$selectedPetition->community ?? ''" class="w-full! box-border! border! border-solid! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white!" />
                        @error('community') <span class="text-[#c00]! text-xs!">{{ $message }}</span> @enderror
</x-admin.form-field>

                <x-admin.form-field label="community url">
<x-ui.input name="community_url" :value="$selectedPetition->community_url ?? ''" placeholder="https://" class="w-full! box-border! border! border-solid! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white!" />
                        @error('community_url') <span class="text-[#c00]! text-xs!">{{ $message }}</span> @enderror
</x-admin.form-field>

                <x-admin.form-field label="location">
<x-ui.input name="city" :value="$selectedPetition->city ?? ''" class="w-full! box-border! border! border-solid! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white!" />
                        @error('city') <span class="text-[#c00]! text-xs!">{{ $message }}</span> @enderror
</x-admin.form-field>

                <x-admin.form-field label="date created">
<x-ui.input name="created_at" type="datetime-local" :value="$selectedPetition->created_at ? \Illuminate\Support\Str::of($selectedPetition->created_at)->replace(' ', 'T')->limit(16, '') : ''" class="w-full! box-border! border! border-solid! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white!" />
                        @error('created_at') <span class="text-[#c00]! text-xs!">{{ $message }}</span> @enderror
</x-admin.form-field>

                <x-admin.form-field label="text">
<textarea class="w-full! box-border! border! border-solid! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white! fc-editor" data-ckeditor="rich" name="text" rows="8">{{ $translationSource->description ?? '' }}</textarea>
</x-admin.form-field>

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

                <div class="flex! justify-end! fc-drawer-savebar">
                    <button type="submit" class="fc-abtn">save</button>
                </div>

                </form>
            @else
                <p class="text-sm! text-[#777]!">View-only access</p>
                <dl class="grid! grid-cols-[120px_1fr]! gap-2! text-sm! max-md:grid-cols-[90px_1fr]!">
                    <dt class="font-bold!">Title</dt><dd>{{ $translationSource->title ?? '—' }}</dd>
                    <dt class="font-bold!">Status</dt><dd>{{ $selectedPetition->status }}</dd>
                    <dt class="font-bold!">Active</dt><dd>{{ $selectedPetition->is_active ? 'Yes' : 'No' }}</dd>
                    <dt class="font-bold!">Signatures</dt><dd>{{ $selectedPetition->signature_count }} / {{ $selectedPetition->goal_signatures }}</dd>
                    <dt class="font-bold!">Locale</dt><dd>{{ $localeOptions[$locale] ?? $locale }}</dd>
                    <dt class="font-bold!">Slug</dt><dd>{{ $translationSource->slug ?? '—' }}</dd>
                </dl>
            @endif
        @else
            <x-admin.empty-state message="select a petition to edit" class="text-[#777]!" />
        @endif
    </x-admin.detail-panel>

@endsection


@include('admin.partials.bulk-js', [
    'actionRoute' => route('admin.petitions.bulkAction'),
    'noun' => 'petitions',
    'emptyMsg' => 'No petitions selected',
])
