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

    <div class="text-center! mt-[10px]! mb-[25px]! text-[44px]! font-bold! tracking-[-1px]! max-md:text-[32px]!">
        <span class="text-brand!">Online</span><span class="text-ink!">Petition</span>
    </div>

    @if (session('success'))
        <div class="w-[320px]! mx-auto! mb-3! border! border-[#b8d7b3]! bg-[#f2fff2]! text-success! p-2.5! px-3! rounded-[3px]! text-[13px]!">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="w-[320px]! mx-auto! mb-3! border! border-[#d7b3b3]! bg-[#fff2f2]! text-danger! p-2.5! px-3! rounded-[3px]! text-[13px]!">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="w-[320px]! mx-auto! rounded! border! border-[#3f66c7]! bg-gradient-to-b! from-[#86a9ff]! to-[#1f3f96]! shadow-[0_4px_12px_rgba(0,0,0,0.15)]! p-4!">
        <div class="text-center! text-[#e9f0ff]! text-[34px]! font-extrabold! tracking-[6px]! [text-shadow:0_1px_0_rgba(0,0,0,0.25)]! my-1! mb-3!">LOGIN</div>

        <form method="POST" action="{{ route('admin.login.post') }}">
            @csrf

            <div class="text-[#e9f0ff]! text-xs! mt-2.5! mb-1!">Email or Username</div>
            <input class="w-full! box-border! py-2! px-2.5! rounded-sm! border! border-[#9bb2e8]! outline-none! bg-surface!" type="text" name="login" value="{{ old('login') }}" autocomplete="username" required>

            <div class="text-[#e9f0ff]! text-xs! mt-2.5! mb-1!">Password</div>
            <input class="w-full! box-border! py-2! px-2.5! rounded-sm! border! border-[#9bb2e8]! outline-none! bg-surface!" type="password" name="password" autocomplete="current-password" required>

            <div class="flex! items-center! justify-between! gap-2.5! mt-3!">
                <label class="text-[#e9f0ff]! text-xs! flex! items-center! gap-1.5! select-none!">
                    <input type="checkbox" name="remember" value="1">
                    stay logged in
                </label>

                <x-ui.button variant="secondary" size="sm" class="btn-login border! border-[#a7b6da]! bg-[#f2f2f2]! px-2.5! py-1.5! rounded-sm! cursor-pointer! text-xs! font-bold!" type="submit" id="btn-login-submit">
                    sign in »
                </x-ui.button>
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
