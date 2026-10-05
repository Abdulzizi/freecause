@extends('admin.layouts.app')

@section('title', 'Statistics')

@section('content')

    <x-admin.page-header title="statistics" />

    <x-admin.filter-box title="statistics" :action="route('admin.stats')" :reset="route('admin.stats')">

        <select name="locale" class="w-full! box-border! border! border-[#bdbdbd]! rounded! py-1.5! px-2! text-sm! bg-white! max-w-[140px]! max-md:text-[13px]! max-md:p-1.5! max-md:px-2!" data-admin-autosubmit>
            <option value="">All locales</option>
            @foreach ($languages as $lang)
                <option value="{{ $lang->code }}" {{ $filters['locale'] == $lang->code ? 'selected' : '' }}>
                    {{ $lang->name }}
                </option>
            @endforeach
        </select>

        <select name="range" class="w-full! box-border! border! border-[#bdbdbd]! rounded! py-1.5! px-2! text-sm! bg-white! max-w-[160px]! max-md:text-[13px]! max-md:p-1.5! max-md:px-2!" data-admin-autosubmit>
            <option value="1d" {{ $filters['range'] == '1d' ? 'selected' : '' }}>Last day</option>
            <option value="7d" {{ $filters['range'] == '7d' ? 'selected' : '' }}>Last 7 days</option>
            <option value="30d" {{ $filters['range'] == '30d' ? 'selected' : '' }}>Last 30 days</option>
        </select>

        <select name="type" class="w-full! box-border! border! border-[#bdbdbd]! rounded! py-1.5! px-2! text-sm! bg-white! max-w-[240px]! max-md:text-[13px]! max-md:p-1.5! max-md:px-2!" data-admin-autosubmit>
            <option value="">Select statistic</option>
            <option value="top_signers" {{ $filters['type'] == 'top_signers' ? 'selected' : '' }}>Top 20 signers</option>
            <option value="top_petitioners" {{ $filters['type'] == 'top_petitioners' ? 'selected' : '' }}>Top 20 petitioners
            </option>
            <option value="petitions_all" {{ $filters['type'] == 'petitions_all' ? 'selected' : '' }}>Petitions (all)</option>
            <option value="emails_verified" {{ $filters['type'] == 'emails_verified' ? 'selected' : '' }}>Verified emails</option>
        </select>

    </x-admin.filter-box>

    <div class="border! border-[#9e9e9e]! rounded! p-2.5! mb-5!">

        <div class="flex! flex-wrap! gap-[30px]!">

            <div>
                <h2>{{ number_format($summary['users_total']) }}</h2>
                users<br>
                <small>
                    +{{ number_format($summary['users_new']) }} new |
                    {{ $summary['users_verified_percent'] }}% verified
                </small>
            </div>

            <div>
                <h2>{{ number_format($summary['petitions_total']) }}</h2>
                petitions<br>
                <small>
                    +{{ number_format($summary['petitions_new']) }} new |
                    {{ $summary['petitions_publish_percent'] }}% published
                </small>
            </div>

            <div>
                <h2>{{ number_format($summary['signatures_total']) }}</h2>
                signatures<br>
                <small>
                    +{{ number_format($summary['signatures_new']) }} new
                </small>
            </div>

        </div>

    </div>


    @if ($results)

        <x-admin.data-table :p="$results">

            <x-slot:thead>
                <tr data-admin-row-hover class="border-b! border-[#ccc]!">
                    @foreach ($columns as $c)
                        <th class="text-left!">{{ $c }}</th>
                    @endforeach
                </tr>
            </x-slot:thead>

            <x-slot:tbody>
                @foreach ($results as $row)
                    <tr data-admin-row-hover class="border-b! border-[#eee]!">
                        @foreach ((array) $row as $cell)
                            <td>{{ $cell }}</td>
                        @endforeach
                    </tr>
                @endforeach
            </x-slot:tbody>

        </x-admin.data-table>

    @endif

@endsection
