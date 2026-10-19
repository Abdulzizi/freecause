@if (\App\Support\Ads::enabled() && \App\Support\Ads::slot('skyscraper_left') && ! empty(\App\Support\Ads::slot('skyscraper_left')['key']))
    <aside id="fc-ad-rail-left" class="fc-ad-rail fc-ad-rail--left" aria-label="Advertisement">
        <x-ads.slot name="skyscraper_left" />
    </aside>
@endif
