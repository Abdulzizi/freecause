@extends('admin.layouts.app')

@section('title', 'Fanpages')

@section('content')

    <h1>petitions ({{ number_format($approxTotal) }})</h1>

    <x-admin.filter-box title="filter petitions" :action="route('admin.fanpages')" :reset="route('admin.fanpages')">

        <select class="fc-select" name="locale" style="max-width:140px;">
            @foreach($locales as $k => $label)
                <option value="{{ $k }}" {{ $locale === $k ? 'selected' : '' }}>
                    {{ $label }}
                </option>
            @endforeach
        </select>

        <input class="fc-input" name="id" placeholder="ID" value="{{ $filters['id'] ?? '' }}" style="max-width:90px;">

        <input class="fc-input" name="email" placeholder="Author Email" value="{{ $filters['email'] ?? '' }}"
            style="max-width:200px;">

        <input class="fc-input" name="title" placeholder="Title" value="{{ $filters['title'] ?? '' }}"
            style="max-width:220px;">

    </x-admin.filter-box>


    <x-admin.list-table-box :p="$fanpages">

        <x-slot:thead>
            <tr>
                <th>ID</th>
                <th>Author</th>
                <th>FP</th>
                <th>Title</th>
                <th>Locale</th>
                <th>Date</th>
            </tr>
        </x-slot:thead>

        <x-slot:tbody>
            @foreach($fanpages as $row)
                <tr>
                    <td>{{ $row->id }}</td>
                    <td>{{ $row->email }}</td>
                    <td>—</td>
                    <td>{{ $row->title }}</td>
                    <td>{{ $row->locale }}</td>
                    <td>{{ $row->created_at }}</td>
                </tr>
            @endforeach
        </x-slot:tbody>

    </x-admin.list-table-box>

@endsection
