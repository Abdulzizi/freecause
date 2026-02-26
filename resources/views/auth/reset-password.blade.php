@extends('layouts.legacy')

@section('title', 'Reset Password')

@section('content')
    <section class="py-5">
        <div class="container">
            <h3>Reset Password</h3>

            <form method="POST" action="{{ lroute('password.update') }}">
                @csrf

                <input type="hidden" name="token" value="{{ $token }}">

                <div class="mb-3">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label>New Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label>Confirm Password</label>
                    <input type="password" name="password_confirmation" class="form-control" required>
                </div>

                <button class="btn btn-danger">Reset Password</button>
            </form>
        </div>
    </section>
@endsection
