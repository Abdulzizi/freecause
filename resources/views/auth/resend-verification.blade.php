@extends('layouts.legacy')

@section('title', 'Resend Verification')

@section('content')
    <section class="py-5">
        <div class="container">
            <h3>Resend Verification Email</h3>

            <form method="POST" action="{{ lroute('verification.resend') }}">
                @csrf

                <div class="mb-3">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>

                <button class="btn btn-danger">Resend Email</button>
            </form>
        </div>
    </section>
@endsection
