@extends('admin.layouts.app')

@section('title', 'Statistics')

@section('content')

    <x-admin.page-header title="statistics" />

    <x-admin.filter-box title="statistics" :action="route('admin.stats')" :reset="route('admin.stats')">

        <select name="locale" class="fc-filter-field" data-admin-autosubmit>
            <option value="">All locales</option>
            @foreach ($languages as $lang)
                <option value="{{ $lang->code }}" {{ $filters['locale'] == $lang->code ? 'selected' : '' }}>
                    {{ $lang->name }}
                </option>
            @endforeach
        </select>

        <select name="range" class="fc-filter-field" data-admin-autosubmit>
            <option value="1d" {{ $filters['range'] == '1d' ? 'selected' : '' }}>Last day</option>
            <option value="7d" {{ $filters['range'] == '7d' ? 'selected' : '' }}>Last 7 days</option>
            <option value="30d" {{ $filters['range'] == '30d' ? 'selected' : '' }}>Last 30 days</option>
        </select>

        <select name="type" class="fc-filter-field" data-admin-autosubmit>
            <option value="">Select statistic</option>
            <option value="top_signers" {{ $filters['type'] == 'top_signers' ? 'selected' : '' }}>Top 20 signers</option>
            <option value="top_petitioners" {{ $filters['type'] == 'top_petitioners' ? 'selected' : '' }}>Top 20 petitioners
            </option>
            <option value="petitions_all" {{ $filters['type'] == 'petitions_all' ? 'selected' : '' }}>Petitions (all)</option>
            <option value="emails_verified" {{ $filters['type'] == 'emails_verified' ? 'selected' : '' }}>Verified emails</option>
        </select>

    </x-admin.filter-box>

    <div class="border border-border rounded-sm p-2.5 mb-5">

        <div class="flex flex-wrap gap-8">

            <div>
                <h2>{{ number_format($summary['users_total']) }}</h2>
                real users<br>
                <small>
                    +{{ number_format($summary['users_new']) }} new |
                    {{ $summary['users_verified_percent'] }}% verified |
                    {{ number_format($summary['users_accounts']) }} accounts
                    ({{ number_format($summary['users_synthetic']) }} legacy nick-only)
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

    <section class="border border-border rounded-sm p-4 mb-5" aria-labelledby="aggregate-traffic-title">
        <h2 id="aggregate-traffic-title" class="font-bold mb-2">Aggregate page loads</h2>
        <p class="mb-3">First-party counts across the site and magazine, including visitors who reject optional cookies.
            No visitor identifiers, cookies, full URLs or referrers are stored in these statistics.</p>
        <div class="flex flex-wrap gap-8 mb-3">
            <div><strong>{{ number_format($traffic['browser']) }}</strong><br>Browser page loads</div>
            <div><strong>{{ number_format($traffic['automation']) }}</strong><br>Recognized automated loads</div>
            @foreach ($traffic['devices'] as $device => $total)
                <div><strong>{{ number_format($total) }}</strong><br>{{ ucfirst($device) }} loads</div>
            @endforeach
        </div>
        @if ($traffic['areas'])
            <ul class="mb-3">
                @foreach ($traffic['areas'] as $area => $total)
                    <li>{{ ucfirst($area) }}: {{ number_format($total) }}</li>
                @endforeach
            </ul>
        @endif
        <p class="text-sm text-muted">Page loads are not unique visitors or verified humans. Reloads count again;
            bots that imitate browsers may be included. JavaScript blockers, Do Not Track and Global Privacy Control can prevent counting.
            Hourly UTC totals follow the selected language and period, with up to one hour of rounding. Aggregate totals are retained for 90 days.</p>
    </section>

    @if ($results)

        <x-admin.data-table :p="$results">

            <x-slot:thead>
                <tr data-admin-row-hover class="border-b border-border">
                    @foreach ($columns as $c)
                        <th class="text-left">{{ $c }}</th>
                    @endforeach
                </tr>
            </x-slot:thead>

            <x-slot:tbody>
                @foreach ($results as $row)
                    <tr data-admin-row-hover class="border-b border-border">
                        @foreach ((array) $row as $cell)
                            <td>{{ $cell }}</td>
                        @endforeach
                    </tr>
                @endforeach
            </x-slot:tbody>

        </x-admin.data-table>

    @endif

@endsection
