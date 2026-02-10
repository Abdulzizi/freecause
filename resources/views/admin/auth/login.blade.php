@extends('admin.layouts.auth')

@section('title', 'Admin Login')

@section('content')
    <div class="admin-logo">
        <span class="red">Online</span><span class="black">Petition</span>
    </div>

    @if (session('success'))
        <div class="alert success">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="login-box">
        <div class="login-title">LOGIN</div>

        <form method="POST" action="{{ route('admin.login.post') }}">
            @csrf

            <div class="field-label">Email</div>
            <input class="field-input" type="email" name="email" value="{{ old('email') }}" autocomplete="username"
                required>

            <div class="field-label">Password</div>
            <input class="field-input" type="password" name="password" autocomplete="current-password" required>

            <div class="row-actions">
                <label class="remember">
                    <input type="checkbox" name="remember" value="1">
                    stay logged in
                </label>

                <button class="btn-login" type="submit">
                    sign in »
                </button>
            </div>
        </form>
    </div>
@endsection
