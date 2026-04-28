@extends('layouts.legacy')

@section('title', __('messages.contacts.title') . ' - xPetition')
@section('body_class', 'contacts')

@section('content')
    <section class="breadcrumb-section">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <h2 class="page-titles">{{ __('messages.contacts.title') }}</h2>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb m-0 p-0">
                            <li class="breadcrumb-item">
                                <a href="{{ lroute('home') }}">{{ __('messages.contacts.home') }}</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">{{ __('messages.contacts.title') }}</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </section>

    <section class="petitions-list py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-5">
                    <div class="card">
                        <div class="card-body">
                            <div class="mb-4">
                                <h4 class="headings">{{ __('messages.contacts.title') }}</h4>

                                @if (session('success'))
                                    <div class="alert alert-success">
                                        {{ session('success') }}
                                    </div>
                                @endif

                                <form method="post" action="{{ route('contacts.submit', app()->getLocale()) }}">
                                    @csrf

                                    <fieldset>
                                        <input type="hidden" name="__contacts" value="1" />

                                        <div class="mb-3">
                                            <label for="contact_name" class="form-label">{{ __('messages.contacts.name_label') }}</label>
                                            <input id="contact_name" type="text" name="name" class="form-control" placeholder="{{ __('messages.contacts.name_placeholder') }}"
                                                value="" />
                                        </div>

                                        <div class="mb-3">
                                            <label for="contact_email" class="form-label">{{ __('messages.contacts.email_label') }}</label>
                                            <input id="contact_email" type="email" name="email" class="form-control" placeholder="{{ __('messages.contacts.email_placeholder') }}"
                                                value="" />
                                        </div>

                                        <div class="mb-3">
                                            <label for="contact_text" class="form-label">{{ __('messages.contacts.text_label') }}</label>
                                            <textarea id="contact_text" name="text" class="form-control" rows="5"
                                                placeholder="{{ __('messages.contacts.text_placeholder') }}"></textarea>
                                        </div>

                                        <div class="mb-3">
                                            <input type="submit" class="btn btn-primary" value="{{ __('messages.contacts.submit') }}">
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
