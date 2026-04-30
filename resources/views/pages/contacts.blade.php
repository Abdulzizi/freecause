@extends('layouts.legacy')

@section('title', trans_db('contacts.title') . ' - xPetition')
@section('body_class', 'contacts')

@section('content')
    <section class="breadcrumb-section">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <h2 class="page-titles">{{ trans_db('contacts.title') }}</h2>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb m-0 p-0">
                            <li class="breadcrumb-item">
                                <a href="{{ lroute('home') }}">{{ trans_db('contacts.home') }}</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">{{ trans_db('contacts.title') }}</li>
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
                                <h4 class="headings">{{ trans_db('contacts.title') }}</h4>

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
                                            <label for="contact_name" class="form-label">{{ trans_db('contacts.name_label') }}</label>
                                            <input id="contact_name" type="text" name="name" class="form-control" placeholder="{{ trans_db('contacts.name_placeholder') }}"
                                                value="" />
                                        </div>

                                        <div class="mb-3">
                                            <label for="contact_email" class="form-label">{{ trans_db('contacts.email_label') }}</label>
                                            <input id="contact_email" type="email" name="email" class="form-control" placeholder="{{ trans_db('contacts.email_placeholder') }}"
                                                value="" />
                                        </div>

                                        <div class="mb-3">
                                            <label for="contact_text" class="form-label">{{ trans_db('contacts.text_label') }}</label>
                                            <textarea id="contact_text" name="text" class="form-control" rows="5"
                                                placeholder="{{ trans_db('contacts.text_placeholder') }}"></textarea>
                                        </div>

                                        <div class="mb-3">
                                            <input type="submit" class="btn btn-primary" value="{{ trans_db('contacts.submit') }}">
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
