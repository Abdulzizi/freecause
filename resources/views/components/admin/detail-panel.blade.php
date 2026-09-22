@props([
    'title' => 'detail',
    'closeRoute' => null,
])

<div class="fc-tab" style="margin-top:14px; display:flex; align-items:center; justify-content:space-between;">
    <span>{{ $title }}</span>
    @if ($closeRoute)
        <a href="{{ $closeRoute }}" class="fc-panel-close" title="Close panel" aria-label="Close panel">&times;</a>
    @endif
</div>
<div class="fc-box">
    {{ $slot }}
</div>
