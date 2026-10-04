@extends('layouts.legacy')

@section('title', __('auth.forgot_title'))

@section('content')
    <section class="py-12!">
        <div class="mx-auto! w-full! px-3! max-w-[540px]! md:max-w-[720px]! lg:max-w-[960px]! xl:max-w-[1140px]! 2xl:max-w-[1320px]!">
            <div class="flex! flex-wrap! -mx-3! justify-center!">
                <div class="w-full! shrink-0! grow-0! px-3! md:w-[41.666667%]!">
                    <div class="shadow-sm">
                        <h1 class="mb-6! text-center! text-2xl!">{{ __('auth.forgot_title') }}</h1>

                        @if (session('status'))
                            <x-ui.alert variant="success" class="mb-4!">{{ session('status') }}</x-ui.alert>
                        @endif

                        <form method="POST" action="{{ lroute('password.email') }}">
                            @csrf

                            <div class="mb-4!">
                                <x-ui.input
                                    type="email"
                                    id="forgot_email"
                                    name="email"
                                    :label="__('auth.email')"
                                    :value="old('email')"
                                    :required="true"
                                    :error="$errors->first('email')"
                                    autocomplete="email"
                                    autofocus
                                />
                            </div>

                            <x-ui.button type="submit" variant="danger" class="w-full!">{{ __('auth.send_reset') }}</x-ui.button>

                            <div class="text-center! mt-4!">
                                <a href="{{ lroute('login') }}" class="text-[#6c757d]! text-sm!">{{ __('auth.back_login') }}</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
