@props(['name', 'sticky' => false, 'claimed' => false])

@php
    // resolve slot config via the Settings-aware resolver (admin overrides config).
    // NB: prop is "name" not "slot" -- $slot is Blade's reserved component-content var.
    // A slot renders only under its own configured zone key; an unnamed or keyless slot
    // renders nothing rather than aliasing onto another zone.
    $adsOn = \App\Support\Ads::enabled();
    $cfg = \App\Support\Ads::slot($name);
    $hasKey = $adsOn && $cfg && !empty($cfg['key']);
    // The 320x50 "mobile" zone belongs to the sticky anchor (partials.ads-anchor).
    // Blade renders a page's content before the layout, so an inline copy would always
    // claim the zone first and leave the anchor empty -- inline uses render nothing.
    $anchorOnly = $name === 'mobile' && ! $sticky;
    // otherwise the first occurrence of a slot in the request wins, so the layout's
    // default stack fills gaps without duplicating a zone a page already places inline.
    $show = ! $anchorOnly && \App\Support\Ads::canShow($name) && ($claimed || \App\Support\Ads::renderOnce($name));
@endphp

@if ($show)
    @php
        $w = (int) ($cfg['w'] ?? 0);
        $h = (int) ($cfg['h'] ?? 0);
        $key = $cfg['key'] ?? '';
        // native content is fluid and taller than the reserved height; the parent
        // script grows the frame to the ad's real height (cross-origin postMessage
        // to the dedicated ads.frame_origin) so it never clips
        $fluid = ($cfg['format'] ?? 'iframe') === 'native';
        // banner468 used to hide only below sm (640px), which let the 640-767
        // tablet band stack it alongside the anchor + a rectangle. md (768) is the
        // first width where 468px centred in the content column reads cleanly.
        $hidden = ['native' => 'max-md:hidden', 'banner468' => 'max-md:hidden'][$name] ?? '';
    @endphp

    {{-- reserved box: min-height = banner height so no layout shift while the ad loads.
         The network code runs inside its own cross-origin document on the dedicated
         ads.frame_origin (route ads.frame): the Adsterra loader document.writes an
         inline <script>, which the public site's strict CSP blocks, so each slot is
         sandboxed into a frame that carries a relaxed policy and no site content. --}}
    <div {{ $attributes->merge(['class' => trim("fc-ad fc-ad--{$name} {$hidden}")]) }}
         data-ad-slot="{{ $name }}"
         style="min-height: {{ $h }}px; max-width: min(100%, {{ $w }}px); margin-inline: auto;">
        @if ($hasKey)
            <iframe
                src="{{ \App\Support\Ads::frameUrl($name) }}"
                width="{{ $w }}" height="{{ $h }}"
                title="Advertisement"
                scrolling="no"
                @if ($fluid) data-ad-fluid="1" @endif
                style="width: {{ $w }}px; max-width: 100%; {{ $fluid ? 'max-height: 320px;' : '' }} height: {{ $h }}px; border: 0; display: block; margin-inline: auto; overflow: hidden;"
                sandbox="allow-scripts allow-same-origin allow-popups allow-popups-to-escape-sandbox allow-forms"></iframe>
        @endif
    </div>
@endif
