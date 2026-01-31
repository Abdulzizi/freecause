@extends('layouts.legacy')

@section('title', 'Thank you for having signed - FreeCause')

@section('content')
    <section class="py-5">
        <div class="container">
            <div class="bg-white shadow-sm rounded-3 p-4" style="border:1px solid #eee; max-width: 820px; margin: 0 auto;">
                <h1 class="mb-3" style="font-size:28px;">Thank you for having signed:</h1>

                <div class="mb-3">
                    <a class="red"
                        href="{{ route('petition.show', ['locale' => $locale, 'slug' => $petition->slug, 'id' => $petition->id]) }}">
                        {{ $petition->title }}
                    </a>
                </div>

                <p class="mb-4" style="font-size:15px;">
                    Registration has been successful, however you still have to activate your account by clicking a link
                    you'll receive
                    soon at the supplied email address.
                </p>

                <h2 class="mb-3" style="font-size:22px;">Petitions you might like</h2>

                <div class="mb-4">
                    @foreach($suggestions as $p)
                        <div class="mb-2">
                            <a class="d-block p-2" style="border:1px solid #f0caca; border-radius:4px; background:#fff6f6;"
                                href="{{ route('petition.show', ['locale' => $locale, 'slug' => $p->slug, 'id' => $p->id]) }}">
                                {{ $p->title }}
                            </a>
                        </div>
                    @endforeach
                </div>

                <a class="btn btn-danger" href="#">Invite friends from your address book »</a>
            </div>
        </div>
    </section>
@endsection
