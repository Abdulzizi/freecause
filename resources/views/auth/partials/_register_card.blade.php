
@php($isRegisterSubmit = old('auth_form') === 'register')
<div class="bg-white! rounded-[.5rem]! shadow-sm! p-4!">
    @include('auth.partials._social_box', ['redirect' => $redirect])

    <div class="mb-4!">
        <div class="font-semibold! text-[22px]!">{{ __('auth.register') }}</div>
    </div>

    @if (old('auth_form') === 'register' && $errors->any())
        <x-ui.alert variant="error" class="mb-4!">
            <ul class="mb-0!">
                @foreach ($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </x-ui.alert>
    @endif

    <form method="POST" action="{{ lroute('register.post') }}">
        @csrf
        <input type="hidden" name="auth_form" value="register">
        <input type="hidden" name="redirect" value="{{ $redirect }}">

        <div class="mb-4!">
            <x-ui.input
                type="text"
                name="name"
                :label="__('auth.name')"
                :value="old('name')"
                :required="true"
                :error="$isRegisterSubmit ? $errors->first('name') : null"
            />
        </div>

        <div class="mb-4!">
            <x-ui.input
                type="text"
                name="surname"
                :label="__('auth.surname')"
                :value="old('surname')"
                :required="true"
                :error="$isRegisterSubmit ? $errors->first('surname') : null"
            />
        </div>

        <div class="mb-4!">
            <x-ui.input
                type="email"
                name="email"
                :label="__('auth.email')"
                :value="old('email')"
                :required="true"
                :error="$isRegisterSubmit ? $errors->first('email') : null"
                data-email-suggest
            />
        </div>

        <div class="mb-4!">
            <x-ui.input
                type="password"
                name="password"
                :label="__('auth.password')"
                :required="true"
                :error="$isRegisterSubmit ? $errors->first('password') : null"
                data-strength
                data-strength-labels='["Very weak","Weak","Fair","Good","Strong"]'
            />
        </div>

        <div class="mb-4! text-[14px]!">
            {{ __('auth.nickname_hint') }}
        </div>

        <div class="mb-4!">
            <x-ui.input
                type="text"
                name="nickname"
                :label="__('sign.nickname')"
                :value="old('nickname')"
                :error="$isRegisterSubmit ? $errors->first('nickname') : null"
            />
        </div>

        <div class="mb-4!">
            <x-ui.input
                type="text"
                name="city"
                :label="__('sign.city')"
                :value="old('city')"
                :error="$isRegisterSubmit ? $errors->first('city') : null"
            />
        </div>

        <div class="block! min-h-6! pl-6! mb-4!">
            <input class="form-check-input" type="checkbox" name="agree_terms" id="agree_terms" {{ old('agree_terms') ? 'checked' : '' }}>
            <label class="form-check-label" for="agree_terms">
                <span class="text-brand!">{{ __('auth.accept_terms') }}</span>
            </label>
        </div>

        <x-ui.button type="submit" variant="danger" class="px-6! rounded-[6px]!">{{ __('auth.submit') }}</x-ui.button>
    </form>
</div>
