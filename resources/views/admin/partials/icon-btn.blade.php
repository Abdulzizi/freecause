@php
    $w = $w ?? 52;
    $h = $h ?? 44;
@endphp

<button type="{{ $type ?? 'button' }}"
    @if(!empty($id)) id="{{ $id }}" @endif
    @if(!empty($class)) class="{{ $class }}" @endif
    @if(!empty($data)) @foreach($data as $k => $v) data-{{ $k }}="{{ $v }}" @endforeach @endif
    title="{{ $title ?? '' }}"
    style="width:{{ $w }}px; height:{{ $h }}px; border:1px solid #bbb; background:#fff; padding:0; display:flex; flex-direction:column; align-items:center; justify-content:center; gap:2px; cursor:pointer;">

    <div style="line-height:0;">
        {!! $icon !!}
    </div>

    @if(!empty($label))
        <div style="font-size:10px; color:#666; line-height:1;">
            {{ $label }}
        </div>
    @endif
</button>
