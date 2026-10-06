@extends('admin.layouts.app')

@section('title', 'Categories')

@section('content')

<x-admin.page-header title="categories" :count="$approxTotal" />

@if (session('success'))
    <div class="border! border-[#77c777]! bg-[#e9ffe9]! text-[#135f13]! px-2! py-1.5! rounded! mt-1.5! mb-2.5!">{{ session('success') }}</div>
@endif

@if ($errors->any())
    <div class="fc-error mb-3!">{{ $errors->first() }}</div>
@endif

{{-- ── NEW CATEGORY FORM ─────────────────────────────────────────────────── --}}
<div class="bg-[#f9f9f9]! border! border-[#ddd]! rounded! p-4! mb-[18px]!">
    <div class="font-bold! mb-2.5! text-[13px]! uppercase! tracking-[0.05em]!">
        + new category
    </div>
    <form method="post" action="{{ route('admin.categories.create') }}" class="flex! gap-2.5! items-end! flex-wrap!">
        @csrf
        <div>
            <label class="block! text-xs! text-[#555]! mb-1!">locale</label>
            <select class="fc-filter-field" name="locale">
                @foreach ($locales as $k => $label)
                    <option value="{{ $k }}" {{ $locale === $k ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex-1! min-w-[180px]!">
            <label class="block! text-xs! text-[#555]! mb-1!">name *</label>
            <input class="w-full! box-border! border! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white!" type="text" name="name" placeholder="Category name" required>
        </div>
        <div>
            <button type="submit" class="fc-abtn fc-abtn--success">create</button>
        </div>
    </form>
</div>

{{-- ── FILTER ────────────────────────────────────────────────────────────── --}}
@php
    $activeFilterCount = collect($filters)->filter(fn($v) => $v !== '' && $v !== null)->count();
@endphp

<x-admin.filter-box title="filter categories"
    :action="route('admin.categories')"
    :reset="route('admin.categories')"
    :activeCount="$activeFilterCount">

    <select data-admin-autosubmit class="fc-filter-field" name="locale">
        @foreach ($locales as $k => $label)
            <option value="{{ $k }}" {{ $locale === $k ? 'selected' : '' }}>
                {{ $label }}
            </option>
        @endforeach
    </select>

    <input class="fc-filter-field" name="id"
        placeholder="ID"
        value="{{ $filters['id'] ?? '' }}">

    <input class="fc-filter-field" name="name"
        placeholder="Name"
        value="{{ $filters['name'] ?? '' }}">

</x-admin.filter-box>

{{-- ── LIST ──────────────────────────────────────────────────────────────── --}}
<x-admin.data-table empty-text="no categories found, try clearing filters"
    :p="$categories">

    <x-slot:thead>
        <tr class="border-b! border-[#ccc]!">
            <th class="w-[90px]!">ID</th>
            <th class="w-[110px]!">Locale</th>
            <th>Name</th>
            <th>Slug</th>
        </tr>
    </x-slot:thead>

    <x-slot:tbody>
        @foreach ($categories as $c)
            @php
                $missing = empty($c->name);
            @endphp

            <tr class="border-b! border-[#eee]!" data-admin-row-hover data-detail-row data-detail-href="{{ route('admin.categories', array_merge(request()->query(), ['select' => $c->id])) }}">
                <td>
                    <a href="{{ route('admin.categories', array_merge(request()->query(), ['select' => $c->id])) }}">
                        {{ $c->id }}
                    </a>
                </td>

                <td>{{ $locale }}</td>

                <td>
                    <a href="{{ route('admin.categories', array_merge(request()->query(), ['select' => $c->id])) }}">
                        @if($missing)
                            <span class="text-[#c00]! font-bold!">MISSING</span>
                        @else
                            {{ $c->name }}
                        @endif
                    </a>
                </td>

                <td>
                    @if($c->slug)
                        {{ $c->slug }}
                    @else
                        <span class="text-[#999]!">none</span>
                    @endif
                </td>
            </tr>
        @endforeach
    </x-slot:tbody>

</x-admin.data-table>

{{-- ── DETAIL / EDIT PANEL ───────────────────────────────────────────────── --}}
<x-admin.detail-panel title="category" :closeRoute="route('admin.categories', request()->except('select'))">

    @if ($selectedCategory)
        {{-- Edit translation --}}
        <form method="post"
              action="{{ route('admin.categories.save') }}"
              class="m-0!">
            @csrf

            <input type="hidden" name="id" value="{{ $selectedCategory->id }}">
            <input type="hidden" name="locale" value="{{ $locale }}">

            <div class="grid! grid-cols-1! gap-1.5! items-start! py-1.5! md:grid-cols-[160px_1fr]! md:gap-3! md:items-center!">
                <label class="font-bold! text-xs! md:text-sm!">locale</label>
                <div class="py-1.5! text-[#444]!">{{ $locale }}</div>
            </div>

            <x-admin.form-field label="name *">
                <x-ui.input name="name" :value="$selectedTranslation->name ?? ''" class="w-full! box-border! border! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white!"
                       required />
            </x-admin.form-field>

            <x-admin.form-field label="slug" :hint="'used in url: /petitions/category-slug-' . $selectedCategory->id">
                <x-ui.input name="slug" :value="$selectedTranslation->slug ?? ''" class="w-full! box-border! border! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white!" />
            </x-admin.form-field>

            <div class="grid! grid-cols-1! gap-1.5! items-start! py-1.5! md:grid-cols-[160px_1fr]! md:gap-3! md:items-center!">
                <label class="font-bold! text-xs! md:text-sm!">active</label>
                <input type="checkbox" name="is_active" value="1"
                       {{ ($selectedCategory->is_active ?? 1) ? 'checked' : '' }}>
            </div>

            <div class="grid! grid-cols-1! gap-1.5! items-start! py-1.5! md:grid-cols-[160px_1fr]! md:gap-3! md:items-center!">
                <label class="font-bold! text-xs! md:text-sm!">sort order</label>
                <input class="fc-filter-field" type="number" name="sort_order" min="0"
                       value="{{ $selectedCategory->sort_order ?? 0 }}">
            </div>

            <div class="flex! justify-end! mt-1.5!">
                <button type="submit" class="fc-abtn">save</button>
            </div>
        </form>

        {{-- Delete category (only when no petitions use it) --}}
        <div class="mt-[18px]! pt-3.5! border-t! border-[#eee]!">
            @if ($selectedPetitionCount === 0)
                <form method="post" action="{{ route('admin.categories.destroy') }}"
                      data-admin-confirm="Delete category #{{ $selectedCategory->id }} and all its translations? This cannot be undone.">
                    @csrf
                    <input type="hidden" name="id" value="{{ $selectedCategory->id }}">
                    <div class="flex! items-center! justify-between!">
                        <span class="text-xs! text-[#999]!">no petitions use this category</span>
                        <x-ui.button variant="danger" class="border! border-[#c00]! bg-[#c00]! text-white! px-[10px]! py-1! rounded-[3px]! cursor-pointer!" type="submit">delete category</x-ui.button>
                    </div>
                </form>
            @else
                <div class="text-xs! text-[#888]!">
                    ⚠ {{ $selectedPetitionCount }} petition(s) use this category — cannot delete.
                </div>
            @endif
        </div>
    @else
        <x-admin.empty-state message="select a category to edit" class="text-[#777]!" />
    @endif

</x-admin.detail-panel>

@endsection
