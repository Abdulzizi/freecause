@php
    use App\Models\Language;

    $languages = cache()->remember('active_languages_full', 60, fn() => Language::where('is_active', 1)->orderByDesc('is_default')->get(),);
    $locale = app()->getLocale();
    $current = $languages->firstWhere('code', $locale) ?? $languages->first();
@endphp

<div class="dropdown fc-locale-dropdown position-static">
    <button class="btn fc-flag-btn dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
        @if ($current?->flag)
            <img class="fc-flag-img" src="{{ asset($current->flag) }}" alt="{{ $current->name }}">
        @endif
    </button>

    <div class="dropdown-menu fc-country-panel w-100 shadow-sm border-0">
        <div class="container py-3">
            <div class="d-flex flex-wrap gap-2">
                @foreach ($languages as $lang)
                    <a class="fc-country-pill {{ $lang->code === $locale ? 'active' : '' }}"
                        href="{{ locale_url($lang->code) }}">
                        @if ($lang->flag)
                            <img src="{{ asset($lang->flag) }}" width="20" alt="{{ $lang->name }}">
                        @endif
                        <span>{{ $lang->name }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</div>
