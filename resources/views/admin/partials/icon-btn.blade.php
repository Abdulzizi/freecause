@php
    $w = $w ?? 52;
    $h = $h ?? 44;
@endphp

<button type="{{ $type ?? 'button' }}"
    @if(!empty($id)) id="{{ $id }}" @endif
    @if(!empty($class)) class="border! border-[#bbb]! bg-white! p-0! flex! flex-col! items-center! justify-center! gap-0.5! cursor-pointer! transition-[background,opacity]! duration-150! hover:bg-[#f7f7f7]! active:bg-[#dcdcdc]! disabled:opacity-[0.35]! disabled:pointer-events-none! {{ $class }}" @else class="border! border-[#bbb]! bg-white! p-0! flex! flex-col! items-center! justify-center! gap-0.5! cursor-pointer! transition-[background,opacity]! duration-150! hover:bg-[#f7f7f7]! active:bg-[#dcdcdc]! disabled:opacity-[0.35]! disabled:pointer-events-none!" @endif
    @if(!empty($data)) @foreach($data as $k => $v) data-{{ $k }}="{{ $v }}" @endforeach @endif
    title="{{ $title ?? '' }}"
    style="width:{{ $w }}px; height:{{ $h }}px;">

    <div class="leading-[0]!">
        {!! $icon !!}
    </div>

    @if(!empty($label))
        <div class="text-[10px]! text-[#666]! leading-none!">
            {{ $label }}
        </div>
    @endif
</button>
