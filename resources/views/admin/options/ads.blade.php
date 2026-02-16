@extends('admin.layouts.app')

@section('title', 'ads.txt')

@section('content')
    <h1>ads.txt</h1>

    {{-- @if(session('success'))
        <div class="fc-success">{{ session('success') }}</div>
    @endif --}}

    <form method="post" action="{{ route('admin.ads.update') }}">
        @csrf

        <div class="fc-box">
            <textarea class="fc-textarea" name="ads_txt"
                style="width:100%; height:360px; font-family:monospace;">{{ $ads_txt }}</textarea>
        </div>

        <div style="display:flex; justify-content:flex-end; margin-top:6px;">
            <button class="fc-btn" type="submit" title="save">save</button>
        </div>
    </form>
@endsection
