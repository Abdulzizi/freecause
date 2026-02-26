@extends('layouts.legacy')

@section('title', 'Forgot Password')

@section('content')
    <section class="py-5">
        <div class="container">
            <h3>Forgot Password</h3>

            <form method="POST" action="{{ lroute('password.email') }}">
                @csrf

                <div class="mb-3">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>

                <button class="btn btn-danger">Send Reset Link</button>
            </form>
        </div>
    </section>
@endsection
