@extends('layouts.legacy')

@section('title', 'Contacts - FreeCause - Online Petition')
@section('body_class', 'contacts')

@section('content')
    <section class="breadcrumb-section">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <h2 class="page-titles">Contacts</h2>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb m-0 p-0">
                            <li class="breadcrumb-item">
                                <a href="{{ lroute('home') }}">Home</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Contacts</li>
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
                                <h4 class="headings">Contacts</h4>

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
                                            <p class="mb-1 fs-14">Name (mandatory)</p>
                                            <input type="text" name="name" class="form-control" placeholder="Name"
                                                value="" />
                                        </div>

                                        <div class="mb-3">
                                            <p class="mb-1 fs-14">Email (mandatory)</p>
                                            <input type="text" name="email" class="form-control" placeholder="Email"
                                                value="" />
                                        </div>

                                        <div class="mb-3">
                                            <p class="mb-1 fs-14">Text (mandatory)</p>
                                            <textarea name="text" class="form-control" rows="5"
                                                placeholder="Text"></textarea>
                                        </div>

                                        {{-- reCAPTCHA removed for local UI parity (it will fail without valid keys/domain)
                                        --}}
                                        {{-- <div style="padding:10px 0;">
                                            <div class="g-recaptcha" data-sitekey="..."></div>
                                        </div> --}}

                                        <div class="mb-3">
                                            <input type="submit" class="btn btn-primary" value="Submit">
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
