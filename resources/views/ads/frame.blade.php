<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
<meta charset="utf-8">
<meta name="robots" content="noindex, nofollow">
<style>html, body { margin: 0; padding: 0; overflow: hidden; background: transparent; }</style>
</head>
<body>
@php
    $key = $cfg['key'];
    $path = $cfg['path'] ?? '22';
@endphp
@if (($cfg['format'] ?? 'iframe') === 'native')
    {{-- container before the async loader so it is parsed when the loader runs --}}
    <div id="container-{{ $key }}"></div>
    <script async data-cfasync="false" src="{{ $host }}/{{ $path }}/{{ $key }}"></script>
@else
    <script src="{{ route('ads.config', ['slot' => $slot]) }}"></script>
    <script data-cfasync="false" src="{{ $host }}/{{ $path }}/{{ $key }}"></script>
@endif
<script>
(function () {
    var last = 0;
    function post() {
        var h = Math.ceil(document.documentElement.scrollHeight);
        if (h !== last) {
            last = h;
            parent.postMessage({ fcAdHeight: h }, '*');
        }
    }
    new ResizeObserver(post).observe(document.documentElement);
    post();
})();
</script>
</body>
</html>
