@extends('layouts.legacy')

@section('title', 'Sign - ' . ($tr->title ?? 'Petition'))

@section('content')
    <section class="py-5">
        <div class="container">
            <div class="bg-white shadow-sm rounded-3 p-5 text-center"
                style="border:1px solid #eee; max-width:900px; margin:0 auto;">
                <h2 class="mb-4" style="font-size:28px; font-weight:500;">
                    Support and share your cause.<br>
                    Please click "like" button and sign the petition
                </h2>

                <form action="{{ route('petition.sign', ['locale' => $tr->locale, 'slug' => $tr->slug, 'id' => $petition->id]) }}"
                    method="POST" class="d-inline-block">
                    @csrf

                    <input type="hidden" name="agree1" value="agree">
                    <input type="hidden" name="agree2" value="agree">
                    <input type="hidden" name="agree3" value="agree">

                    <input type="hidden" name="comment" value="I support this petition">

                    <button class="btn btn-danger px-5 py-3" type="submit" style="font-size:22px;">
                        Sign <span style="margin-left:16px;">»</span>
                    </button>
                </form>
            </div>
        </div>
    </section>
@endsection
