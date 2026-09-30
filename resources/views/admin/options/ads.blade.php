@extends('admin.layouts.app')

@section('title', 'ads.txt')

@section('content')
    <h1 class="m-0! mb-2! text-lg! max-md:text-base!">ads.txt</h1>

    {{-- @if(session('success'))
        <div class="fc-success">{{ session('success') }}</div>
    @endif --}}

    <form method="post" action="{{ route('admin.ads.update') }}">
        @csrf

        <div class="border! border-[#9e9e9e]! rounded! p-2.5! mb-2.5!">
            <textarea class="w-full! box-border! border! border-[#bdbdbd]! rounded! py-1.5! px-2! text-sm! bg-white! min-h-[140px]! resize-y! font-mono! text-[13px]! h-[360px]!" name="ads_txt">{{ $ads_txt }}</textarea>
        </div>

        <div class="flex! justify-end! mt-1.5!">
            <button class="border! border-[#7d7d7d]! bg-[#e6e6e6]! px-2.5! py-1! rounded-[3px]! cursor-pointer! hover:bg-[#dcdcdc]! max-md:text-xs! max-md:px-3! max-md:py-1.5!" type="submit" title="save">save</button>
        </div>
    </form>
@endsection
