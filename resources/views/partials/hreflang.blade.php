@php
    use App\Models\Language;
    use App\Models\PetitionTranslation;

    $hrefLangs = cache()->remember('active_languages_full', 60, fn() => Language::where('is_active', 1)->orderByDesc('is_default')->get());

    // A fallback page is not a translation in the requested language.
    if (request()->routeIs('petition.*') && request()->route('id')) {
        $translatedLocales = PetitionTranslation::where('petition_id', request()->route('id'))->pluck('locale');
        $hrefLangs = $hrefLangs->whereIn('code', $translatedLocales)->values();
    }
@endphp

@foreach ($hrefLangs as $lang)
    <link rel="alternate" hreflang="{{ $lang->code }}" href="{{ locale_url($lang->code) }}">
@endforeach
@if ($hrefLangs->isNotEmpty())
    <link rel="alternate" hreflang="x-default" href="{{ locale_url($hrefLangs->first()->code) }}">
@endif
