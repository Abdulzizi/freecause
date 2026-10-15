@props(['name'])

@php
    // resolve slot config; bail silently if ads disabled or slot unknown.
    // NB: prop is "name" not "slot" -- $slot is Blade's reserved component-content var.
    $adsOn = (bool) config('ads.enabled', false);
    $cfg = config("ads.slots.$name");
    $host = rtrim((string) config('ads.host', ''), '/');
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

    {{-- reserved box: min-height = banner height so no layout shift while the ad loads --}}
    <div {{ $attributes->merge(['class' => "fc-ad fc-ad--{$name}"]) }}
         data-ad-id="{{ $key }}"
         style="min-height: {{ $h }}px; max-width: {{ $w }}px; margin-inline: auto;">
        @if ($hasKey && $fmt === 'native')
            <div id="container-{{ $key }}"></div>
        @endif
    </div>

    @if ($hasKey)
        @push('scripts')
            @if ($fmt === 'native')
                {{-- native: async loader + its own container (rendered above) --}}
                <script async data-cfasync="false" src="{{ $host }}/{{ $path }}/{{ $key }}"></script>
            @else
                {{-- iframe banner: atOptions must be set immediately before each loader --}}
                <script>
                    atOptions = {
                        'key': '{{ $key }}',
                        'format': 'iframe',
                        'height': {{ $h }},
                        'width': {{ $w }},
                        'params': {}
                    };
                </script>
                <script src="{{ $host }}/{{ $path }}/{{ $key }}"></script>
            @endif
        @endpush
    @endif
@endif
