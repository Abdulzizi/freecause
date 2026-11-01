@foreach (\App\Support\Seo::alternates(get_defined_vars()) as $language => $alternateUrl)
    <link rel="alternate" hreflang="{{ $language }}" href="{{ $alternateUrl }}">
@endforeach
