@props([
    'title' => 'filter',
    'action' => '',
    'reset' => '',
    'activeCount' => 0,
])

<div class="fc-tab" style="display:flex; align-items:center; gap:8px;">
    {{ $title }}
    @if ($activeCount > 0)
        <span class="fc-filter-count show">{{ $activeCount }} active</span>
    @endif
</div>
<div class="fc-box">
    <form method="get" action="{{ $action }}" class="fc-filter-form" data-live-search style="margin:0;">

        {{ $slot }}

        <div class="fc-filter-actions">
            <button class="fc-abtn" type="submit">apply</button>
            <a class="fc-abtn fc-abtn--neutral" href="{{ $reset }}" style="text-decoration:none;">reset</a>
        </div>
    </form>
</div>
