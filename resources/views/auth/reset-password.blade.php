@extends('layouts.legacy')

@section('title', __('auth.reset_title'))

@section('content')
    <section class="py-12!">
        <div class="mx-auto! w-full! px-3! max-w-[540px]! md:max-w-[720px]! lg:max-w-[960px]! xl:max-w-[1140px]! 2xl:max-w-[1320px]!">
            <div class="flex! flex-wrap! -mx-3! justify-center!">
                <div class="w-full! shrink-0! grow-0! px-3! md:w-[41.666667%]!">
                    <div class="shadow-sm">
                        <h1 class="mb-6! text-center! text-2xl!">{{ __('auth.reset_title') }}</h1>

                        <form method="POST" action="{{ lroute('password.update') }}">
                            @csrf

                            <input type="hidden" name="token" value="{{ $token }}">

                            <div class="mb-4!">
                                <x-ui.input
                                    type="email"
                                    id="reset_email"
                                    name="email"
                                    :label="__('auth.email')"
                                    :value="old('email')"
                                    :required="true"
                                    :error="$errors->first('email')"
                                />
                            </div>

                            <div class="mb-4!">
                                <x-ui.input
                                    type="password"
                                    id="reset_password"
                                    name="password"
                                    :label="__('auth.new_password')"
                                    :required="true"
                                    :error="$errors->first('password')"
                                />
                            </div>

                            <div class="mb-4!">
                                <x-ui.input
                                    type="password"
                                    id="reset_password_confirmation"
                                    name="password_confirmation"
                                    :label="__('auth.confirm_password')"
                                    :required="true"
                                />
                            </div>

                            <x-ui.button type="submit" variant="danger" class="w-full!">{{ __('auth.reset_submit') }}</x-ui.button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
