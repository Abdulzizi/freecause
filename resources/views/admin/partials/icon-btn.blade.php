@php
    $w = $w ?? 52;
    $h = $h ?? 44;
@endphp

<button
    type="{{ $type ?? 'button' }}"
    @if(!empty($id)) id="{{ $id }}" @endif
    @if(!empty($class)) @else @endif
    @if(!empty($data)) @foreach($data as $k => $v) data-{{ $k }}="{{ $v }}" @endforeach @endif
    title="{{ $title ?? '' }}"
    style="width:{{ $w }}px; height:{{ $h }}px;">

    <div>
        {!! $icon !!}
    </div>

    @if(!empty($label))
        <div>
            {{ $label }}
        </div class="fc-abtn">
    @endif
</button>
