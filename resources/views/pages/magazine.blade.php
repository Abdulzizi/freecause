@extends('layouts.legacy')

@section('title', __('nav.magazine').' - FreeCause')
@section('body_class', 'magazine')

@section('content')
    <section class="bg-[#fbfbfb]! py-[30px]!">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <h1 class="text-xl! mb-[5px]!">{{ __('nav.magazine') }}</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb m-0 p-0">
                            <li class="breadcrumb-item">
                                <a href="{{ lroute('home') }}">{{ __('nav.home') }}</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">{{ __('nav.magazine_breadcrumb') }}</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </section>

    <section class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8 py-3">
                    <div class="alert alert-warning d-flex align-items-center gap-2 mb-4" role="alert">
                        <div>
                            <strong>{{ __('nav.magazine_notice_title') }}</strong>
                            {{ __('nav.magazine_notice_body') }}
                        </div>
                    </div>
                    <div class="text-center py-4">
                        <h2 class="mb-3 font-semibold!">{{ __('nav.magazine_coming_soon') }}</h2>
                        <p class="text-muted text-base!">
                            {{ __('nav.magazine_body') }}
                        </p>
                        <a href="{{ lroute('home') }}" class="btn btn-danger mt-3">{{ __('nav.magazine_back_home') }}</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
