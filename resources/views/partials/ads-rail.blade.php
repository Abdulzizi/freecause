@if (\App\Support\Ads::enabled() && ($cfg = \App\Support\Ads::slot('skyscraper')) && ! empty($cfg['key']) && ! request()->attributes->has('ads_slot_rendered_skyscraper') && \App\Support\Ads::renderOnce('skyscraper'))
    <aside id="fc-ad-rail" class="fc-ad-rail" aria-label="Advertisement">
        <x-ads.slot name="skyscraper" claimed />
    </aside>
@endif
