@extends('admin.layouts.auth')

@section('title', 'Admin Login')

@section('content')
    <div class="text-center mt-[10px] mb-[25px] text-[44px] font-bold tracking-[-1px] max-md:text-[32px]">
        <span class="text-brand">Online</span><span class="text-ink">Petition</span>
    </div>

    @if (session('success'))
        <x-blatui.alert tone="success" class="mb-4">{{ session('success') }}</x-blatui.alert>
    @endif

    @if ($errors->any())
        <x-blatui.alert variant="destructive" class="mb-4">{{ $errors->first() }}</x-blatui.alert>
    @endif

    <x-blatui.card variant="sectioned" class="max-w-[400px] mx-auto">
        <x-blatui.card-header>
            <x-blatui.card-title>Login</x-blatui.card-title>
        </x-blatui.card-header>

        <x-blatui.card-content>
            <form method="POST" action="{{ route('admin.login.post') }}" class="flex flex-col gap-5">
                @csrf

                <x-blatui.field data-invalid="{{ $errors->has('login') ? 'true' : 'false' }}">
                    <x-blatui.field-label for="login">Email or Username</x-blatui.field-label>
                    <x-blatui.input id="login" name="login" :value="old('login')" autocomplete="username" autofocus required />
                    @error('login')
                        <x-blatui.field-error>{{ $message }}</x-blatui.field-error>
                    @enderror
                </x-blatui.field>

                <x-blatui.field data-invalid="{{ $errors->has('password') ? 'true' : 'false' }}">
                    <x-blatui.field-label for="password">Password</x-blatui.field-label>
                    <x-blatui.input id="password" type="password" name="password" autocomplete="current-password" required />
                    @error('password')
                        <x-blatui.field-error>{{ $message }}</x-blatui.field-error>
                    @enderror
                </x-blatui.field>

                <div class="flex items-center justify-between gap-3">
                    <label class="flex items-center gap-1.5 text-sm select-none">
                        <input type="checkbox" name="remember" value="1">
                        stay logged in
                    </label>

                    <x-blatui.button type="submit" id="btn-login-submit">sign in »</x-blatui.button>
                </div>
            </form>
        </x-blatui.card-content>
    </x-blatui.card>

    @push('scripts')
        <script>
            (function () {
                const form = document.querySelector('form[action="{{ route('admin.login.post') }}"]');
                const btn = document.getElementById('btn-login-submit');
                if (!form || !btn) return;
                form.addEventListener('submit', function () {
                    btn.disabled = true;
                    btn.textContent = 'signing in...';
                });
            })();
        </script>
    @endpush
@endsection
