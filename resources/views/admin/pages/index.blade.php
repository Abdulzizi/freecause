@extends('admin.layouts.app')

@section('title', 'Pages')

@section('content')

    @php
        $icoLink = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none">
<path d="M10 13a5 5 0 0 1 0-7l2-2a5 5 0 0 1 7 7l-1 1" stroke="#444" stroke-width="2"/>
<path d="M14 11a5 5 0 0 1 0 7l-2 2a5 5 0 0 1-7-7l1-1" stroke="#444" stroke-width="2"/>
</svg>';
    @endphp


    <x-admin.page-header title="pages" :count="$approxTotal" />


    @php
        $activeFilterCount = collect($filters)->filter(fn($v) => $v !== '' && $v !== null)->count();
    @endphp

    <x-admin.filter-box title="filter pages" :action="route('admin.pages')" :reset="route('admin.pages')" :activeCount="$activeFilterCount">

        <input class="fc-filter-field" name="id" placeholder="ID" value="{{ $filters['id'] ?? '' }}">

        <input class="fc-filter-field" name="title" placeholder="Title" value="{{ $filters['title'] ?? '' }}">

        <select data-admin-autosubmit class="fc-filter-field" name="locale">
            @foreach ($locales as $k => $label)
                <option value="{{ $k }}" {{ ($filters['locale'] ?? '') === $k ? 'selected' : '' }}>
                    {{ $label }}
                </option>
            @endforeach
        </select>

    </x-admin.filter-box>


    <x-admin.data-table emptyText="no pages found" :p="$pages" :emptyClearRoute="route('admin.pages')">

        <x-slot:thead>
            <tr class="border-b! border-border!">
                <th width="70">ID</th>
                <th width="26"></th>
                <th width="26">P</th>
                <th width="26" data-hide-mobile></th>
                <th class="text-start!">Title</th>
                <th data-hide-mobile>Slug</th>
                <th class="w-[90px]!" data-hide-mobile>Locale</th>
            </tr>
        </x-slot:thead>

        <x-slot:tbody>
            @foreach ($pages as $p)
                <tr class="border-b! border-border! {{ $p->published ? '' : 'fc-row-muted' }}" data-admin-row-hover
                    data-detail-row data-detail-href="{{ route('admin.pages', array_merge(request()->query(), ['select' => $p->page_id, 'locale' => $p->locale])) }}">

                    <td class="text-center!">
                        <a
                            href="{{ route('admin.pages', array_merge(request()->query(), ['select' => $p->page_id, 'locale' => $p->locale])) }}">
                            {{ $p->page_id }}
                        </a>
                    </td>

                    <td>
                        @if (admin_can('pages', 'edit') || admin_can('pages', 'delete'))
                            <input type="checkbox" class="bulk-checkbox" value="{{ $p->page_id }}" aria-label="Select page {{ $p->page_id }}">
                        @endif
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

                    <td>
                        {{ $p->title }}
                        @if (! $p->published)
                            <x-blatui.badge tone="warning" class="ml-1! align-middle!">unpublished</x-blatui.badge>
                        @endif
                    </td>
                    <td data-hide-mobile class="text-center!">{{ $p->slug }}</td>
                    <td data-hide-mobile class="text-center!">{{ $p->locale }}</td>

                </tr>
            @endforeach
        </x-slot:tbody>


        <x-slot:footer>
            <div class="flex! gap-2! items-center! flex-wrap!">
                @if (admin_can('pages', 'edit'))
                    <div class="flex! gap-2! items-center!">
                        <x-blatui.button variant="outline" size="sm" type="button" class="bulk-action" data-action="publish" title="Publish">
                            <x-lucide-circle-check aria-hidden="true" />
                            <span class="max-md:hidden!">Publish</span>
                        </x-blatui.button>

                        <x-blatui.button variant="outline" size="sm" type="button" class="bulk-action" data-action="unpublish" title="Unpublish">
                            <x-lucide-circle-x aria-hidden="true" />
                            <span class="max-md:hidden!">Unpublish</span>
                        </x-blatui.button>
                    </div>
                @endif

                @if (admin_can('pages', 'delete'))
                    <x-blatui.button variant="destructive" size="sm" type="button" class="bulk-action" data-action="delete" title="Delete">
                        <x-lucide-trash-2 aria-hidden="true" />
                        <span class="max-md:hidden!">Delete</span>
                    </x-blatui.button>
                @endif

                <x-blatui.button variant="outline" size="sm" :href="route('admin.pages', request()->except('select'))" title="Clear selection">
                    <x-lucide-x aria-hidden="true" />
                    <span class="max-md:hidden!">Clear</span>
                </x-blatui.button>
            </div>
        </x-slot:footer>

    </x-admin.data-table>


    <x-admin.detail-panel title="page" :closeRoute="route('admin.pages', request()->except('select'))">

        @if ($selectedPage)
            @if (admin_can('pages', 'edit'))
            <form method="POST" action="{{ route('admin.pages.save') }}" data-turbo="false">
                @csrf

                <input type="hidden" name="page_id" value="{{ $selectedPage->page_id }}">
                <input type="hidden" name="locale" value="{{ $selectedPage->locale }}">

                <div class="grid! grid-cols-1! gap-1.5! items-start! py-1.5! md:grid-cols-[160px_1fr]! md:gap-3! md:items-center!">
                    <label class="font-bold! text-xs! md:text-sm!" for="page-published">published</label>
                    <span class="fc-switch">
                        <input id="page-published" type="checkbox" name="published" value="1" {{ $selectedPage->published ? 'checked' : '' }}>
                        <span class="fc-switch__track" aria-hidden="true"></span>
                    </span>
                </div>

                <x-admin.form-field label="title">
                    <x-ui.input name="title" data-slug-source :value="$selectedPage->title" class="w-full! box-border! border! border-border! rounded! px-2! py-1.5! text-sm! bg-white!" />
                </x-admin.form-field>

                <x-admin.form-field label="slug" :error="$errors->first('slug')">
                    <x-ui.input name="slug" data-slug-target :value="$selectedPage->slug" class="w-full! box-border! border! border-border! rounded! px-2! py-1.5! text-sm! bg-white!" />
                </x-admin.form-field>

                @php
                    $existingSlugs = \Illuminate\Support\Facades\DB::table('page_translations')
                        ->pluck('slug')
                        ->filter()
                        ->values()
                        ->all();
                @endphp
                <script type="application/json" id="existing-slugs">{!! json_encode($existingSlugs) !!}</script>

                <x-admin.form-field label="content">
                    <textarea id="content" name="content" class="w-full! box-border! border! border-border! rounded! px-2! py-1.5! bg-white! min-h-[140px]! resize-y! font-mono! text-[13px]! fc-editor" data-ckeditor="rich" rows="12">{{ $selectedPage->content }}</textarea>
                </x-admin.form-field>

                <div class="flex! justify-end! fc-drawer-savebar">
                    <x-blatui.button type="submit">save</x-blatui.button>
                </div>

            </form>
            @else
                <p class="text-sm! text-muted!">View-only access</p>
                <dl class="grid! grid-cols-[120px_1fr]! gap-2! text-sm! max-md:grid-cols-[90px_1fr]!">
                    <dt class="font-bold!">Title</dt><dd>{{ $selectedPage->title }}</dd>
                    <dt class="font-bold!">Slug</dt><dd>{{ $selectedPage->slug }}</dd>
                    <dt class="font-bold!">Locale</dt><dd>{{ $selectedPage->locale }}</dd>
                    <dt class="font-bold!">Published</dt><dd>{{ $selectedPage->published ? 'Yes' : 'No' }}</dd>
                </dl>
                <div class="mt-3!">
                    <h3 class="font-bold! text-sm! mb-1!">Content</h3>
                    <div class="whitespace-pre-wrap! break-words! text-sm!">{{ $selectedPage->content }}</div>
                </div>
            @endif
        @else
            <x-admin.empty-state message="select a page to edit" class="text-muted!" />
        @endif

    </x-admin.detail-panel>


    @include('admin.partials.bulk-js', [
        'actionRoute' => route('admin.pages.bulkAction'),
        'noun' => 'pages',
        'emptyMsg' => 'No pages selected',
    ])

@endsection
