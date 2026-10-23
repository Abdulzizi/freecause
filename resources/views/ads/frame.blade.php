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
    $parentOrigin = \App\Support\Ads::parentOrigin();
@endphp
@if (($cfg['format'] ?? 'iframe') === 'native')
    {{-- container before the async loader so it is parsed when the loader runs --}}
    <div id="container-{{ $key }}"></div>
    <script async data-cfasync="false" src="{{ $host }}/{{ $path }}/{{ $key }}"></script>
@else
    <script data-cfasync="false">atOptions = {'key': {{ \Illuminate\Support\Js::from($key) }}, 'format': 'iframe', 'height': {{ (int) ($cfg['h'] ?? 0) }}, 'width': {{ (int) ($cfg['w'] ?? 0) }}, 'params': {}};</script>
    <script data-cfasync="false" src="{{ $host }}/{{ $path }}/{{ $key }}"></script>
@endif
@if ($parentOrigin !== '')
<script>
(function () {
    var last = 0;
    function post() {
        var h = Math.ceil(document.documentElement.scrollHeight);
        if (h !== last) {
            last = h;
            parent.postMessage({ fcAdHeight: h, fcAdSlot: {{ \Illuminate\Support\Js::from($slot) }} }, {{ \Illuminate\Support\Js::from($parentOrigin) }});
        }
    }
    new ResizeObserver(post).observe(document.documentElement);
    post();
})();
</script>
@endif
</body>
</html>
