@extends('layouts.legacy')

@php
    $pageTitle = trans_db('sign.page_title', ['title' => $tr->title ?? 'Petition']);

    $h2Line1 = trans_db('sign.h2_line1');
    $h2Line2 = trans_db('sign.h2_line2');
    $btnText = trans_db('sign.btn');

    $signUrl = isset($tr) && $tr ? lroute('petition.sign', ['slug' => $tr->slug, 'id' => $petition->id]) : '#';
@endphp

@section('title', $pageTitle)

@section('content')
    <section class="py-5">
        <div class="container">
            <div class="bg-white shadow-sm rounded-3 p-5 text-center"
                style="border:1px solid #eee; max-width:900px; margin:0 auto;">

                <h2 class="mb-4" style="font-size:28px; font-weight:500;">
                    {{ $h2Line1 }}<br>
                    {{ $h2Line2 }}
                </h2>

                <form action="{{ $signUrl }}" method="POST" class="d-inline-block">
                    @csrf

                    <input type="hidden" name="agree1" value="{{ session('sign.agree1', 'agree') }}">
                    <input type="hidden" name="agree2" value="{{ session('sign.agree2', 'agree') }}">
                    <input type="hidden" name="agree3" value="{{ session('sign.agree3', 'agree') }}">

                    <input type="hidden" name="comment" value="{{ session('sign.comment', 'I support this petition') }}">

                    <button class="btn btn-danger px-5 py-3" type="submit" style="font-size:22px;">
                        {{ $btnText }} <span style="margin-left:16px;">»</span>
                    </button>
                </form>
            </div>
        </div>
    </section>
@endsection
