@extends('layouts.legacy')

@php
    $pageTitle = __('sign.page_title', ['title' => $tr->title ?? __('common.petition')]);

    $h2Line1 = __('sign.h2_line1');
    $h2Line2 = __('sign.h2_line2');
    $btnText = __('sign.btn');

    $signUrl = isset($tr) && $tr ? lroute('petition.sign', ['slug' => $tr->slug, 'id' => $petition->id]) : '#';
@endphp

@section('title', $pageTitle)

@section('content')
    <section class="py-16!">
        <div class="container">
            <div class="bg-white shadow-sm rounded-3 p-5 text-center border border-[#eee]! max-w-[900px]! mx-auto!">

                <h1 class="mb-4 text-[1.3rem]! md:text-[28px]! font-medium!">
                    {{ $h2Line1 }}<br>
                    {{ $h2Line2 }}
                </h1>

                <form action="{{ $signUrl }}" method="POST" class="d-inline-block">
                    @csrf

                    <input type="hidden" name="agree1" value="{{ session('sign.agree1', '1') }}">
                    <input type="hidden" name="agree2" value="{{ session('sign.agree2', '1') }}">
                    <input type="hidden" name="agree3" value="{{ session('sign.agree3', '1') }}">

                    <input type="hidden" name="comment" value="{{ session('sign.comment', 'I support this petition') }}">
                    <button class="btn btn-danger px-5 py-3 text-[22px]!" type="submit">
                        {{ $btnText }} <span class="ml-4!">»</span>
                    </button>
                </form>
            </div>
        </div>
    </section>
@endsection
