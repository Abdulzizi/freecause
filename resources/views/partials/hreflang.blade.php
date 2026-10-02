@php
    use App\Models\Language;

    $hrefLangs = cache()->remember('active_languages_full', 60, fn() => Language::where('is_active', 1)->orderByDesc('is_default')->get());
@endphp

@foreach ($hrefLangs as $lang)
    <link rel="alternate" hreflang="{{ $lang->code }}" href="{{ locale_url($lang->code) }}">
@endforeach
<link rel="alternate" hreflang="x-default" href="{{ locale_url('en') }}">
