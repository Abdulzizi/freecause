@extends('admin.layouts.app')

@section('title', 'Statistics')

@section('content')

    <h1>statistics</h1>

    <x-admin.filter-box title="statistics" :action="route('admin.stats')" :reset="route('admin.stats')">

        <select name="locale" class="fc-select" style="max-width:140px;">
            <option value="">All locales</option>
            @foreach ($languages as $lang)
                <option value="{{ $lang->code }}" {{ $filters['locale'] == $lang->code ? 'selected' : '' }}>
                    {{ $lang->name }}
                </option>
            @endforeach
        </select>

        <select name="range" class="fc-select" style="max-width:160px;">
            <option value="1d" {{ $filters['range'] == '1d' ? 'selected' : '' }}>Last day</option>
            <option value="7d" {{ $filters['range'] == '7d' ? 'selected' : '' }}>Last 7 days</option>
            <option value="30d" {{ $filters['range'] == '30d' ? 'selected' : '' }}>Last 30 days</option>
        </select>

        <select name="type" class="fc-select" style="max-width:240px;">
            <option value="">Select statistic</option>
            <option value="top_signers" {{ $filters['type'] == 'top_signers' ? 'selected' : '' }}>Top 20 signers</option>
            <option value="top_petitioners" {{ $filters['type'] == 'top_petitioners' ? 'selected' : '' }}>Top 20 petitioners
            </option>
            <option value="petitions_all" {{ $filters['type'] == 'petitions_all' ? 'selected' : '' }}>Petitions (all)</option>
            <option value="emails_verified" {{ $filters['type'] == 'emails_verified' ? 'selected' : '' }}>Verified emails</option>
        </select>

    </x-admin.filter-box>

    <div class="fc-box" style="margin-bottom:20px;">

        <div style="display:flex; flex-wrap:wrap; gap:30px;">

            <div>
                <h3>{{ number_format($summary['users_total']) }}</h3>
                users<br>
                <small>
                    +{{ number_format($summary['users_new']) }} new |
                    {{ $summary['users_verified_percent'] }}% verified
                </small>
            </div>

            <div>
                <h3>{{ number_format($summary['petitions_total']) }}</h3>
                petitions<br>
                <small>
                    +{{ number_format($summary['petitions_new']) }} new |
                    {{ $summary['petitions_publish_percent'] }}% published
                </small>
            </div>

            <div>
                <h3>{{ number_format($summary['signatures_total']) }}</h3>
                signatures<br>
                <small>
                    +{{ number_format($summary['signatures_new']) }} new
                </small>
            </div>

        </div>

    </div>


    @if ($results)

        <x-admin.list-table-box :p="$results">

            <x-slot:thead>
                <tr style="border-bottom:1px solid #ccc;">
                    @foreach ($columns as $c)
                        <th style="text-align:left;">{{ $c }}</th>
                    @endforeach
                </tr>
            </x-slot:thead>

            <x-slot:tbody>
                @foreach ($results as $row)
                    <tr style="border-bottom:1px solid #eee;">
                        @foreach ((array) $row as $cell)
                            <td>{{ $cell }}</td>
                        @endforeach
                    </tr>
                @endforeach
            </x-slot:tbody>

        </x-admin.list-table-box>

    @endif

@endsection
