@extends('layouts.legacy')

@section('title', __('contacts.title') . ' - FreeCause')
@section('body_class', 'contacts')

@section('content')
    <section class="bg-[#fbfbfb]! py-[30px]!">
        <div class="mx-auto! w-full! px-3! max-w-[540px]! md:max-w-[720px]! lg:max-w-[960px]! xl:max-w-[1140px]! 2xl:max-w-[1320px]!">
            <div class="flex! flex-wrap! -mx-3!">
                <div class="w-full! shrink-0! grow-0! px-3!">
                    <h1 class="text-xl! mb-[5px]!">{{ __('contacts.title') }}</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb m-0! p-0!">
                            <li class="breadcrumb-item">
                                <a href="{{ lroute('home') }}">{{ __('contacts.home') }}</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">{{ __('contacts.title') }}</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </section>

    <section class="py-12!">
        <div class="mx-auto! w-full! px-3! max-w-[540px]! md:max-w-[720px]! lg:max-w-[960px]! xl:max-w-[1140px]! 2xl:max-w-[1320px]!">
            <div class="flex! flex-wrap! -mx-3! justify-center!">
                <div class="w-full! md:w-[41.666667%]! shrink-0! grow-0! px-3!">
                    <x-ui.card>
                        <div class="mb-6!">
                            <h2 class="relative! capitalize! text-lg! pb-2.5! mb-5! font-semibold! after:absolute! after:content-['']! after:h-px! after:bg-[linear-gradient(to_right,#000_30%,#ce2020_30%)]! after:w-full! after:left-0! after:bottom-0!">{{ __('contacts.title') }}</h2>

                            @if ($errors->any())
                                <x-ui.alert variant="error">
                                    {{ $errors->first() }}
                                </x-ui.alert>
                            @endif

                            <form method="post" action="{{ route('contacts.submit', app()->getLocale()) }}"
                                id="contactForm"
                                data-sending="{{ __('contacts.sending') }}"
                                data-sent="{{ __('contacts.sent') }}"
                                data-failed="{{ __('contacts.failed') }}">
                                @csrf

                                <fieldset>
                                    <input type="hidden" name="__contacts" value="1" />

                                    <div class="mb-4!">
                                        <x-ui.input
                                            id="contact_name"
                                            name="name"
                                            type="text"
                                            :label="__('contacts.name_label')"
                                            :placeholder="__('contacts.name_placeholder')"
                                            :value="old('name')"
                                            :required="true" />
                                    </div>

                                    <div class="mb-4!">
                                        <x-ui.input
                                            id="contact_email"
                                            name="email"
                                            type="email"
                                            :label="__('contacts.email_label')"
                                            :placeholder="__('contacts.email_placeholder')"
                                            :value="old('email')"
                                            :required="true" />
                                    </div>

                                    <div class="mb-4!">
                                        <label for="contact_text" class="block! mb-2!">{{ __('contacts.text_label') }}</label>
                                        <textarea id="contact_text" name="text" class="block! w-full! px-3! py-1.5! text-base! leading-normal! text-[#212529]! bg-white! border! border-[#ced4da]! rounded-md! appearance-none! focus:border-[#86b7fe]! focus:outline-none! focus:ring-4! focus:ring-[#0d6efd]/25!" rows="5"
                                            placeholder="{{ __('contacts.text_placeholder') }}" required>{{ old('text') }}</textarea>
                                    </div>

                                    <div class="mb-4!">
                                        <input type="submit" class="inline-flex! items-center! justify-center! bg-[#cc1f1f]! text-white! border! border-[#cc1f1f]! rounded-[0.625rem]! px-4! py-2! font-semibold! text-[0.9375rem]! cursor-pointer! no-underline! hover:bg-[#b31b1b]!" value="{{ __('contacts.submit') }}">
                                    </div>
                                </fieldset>
                            </form>

                        </div>
                    </x-ui.card>
                </div>
            </div>
        </div>
    </section>
@endsection
