@extends('admin.layouts.app')

@section('title', 'ads.txt')

@section('content')
    <x-admin.page-header title="ads.txt" />

    {{-- @if(session('success'))
        <div class="fc-success">{{ session('success') }}</div>
    @endif --}}

    <form method="post" action="{{ route('admin.ads.update') }}">
        @csrf

        <div class="border! border-border! rounded! p-2.5! mb-2.5!">
            <textarea class="w-full! box-border! border! border-border! rounded! py-1.5! px-2! text-sm! bg-white! min-h-[140px]! resize-y! font-mono! text-[13px]! h-[360px]!" name="ads_txt">{{ $ads_txt }}</textarea>
        </div>

        <div class="flex! justify-end! mt-1.5!">
            <x-blatui.button type="submit" title="save">save</x-blatui.button>
        </div>
    </form>
@endsection
