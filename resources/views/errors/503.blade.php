@php
    $locale = 'en';
@endphp

@extends('layouts.legacy')

@section('title', 'Maintenance - xPetition')

@section('content')
    <section class="py-5">
        <div class="container">

            <div class="mb-4">
                <h1 class="mb-2" style="font-size:24px; font-weight:600;">Under Maintenance</h1>
            </div>

            <div class="bg-white shadow-sm rounded-3 p-4 mb-5" style="border:1px solid #eee;">
                <div class="mb-2 headings">503</div>
                <p style="font-size:15px; color:#555; margin-bottom:6px;">
                    xPetition is currently undergoing scheduled maintenance.
                </p>
                <p style="font-size:15px; color:#555; margin-bottom:20px;">
                    We will be back shortly. Thank you for your patience.
                </p>
                <a class="btn btn-danger" href="/{{ $locale }}">Try Again</a>
            </div>

        </div>
    </section>
@endsection
