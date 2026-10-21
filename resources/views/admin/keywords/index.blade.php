@extends('admin.layouts.app')

@section('title', 'Review Keywords')

@section('content')

    <x-admin.page-header title="review keywords" />

    <x-admin.section-panel title="locale">
        <form method="get" action="{{ route('admin.keywords') }}">
            <x-admin.form-field label="locale" for="keywords-locale">
                <select id="keywords-locale" class="fc-filter-field" name="locale" data-admin-autosubmit>
                    @foreach ($locales as $code => $name)
                        <option value="{{ $code }}" {{ $code === $locale ? 'selected' : '' }}>
                            {{ $code }} - {{ $name }}{{ array_key_exists($code, $overrides) ? ' (custom)' : '' }}
                        </option>
                    @endforeach
                </select>
            </x-admin.form-field>
        </form>
    </x-admin.section-panel>

    <x-admin.section-panel title="terms — {{ $locale }}">
        @if (! empty($unblockedLegacy))
            <x-blatui.alert tone="warning" class="mb-3">
                these legacy spam words are no longer held for review and will be published: {{ implode(', ', $unblockedLegacy) }} — re-add the wording to a locale list to hold it for review again.
            </x-blatui.alert>
        @endif

        @if (admin_can('spam', 'edit'))
            <form method="post" action="{{ route('admin.keywords.save') }}">
                @csrf
                <input type="hidden" name="locale" value="{{ $locale }}">

                <x-admin.form-field label="terms" for="keywords-terms" hint="one term per line; group variants with 'term :: concept'; maximum {{ $maxTerms }} terms per locale">
                    <textarea id="keywords-terms" name="terms" rows="18"
                        class="w-full box-border border border-border rounded-sm px-2 py-1.5 text-sm bg-surface font-mono resize-y">{{ is_array(old('terms')) ? '' : old('terms', $formatted) }}</textarea>
                </x-admin.form-field>

                <div class="text-xs text-muted mt-2">
                    <p class="mb-1">the english list blocks its wording in every petition; the other locale lists add their own wordings to the same global blocklist.</p>
                    <p class="mb-1">a wording listed in two locales stays blocked until it is removed from both.</p>
                    <p class="mb-1">write "term :: concept" to file a wording under a concept; every wording of a concept can then be switched off at once from the concepts panel.</p>
                    @if ($hasBundled)
                        <p class="mb-1">an empty list means "no terms for this locale"; only "reset to default" brings the bundled list back.</p>
                    @else
                        <p class="mb-1">this locale has no bundled list: an empty list means "no terms for this locale", and "reset to default" removes the custom list entirely.</p>
                    @endif
                    <p>saving replaces the whole list for this locale, so keep every line you want to keep.</p>
                </div>

                <div class="mt-3">
                    <x-blatui.button type="submit">save</x-blatui.button>
                </div>
            </form>

            <div class="mt-4 pt-4 border-t border-border">
                <p class="text-xs text-muted mb-2">
                    @if ($hasBundled)
                        discard the custom list for <strong>{{ $locale }}</strong> and fall back to the bundled default.
                    @else
                        discard the custom list for <strong>{{ $locale }}</strong>; this locale has no bundled list, so it ends up with no terms.
                    @endif
                </p>
                <form method="post" action="{{ route('admin.keywords.reset') }}"
                    data-admin-confirm="{{ $hasBundled ? 'Replace this list with the bundled default?' : 'Remove the custom list for this locale?' }}">
                    @csrf
                    <input type="hidden" name="locale" value="{{ $locale }}">
                    <x-blatui.button variant="outline" type="submit">reset to default</x-blatui.button>
                </form>
            </div>
        @else
            <p class="text-sm text-muted">you have read-only access to the keyword lists — this is the saved list for {{ $locale }}, the terms of a disabled concept are kept here but are not applied.</p>

            <ul class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-x-4 gap-y-1 mt-3 text-sm text-muted">
                @foreach ($terms as $term)
                    <li>{{ $term }}</li>
                @endforeach
            </ul>
        @endif
    </x-admin.section-panel>

    @php($editable = admin_can('spam', 'edit'))
    @php($canRescan = $editable && admin_can('petitions', 'edit'))

    <x-admin.section-panel title="concepts">
        <p class="text-xs text-muted mb-2">
            wordings filed under the same concept share one switch: disabling a concept removes every one of its wordings from the blocklist without touching the saved lists. the table lists the concepts of <strong>{{ $locale }}</strong> and every disabled concept.
        </p>

        <div class="overflow-x-auto max-h-96 overflow-y-auto">
            <table class="w-full border-collapse text-sm">
                <thead>
                    <tr class="border-b border-border text-left">
                        <th class="py-1.5 pr-3 text-xs font-semibold uppercase tracking-wider text-muted">concept</th>
                        <th class="py-1.5 pr-3 text-xs font-semibold uppercase tracking-wider text-muted">wordings</th>
                        <th class="py-1.5 pr-3 text-xs font-semibold uppercase tracking-wider text-muted">state</th>
                        @if ($editable)
                            <th class="py-1.5 text-xs font-semibold uppercase tracking-wider text-muted">action</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse ($conceptRows as $row)
                        <tr class="border-b border-border">
                            <td class="py-1.5 pr-3 align-top font-mono text-xs">{{ $row['concept'] }}</td>
                            <td class="py-1.5 pr-3 align-top text-muted">
                                @forelse ($row['wordings'] as $code => $conceptTerms)
                                    <div class="whitespace-nowrap"><span class="text-ink">{{ $code }}</span>: {{ implode(', ', $conceptTerms) }}</div>
                                @empty
                                    <div class="whitespace-nowrap">no wording left</div>
                                @endforelse
                            </td>
                            <td class="py-1.5 pr-3 align-top">
                                @if ($row['enabled'])
                                    <x-blatui.badge tone="success" variant="soft" size="sm">enabled</x-blatui.badge>
                                @else
                                    <x-blatui.badge tone="danger" variant="soft" size="sm">disabled</x-blatui.badge>
                                @endif
                            </td>
                            @if ($editable)
                                <td class="py-1.5 align-top">
                                    <form method="post" action="{{ route('admin.keywords.concept') }}">
                                        @csrf
                                        <input type="hidden" name="concept" value="{{ $row['concept'] }}">
                                        <input type="hidden" name="enabled" value="{{ $row['enabled'] ? 0 : 1 }}">
                                        <input type="hidden" name="locale" value="{{ $locale }}">
                                        <x-blatui.button variant="outline" size="sm" type="submit">
                                            {{ $row['enabled'] ? 'disable' : 're-enable' }}
                                        </x-blatui.button>
                                    </form>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $editable ? 4 : 3 }}" class="py-1.5 text-muted">
                                no concepts for this locale — save a term as "term :: concept" to create one.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-admin.section-panel>

    <x-admin.section-panel title="rescan">
        <p class="text-xs text-muted mb-2">
            re-checks every published petition against the current list — the title and description of all its translations — and holds the matching ones for review. the rescan is queued and runs in the background, so it never blocks this page.
        </p>

        @if ($canRescan)
            <form method="post" action="{{ route('admin.keywords.rescan') }}"
                data-admin-confirm="Queue a re-check of every published petition against the current keyword list? The title and description of all its translations are checked; matching petitions are held for review.">
                @csrf
                <input type="hidden" name="locale" value="{{ $locale }}">
                <input type="hidden" name="confirm" value="rescan">
                <x-blatui.button variant="outline" type="submit">queue rescan</x-blatui.button>
            </form>
        @else
            <p class="text-sm text-muted">a rescan holds published petitions, so it needs the petitions edit permission as well.</p>
        @endif

        @if ($status !== '')
            <p class="text-xs text-muted mt-2">last rescan: {{ $status }}</p>
        @endif
    </x-admin.section-panel>

    <x-admin.section-panel title="bundled english (read-only)">
        <p class="text-xs text-muted mb-2">
            read-only reference. the bundled english list, {{ count($reference) }} terms — the list in the textarea above is the saved list; the terms of a disabled concept are kept there but are not applied.
        </p>

        <ul class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-x-4 gap-y-1 text-sm text-muted">
            @foreach ($reference as $term)
                <li>{{ $term }}</li>
            @endforeach
        </ul>
    </x-admin.section-panel>

    <x-admin.section-panel title="locale lists">
        <p class="text-xs text-muted mb-2">
            every locale that has a keyword list, with its active term count — the terms currently in force, the terms of a disabled concept are not counted — and whether the list is bundled or custom.
        </p>

        <div class="overflow-x-auto">
            <table class="w-full border-collapse text-sm">
                <thead>
                    <tr class="border-b border-border text-left">
                        <th class="py-1.5 pr-3 text-xs font-semibold uppercase tracking-wider text-muted">locale</th>
                        <th class="py-1.5 pr-3 text-xs font-semibold uppercase tracking-wider text-muted">active terms</th>
                        <th class="py-1.5 text-xs font-semibold uppercase tracking-wider text-muted">source</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($counts as $row)
                        <tr class="border-b border-border">
                            <td class="py-1.5 pr-3">{{ $row['locale'] }} - {{ $row['name'] }}</td>
                            <td class="py-1.5 pr-3 tabular-nums">{{ $row['count'] }}</td>
                            <td class="py-1.5">
                                @if ($row['custom'])
                                    <x-blatui.badge tone="info" variant="soft" size="sm">custom</x-blatui.badge>
                                @else
                                    <x-blatui.badge tone="neutral" variant="soft" size="sm">bundled</x-blatui.badge>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-admin.section-panel>

@endsection
