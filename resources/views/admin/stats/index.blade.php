@extends('admin.layouts.app')

@section('title', 'Statistics')

@section('content')

    <h1>statistics</h1>

    <x-admin.filter-box title="statistics" :action="route('admin.stats')" :reset="route('admin.stats')">

        <select name="locale" class="fc-select" style="max-width:140px;">
            <option value="">(locale)</option>
            <option value="en" {{ $filters['locale'] == 'en' ? 'selected' : '' }}>English</option>
            <option value="it" {{ $filters['locale'] == 'it' ? 'selected' : '' }}>Italian</option>
            <option value="fr" {{ $filters['locale'] == 'fr' ? 'selected' : '' }}>French</option>
        </select>

        <select name="range" class="fc-select" style="max-width:160px;">
            <option value="1d" {{ $filters['range'] == '1d' ? 'selected' : '' }}>Last day</option>
            <option value="7d" {{ $filters['range'] == '7d' ? 'selected' : '' }}>Last 7 days</option>
            <option value="30d" {{ $filters['range'] == '30d' ? 'selected' : '' }}>Last 30 days</option>
        </select>

        <select name="type" class="fc-select" style="max-width:240px;">
            <option value="">(select statistic)</option>
            <option value="top_signers" {{ $filters['type'] == 'top_signers' ? 'selected' : '' }}>Top 20 signers</option>
            <option value="top_petitioners" {{ $filters['type'] == 'top_petitioners' ? 'selected' : '' }}>Top 20 petitioners
            </option>
            <option value="petitions_all" {{ $filters['type'] == 'petitions_all' ? 'selected' : '' }}>Petitions (all)</option>
            <option value="petitions_verified" {{ $filters['type'] == 'petitions_verified' ? 'selected' : '' }}>Petitions
                (verified)</option>
            <option value="emails_all" {{ $filters['type'] == 'emails_all' ? 'selected' : '' }}>Emails (all)</option>
            <option value="emails_verified" {{ $filters['type'] == 'emails_verified' ? 'selected' : '' }}>Emails (verified)
            </option>
        </select>

    </x-admin.filter-box>


    @if($results->count())

        <div class="fc-box">
            <table style="width:100%; border-collapse:collapse;">
                <thead>
                    <tr style="border-bottom:1px solid #ccc;">
                        @foreach($columns as $c)
                            <th style="text-align:left;">{{ $c }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($results as $row)
                        <tr style="border-bottom:1px solid #eee;">
                            @foreach((array) $row as $cell)
                                <td>{{ $cell }}</td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

    @endif

@endsection
