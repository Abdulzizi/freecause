@extends('layouts.legacy')

@section('title', 'Magazine - xPetition')
@section('body_class', 'magazine')

@section('content')
    <section class="breadcrumb-section">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <h2 class="page-titles">Magazine</h2>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb m-0 p-0">
                            <li class="breadcrumb-item">
                                <a href="{{ lroute('home') }}">Home</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Magazine</li>
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
                            <strong>Not implemented yet.</strong>
                            This section is under development and will be available soon.
                        </div>
                    </div>
                    <div class="text-center py-4">
                        <h3 class="mb-3" style="font-weight:600;">Coming Soon</h3>
                        <p class="text-muted" style="font-size:16px;">
                            The xPetition magazine is under construction.<br>
                            Check back soon for articles, guides, and stories about activism and change.
                        </p>
                        <a href="{{ lroute('home') }}" class="btn btn-danger mt-3">Back to Home</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
