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
                    <x-ui.card>
                        <div class="mb-4">
                            <h2 class="relative! capitalize! text-lg! pb-2.5! mb-5! font-semibold! after:absolute! after:content-['']! after:h-px! after:bg-[linear-gradient(to_right,#000_30%,#ce2020_30%)]! after:w-full! after:left-0! after:bottom-0!">{{ __('contacts.title') }}</h2>

                            @if ($errors->any())
                                <x-ui.alert variant="error">
                                    {{ $errors->first() }}
                                </x-ui.alert>
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
                                        <x-ui.input
                                            id="contact_name"
                                            name="name"
                                            type="text"
                                            :label="__('contacts.name_label')"
                                            :placeholder="__('contacts.name_placeholder')"
                                            :value="old('name')"
                                            :required="true" />
                                    </div>

                                    <div class="mb-3">
                                        <x-ui.input
                                            id="contact_email"
                                            name="email"
                                            type="email"
                                            :label="__('contacts.email_label')"
                                            :placeholder="__('contacts.email_placeholder')"
                                            :value="old('email')"
                                            :required="true" />
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
                    </x-ui.card>
                </div>
            </div>
        </div>
    </section>
@endsection
