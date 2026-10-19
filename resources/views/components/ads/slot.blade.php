@props(['name', 'sticky' => false])

@php
    // resolve slot config via the Settings-aware resolver (admin overrides config).
    // NB: prop is "name" not "slot" -- $slot is Blade's reserved component-content var.
    $adsOn = \App\Support\Ads::enabled();
    $cfg = \App\Support\Ads::slot($name);
    if ($cfg && empty($cfg['key']) && str_starts_with($name, 'mobile_') && $name !== 'mobile_float') {
        foreach (['banner468', 'mobile_banner', 'mobile_inline', 'mobile_mini', 'mobile_strip', 'mobile_feed', 'mobile_native_small', 'mobile_card', 'leaderboard', 'rectangle', 'native'] as $cand) {
            $cc = \App\Support\Ads::slot($cand);
            if ($cc && ! empty($cc['key']) && ! request()->attributes->has('ads_slot_rendered_'.$cand)) {
                $name = $cand;
                $cfg = $cc;
                break;
            }
        }
    }
    $hasKey = $adsOn && $cfg && !empty($cfg['key']);
    // The 320x50 "mobile" zone belongs to the sticky anchor (partials.ads-anchor).
    // Blade renders a page's content before the layout, so an inline copy would always
    // claim the zone first and leave the anchor empty -- inline uses render nothing.
    $anchorOnly = $name === 'mobile' && ! $sticky;
    // otherwise the first occurrence of a slot in the request wins, so the layout's
    // default stack fills gaps without duplicating a zone a page already places inline.
    $show = ! $anchorOnly && $adsOn && $hasKey && \App\Support\Ads::renderOnce($name);
@endphp

@if ($show)
    @php
        $w = (int) ($cfg['w'] ?? 0);
        $h = (int) ($cfg['h'] ?? 0);
        $key = $cfg['key'] ?? '';
        // native content is fluid and taller than the reserved height; the parent
        // script grows the frame to the ad's real height (same-origin) so it never clips
        $fluid = ($cfg['format'] ?? 'iframe') === 'native';
    @endphp

    {{-- reserved box: min-height = banner height so no layout shift while the ad loads.
         The network code runs inside its own same-origin document (route ads.frame):
         the Adsterra loader document.writes an inline <script>, which the public
         site's strict CSP blocks, so each slot is sandboxed into a frame that
         carries a relaxed policy and no site content. --}}
    <div {{ $attributes->merge(['class' => "fc-ad fc-ad--{$name}"]) }}
         data-ad-id="{{ $key }}"
         style="min-height: {{ $h }}px; max-width: min(100%, {{ $w }}px); margin-inline: auto;">
        @if ($hasKey)
            <iframe
                src="{{ route('ads.frame', ['slot' => $name]) }}"
                width="{{ $w }}" height="{{ $h }}"
                title="Advertisement"
                scrolling="no"
                @if ($fluid) data-ad-fluid="1" @endif
                style="width: {{ $w }}px; max-width: 100%; {{ $fluid ? 'max-height: 320px;' : '' }} height: {{ $h }}px; border: 0; display: block; margin-inline: auto; overflow: hidden;"
                sandbox="allow-scripts allow-same-origin allow-popups allow-popups-to-escape-sandbox allow-forms"></iframe>
        @endif
    </div>
@endif
