@extends('admin.layouts.auth')

@section('title', 'Admin Login')

@section('content')
    @push('head')
        <style>
            .btn-login[disabled] { opacity: .6; cursor: wait; }
            .btn-login .spinner {
                display: inline-block;
                width: 10px;
                height: 10px;
                border: 2px solid #a7b6da;
                border-top-color: transparent;
                border-radius: 50%;
                animation: fcspin .7s linear infinite;
                margin-right: 6px;
                vertical-align: -1px;
            }
            @keyframes fcspin { to { transform: rotate(360deg); } }
        </style>
    @endpush

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

            <div class="field-label">Email or Username</div>
            <input class="field-input" type="text" name="login" value="{{ old('login') }}" autocomplete="username" required>

            <div class="field-label">Password</div>
            <input class="field-input" type="password" name="password" autocomplete="current-password" required>

            <div class="row-actions">
                <label class="remember">
                    <input type="checkbox" name="remember" value="1">
                    stay logged in
                </label>

                <button class="btn-login" type="submit" id="btn-login-submit">
                    sign in »
                </button>
            </div>
        </form>
    </div>

    @push('scripts')
        <script>
            (function () {
                const form = document.querySelector('form[method="POST"]');
                const btn = document.getElementById('btn-login-submit');
                if (!form || !btn) {
                    return;
                }
                let submitting = false;
                form.addEventListener('submit', function () {
                    if (submitting) {
                        return;
                    }
                    submitting = true;
                    btn.disabled = true;
                    btn.innerHTML = '<span class="spinner"></span>signing in...';
                });
            })();
        </script>
    @endpush
@endsection
