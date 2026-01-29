@php
    // locale => [label, flag_filename]
    $countries = [
        'en' => ['United States', 'en_US.png'],
        'fr' => ['France', 'fr_FR.png'],
        'it' => ['Italia', 'it_IT.png'],
        'es' => ['España', 'es_ES.png'],
        'de' => ['Deutschland', 'de_DE.png'],
        'pt' => ['Portugal', 'pt_PT.png'],
        'nl' => ['Nederland', 'nl_NL.png'],
    ];

    $locale = app()->getLocale();
    [$label, $flagFile] = $countries[$locale] ?? ['United States', 'en_US.png'];

    $flagBase = asset('legacy/images/country-flags/rounded1');
@endphp

<div class="dropdown fc-locale-dropdown position-static">
    <button class="btn fc-flag-btn dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
        <img class="fc-flag-img" src="{{ $flagBase . '/' . $flagFile }}" alt="{{ $label }}">
    </button>

    <div class="dropdown-menu fc-country-panel w-100 shadow-sm border-0">
        <div class="container py-3">
            <div class="d-flex flex-wrap gap-2">
                @foreach ($countries as $loc => [$name, $file])
                    <a class="fc-country-pill {{ $loc === $locale ? 'active' : '' }}" href="{{ locale_url($loc) }}">
                        <img src="{{ $flagBase . '/' . $file }}" alt="{{ $name }}">
                        <span>{{ $name }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</div>
