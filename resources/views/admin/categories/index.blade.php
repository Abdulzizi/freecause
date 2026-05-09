@extends('admin.layouts.app')

@section('title', 'Categories')

@section('content')

<h1>categories ({{ number_format($approxTotal) }})</h1>

@if (session('success'))
    <div class="fc-success">{{ session('success') }}</div>
@endif

@if ($errors->any())
    <div class="fc-error" style="margin-bottom:12px;">{{ $errors->first() }}</div>
@endif

{{-- ── NEW CATEGORY FORM ─────────────────────────────────────────────────── --}}
<div style="background:#f9f9f9; border:1px solid #ddd; border-radius:4px; padding:16px; margin-bottom:18px;">
    <div style="font-weight:700; margin-bottom:10px; font-size:13px; text-transform:uppercase; letter-spacing:.05em;">
        + new category
    </div>
    <form method="post" action="{{ route('admin.categories.create') }}" style="display:flex; gap:10px; align-items:flex-end; flex-wrap:wrap;">
        @csrf
        <div>
            <label style="display:block; font-size:12px; color:#555; margin-bottom:4px;">locale</label>
            <select class="fc-select" name="locale" style="max-width:130px;">
                @foreach ($locales as $k => $label)
                    <option value="{{ $k }}" {{ $locale === $k ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div style="flex:1; min-width:180px;">
            <label style="display:block; font-size:12px; color:#555; margin-bottom:4px;">name *</label>
            <input class="fc-input" type="text" name="name" placeholder="Category name" required style="width:100%;">
        </div>
        <div>
            <button class="fc-btn" type="submit" style="background:#2a7a2a; color:#fff;">create</button>
        </div>
    </form>
</div>

{{-- ── FILTER ────────────────────────────────────────────────────────────── --}}
<x-admin.filter-box title="filter categories"
    :action="route('admin.categories')"
    :reset="route('admin.categories')">

    <select class="fc-select" name="locale" style="max-width:140px;">
        @foreach ($locales as $k => $label)
            <option value="{{ $k }}" {{ $locale === $k ? 'selected' : '' }}>
                {{ $label }}
            </option>
        @endforeach
    </select>

    <input class="fc-input" name="id"
        placeholder="ID"
        value="{{ $filters['id'] ?? '' }}"
        style="max-width:90px;">

    <input class="fc-input" name="name"
        placeholder="Name"
        value="{{ $filters['name'] ?? '' }}"
        style="max-width:180px;">

</x-admin.filter-box>

{{-- ── LIST ──────────────────────────────────────────────────────────────── --}}
<x-admin.list-table-box emptyText="no categories found, try clearing filters"
    :p="$categories">

    <x-slot:thead>
        <tr style="border-bottom:1px solid #ccc;">
            <th style="width:90px;">ID</th>
            <th style="width:110px;">Locale</th>
            <th>Name</th>
            <th>Slug</th>
        </tr>
    </x-slot:thead>

    <x-slot:tbody>
        @foreach ($categories as $c)
            @php
                $missing = empty($c->name);
            @endphp

            <tr style="border-bottom:1px solid #eee;">
                <td>
                    <a href="{{ route('admin.categories', array_merge(request()->query(), ['select' => $c->id])) }}">
                        {{ $c->id }}
                    </a>
                </td>

                <td>{{ $locale }}</td>

                <td>
                    <a href="{{ route('admin.categories', array_merge(request()->query(), ['select' => $c->id])) }}">
                        @if($missing)
                            <span style="color:#c00; font-weight:bold;">MISSING</span>
                        @else
                            {{ $c->name }}
                        @endif
                    </a>
                </td>

                <td>
                    @if($c->slug)
                        {{ $c->slug }}
                    @else
                        <span style="color:#999;">none</span>
                    @endif
                </td>
            </tr>
        @endforeach
    </x-slot:tbody>

</x-admin.list-table-box>

{{-- ── DETAIL / EDIT PANEL ───────────────────────────────────────────────── --}}
<x-admin.detail-panel title="category">

    @if ($selectedCategory)
        {{-- Edit translation --}}
        <form method="post"
              action="{{ route('admin.categories.save') }}"
              style="margin:0;">
            @csrf

            <input type="hidden" name="id" value="{{ $selectedCategory->id }}">
            <input type="hidden" name="locale" value="{{ $locale }}">

            <div class="fc-row">
                <label>locale</label>
                <div style="padding:6px 0; color:#444;">{{ $locale }}</div>
            </div>

            <div class="fc-row">
                <label>name *</label>
                <input class="fc-input"
                       type="text"
                       name="name"
                       value="{{ $selectedTranslation->name ?? '' }}"
                       required>
            </div>

            <div class="fc-row">
                <label>slug</label>
                <input class="fc-input"
                       type="text"
                       name="slug"
                       value="{{ $selectedTranslation->slug ?? '' }}">
                <div style="font-size:11px; color:#777; margin-top:4px;">
                    used in url: /petitions/category-<b>slug</b>-{{ $selectedCategory->id }}
                </div>
            </div>

            <div style="display:flex; justify-content:flex-end; margin-top:6px;">
                <button class="fc-btn" type="submit">save</button>
            </div>
        </form>

        {{-- Delete category (only when no petitions use it) --}}
        <div style="margin-top:18px; padding-top:14px; border-top:1px solid #eee;">
            @if ($selectedPetitionCount === 0)
                <form method="post" action="{{ route('admin.categories.destroy') }}"
                      onsubmit="return confirm('Delete category #{{ $selectedCategory->id }} and all its translations? This cannot be undone.')">
                    @csrf
                    <input type="hidden" name="id" value="{{ $selectedCategory->id }}">
                    <div style="display:flex; align-items:center; justify-content:space-between;">
                        <span style="font-size:12px; color:#999;">no petitions use this category</span>
                        <button class="fc-btn" type="submit"
                                style="background:#c00; color:#fff; border-color:#c00;">delete category</button>
                    </div>
                </form>
            @else
                <div style="font-size:12px; color:#888;">
                    ⚠ {{ $selectedPetitionCount }} petition(s) use this category — cannot delete.
                </div>
            @endif
        </div>
    @else
        <div style="color:#777;">select a category to edit</div>
    @endif

</x-admin.detail-panel>

@endsection
