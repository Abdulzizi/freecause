{{-- Self-contained: no layout, components, DB or cache. Only file-based lang strings. --}}
<!doctype html>
<html lang="{{ $locale }}">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex">
<title>{{ __('errors.503_title', [], $locale) }} - FreeCause</title>
<style>body{font-family:system-ui,sans-serif;margin:0;padding:2rem 1rem;color:#222;text-align:center}main{max-width:32rem;margin:10vh auto}a{color:#b00}</style>
</head>
<body>
<main>
<h1>{{ __('errors.503_title', [], $locale) }}</h1>
<p>{{ __('errors.503_lead', [], $locale) }}</p>
<p>{{ __('errors.503_hint', [], $locale) }}</p>
<p><a href="{{ url()->current() }}">{{ __('errors.503_btn', [], $locale) }}</a></p>
</main>
</body>
</html>
