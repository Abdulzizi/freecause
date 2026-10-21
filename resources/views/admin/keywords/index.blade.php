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
        @if (admin_can('spam', 'edit'))
            <form method="post" action="{{ route('admin.keywords.save') }}">
                @csrf
                <input type="hidden" name="locale" value="{{ $locale }}">

                <x-admin.form-field label="terms" for="keywords-terms" hint="one term per line, maximum {{ $maxTerms }} terms per locale">
                    <textarea id="keywords-terms" name="terms" rows="18"
                        class="w-full box-border border border-border rounded-sm px-2 py-1.5 text-sm bg-surface font-mono resize-y">{{ is_array(old('terms')) ? '' : old('terms', implode("\n", $terms)) }}</textarea>
                </x-admin.form-field>

                <div class="text-xs text-muted mt-2">
                    <p class="mb-1">the english list blocks its wording in every petition; the other locale lists add their own wordings to the same global blocklist.</p>
                    <p class="mb-1">a wording listed in two locales stays blocked until it is removed from both.</p>
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
            <p class="text-sm text-muted">you have read-only access to the keyword lists.</p>

            <ul class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-x-4 gap-y-1 mt-3 text-sm text-muted">
                @foreach ($terms as $term)
                    <li>{{ $term }}</li>
                @endforeach
            </ul>
        @endif
    </x-admin.section-panel>

    <x-admin.section-panel title="bundled english (read-only)">
        <p class="text-xs text-muted mb-2">
            read-only reference. the bundled english list, {{ count($reference) }} terms — the list in the textarea above is the effective one and can differ when it was overridden.
        </p>

        <ul class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-x-4 gap-y-1 text-sm text-muted">
            @foreach ($reference as $term)
                <li>{{ $term }}</li>
            @endforeach
        </ul>
    </x-admin.section-panel>

    <x-admin.section-panel title="locale lists">
        <p class="text-xs text-muted mb-2">
            every locale that has a keyword list, with its term count and whether the list is bundled or custom.
        </p>

        <div class="overflow-x-auto">
            <table class="w-full border-collapse text-sm">
                <thead>
                    <tr class="border-b border-border text-left">
                        <th class="py-1.5 pr-3 text-xs font-semibold uppercase tracking-wider text-muted">locale</th>
                        <th class="py-1.5 pr-3 text-xs font-semibold uppercase tracking-wider text-muted">terms</th>
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
