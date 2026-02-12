@extends('admin.layouts.app')

@section('title', 'Pages')

@section('content')

    <h1>pages ({{ number_format($approxTotal) }})</h1>

    @if (session('success'))
        <div class="fc-success">{{ session('success') }}</div>
    @endif

    <x-admin.filter-box title="filter pages" :action="route('admin.pages')" :reset="route('admin.pages')">

        <input class="fc-input" style="max-width:90px;" name="id" placeholder="ID" value="{{ $filters['id'] }}">

        <input class="fc-input" style="max-width:200px;" name="title" placeholder="Title" value="{{ $filters['title'] }}">

        <select class="fc-select" name="locale" style="max-width:160px;">
            @foreach($locales as $k => $label)
                <option value="{{ $k }}" {{ $filters['locale'] === $k ? 'selected' : '' }}>
                    {{ $label }}
                </option>
            @endforeach
        </select>

    </x-admin.filter-box>


    <x-admin.list-table-box empty-text="no pages found" :p="$pages">

        <x-slot:thead>
            <tr style="border-bottom:1px solid #ccc;">
                <th style="width:80px;">id</th>
                <th style="width:26px;">p</th>
                <th>title</th>
                <th>slug</th>
                <th style="width:90px;">locale</th>
            </tr>
        </x-slot:thead>

        <x-slot:tbody>
            @foreach ($pages as $p)
                    <tr style="border-bottom:1px solid #eee;">
                        <td>
                            <a href="{{ route('admin.pages', array_merge(request()->query(), [
                    'select' => $p->page_id,
                    'locale' => $p->locale
                ])) }}" style="text-decoration:none; color:#000;">
                                {{ $p->page_id }}
                            </a>
                        </td>

                        <td>
                            @if ($p->published)
                                <span class="fc-ok">✔</span>
                            @else
                                <span class="fc-no">✖</span>
                            @endif
                        </td>

                        <td>{{ $p->title }}</td>
                        <td>{{ $p->slug }}</td>
                        <td>{{ $p->locale }}</td>
                    </tr>
            @endforeach
        </x-slot:tbody>

    </x-admin.list-table-box>

    <form method="POST" action="{{ route('admin.pages.save') }}">
        @csrf

        <input type="hidden" name="page_id" value="{{ $selectedPage->page_id ?? '' }}">

        <div class="fc-tab">data</div>
        <div class="fc-box">

            <div class="fc-row">
                <label>locale</label>
                <select name="locale" class="fc-select">
                    @foreach($locales as $k => $label)
                        @if($k !== '')
                            <option value="{{ $k }}" {{ ($selectedPage->locale ?? '') === $k ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endif
                    @endforeach
                </select>
            </div>

            <div class="fc-row">
                <label>published</label>
                <input type="checkbox" name="published" value="1" {{ ($selectedPage->published ?? 0) ? 'checked' : '' }}>
            </div>

            <div class="fc-row">
                <label>title</label>
                <input class="fc-input" type="text" name="title" value="{{ $selectedPage->title ?? '' }}">
            </div>

            <div class="fc-row">
                <label>slug</label>
                <input class="fc-input" type="text" name="slug" value="{{ $selectedPage->slug ?? '' }}">
            </div>

            <div class="fc-row">
                <label>content</label>
                <textarea name="content" class="fc-textarea fc-editor content">
                        {{ $selectedPage->content ?? '' }}
                    </textarea>
            </div>

        </div>

        <div style="display:flex; justify-content:flex-end;">
            <button class="fc-btn" type="submit">save</button>
        </div>

    </form>
@endsection
