@extends('layouts.legacy')

@section('title', __('contacts.title') . ' - FreeCause')
@section('body_class', 'contacts')

@section('content')
    <section class="bg-[#fbfbfb]! py-[30px]!">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <h1 class="text-xl! mb-[5px]!">{{ __('contacts.title') }}</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb m-0 p-0">
                            <li class="breadcrumb-item">
                                <a href="{{ lroute('home') }}">{{ __('contacts.home') }}</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">{{ __('contacts.title') }}</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </section>

    <section class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-5">
                    <div class="card">
                        <div class="card-body">
                            <div class="mb-4">
                                <h2 class="relative! capitalize! text-lg! pb-2.5! mb-5! font-semibold! after:absolute! after:content-['']! after:h-px! after:bg-[linear-gradient(to_right,#000_30%,#ce2020_30%)]! after:w-full! after:left-0! after:bottom-0!">{{ __('contacts.title') }}</h2>

                                @if ($errors->any())
                                    <div class="alert alert-danger" role="alert">
                                        {{ $errors->first() }}
                                    </div>
                                @endif

                                <form method="post" action="{{ route('contacts.submit', app()->getLocale()) }}"
                                    id="contactForm"
                                    data-sending="{{ __('contacts.sending') }}"
                                    data-sent="{{ __('contacts.sent') }}"
                                    data-failed="{{ __('contacts.failed') }}">
                                    @csrf

                                    <fieldset>
                                        <input type="hidden" name="__contacts" value="1" />

                                        <div class="mb-3">
                                            <label for="contact_name" class="form-label">{{ __('contacts.name_label') }}</label>
                                            <input id="contact_name" type="text" name="name" class="form-control" placeholder="{{ __('contacts.name_placeholder') }}"
                                                value="{{ old('name') }}" required />
                                        </div>

                                        <div class="mb-3">
                                            <label for="contact_email" class="form-label">{{ __('contacts.email_label') }}</label>
                                            <input id="contact_email" type="email" name="email" class="form-control" placeholder="{{ __('contacts.email_placeholder') }}"
                                                value="{{ old('email') }}" required />
                                        </div>

                                        <div class="mb-3">
                                            <label for="contact_text" class="form-label">{{ __('contacts.text_label') }}</label>
                                            <textarea id="contact_text" name="text" class="form-control" rows="5"
                                                placeholder="{{ __('contacts.text_placeholder') }}" required>{{ old('text') }}</textarea>
                                        </div>

                                        <div class="mb-3">
                                            <input type="submit" class="btn btn-primary" value="{{ __('contacts.submit') }}">
                                        </div>
                                    </fieldset>
                                </form>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
