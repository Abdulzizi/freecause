@if (\App\Support\Ads::enabled() && \App\Support\Ads::slot('skyscraper') && ! empty(\App\Support\Ads::slot('skyscraper')['key']))
    <aside id="fc-ad-rail" class="fc-ad-rail" aria-label="Advertisement">
        <x-ads.slot name="skyscraper" />
    </aside>
@endif
