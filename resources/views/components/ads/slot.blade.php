@props(['name'])

@php
    // resolve slot config via the Settings-aware resolver (admin overrides config).
    // NB: prop is "name" not "slot" -- $slot is Blade's reserved component-content var.
    $adsOn = \App\Support\Ads::enabled();
    $cfg = \App\Support\Ads::slot($name);
    $host = \App\Support\Ads::host();
    $hasKey = $adsOn && $cfg && !empty($cfg['key']);
@endphp

@if ($adsOn && $cfg)
    @php
        $w = (int) ($cfg['w'] ?? 0);
        $h = (int) ($cfg['h'] ?? 0);
        $fmt = $cfg['format'] ?? 'iframe';
        $key = $cfg['key'] ?? '';
        $path = $cfg['path'] ?? '22';
    @endphp

    {{-- reserved box: min-height = banner height so no layout shift while the ad loads.
         Scripts render INLINE inside the box: the iframe loader document.writes the
         banner at its own DOM position, so end-of-body placement would stack all
         ads at the page bottom --}}
    @php $isLocal = app()->environment('local'); @endphp
    <div {{ $attributes->merge(['class' => "fc-ad fc-ad--{$name}" . ($isLocal ? ' fc-ad--dev' : '')]) }}
         data-ad-id="{{ $key }}"
         style="min-height: {{ $h }}px;{{ $w > 0 ? ' max-width: '.$w.'px;' : '' }} margin-inline: auto;{{ $isLocal ? ' background: repeating-linear-gradient(45deg,#f0f0f0,#f0f0f0 8px,#e0e0e0 8px,#e0e0e0 16px); border: 1px dashed #aaa; position: relative;' : '' }}">
        @if ($isLocal)
            {{-- dev placeholder: shows slot name + dimensions so empty boxes are identifiable on localhost --}}
            <span style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;font:12px/1 monospace;color:#666;pointer-events:none;">
                {{ $name }} {{ $w > 0 ? $w.'×'.$h : 'native' }}
            </span>
        @endif
        @if ($fmt === 'native')
            <div id="container-{{ $key }}"></div>
        @endif

        @if ($hasKey)
            @if ($fmt === 'native')
                {{-- native: async loader targets its container div above --}}
                <script async data-cfasync="false" src="{{ $host }}/{{ $path }}/{{ $key }}"></script>
            @else
                {{-- iframe banner: atOptions from same-origin config script (CSP script-src 'self'),
                     loaded synchronously so it completes before the network loader below --}}
                <script src="{{ route('ads.config', ['slot' => $name]) }}"></script>
                <script src="{{ $host }}/{{ $path }}/{{ $key }}"></script>
            @endif
        @endif
    </div>
@endif
