@extends('layouts.legacy')

@section('title', __('auth.verify_done_title'))

@section('content')
    <section class="fc-section">
        <x-ui.container>
            <div class="flex flex-wrap -mx-3 justify-center">
                <div class="w-full shrink-0 grow-0 px-3 md:w-2/3 lg:w-1/2">
                    <div class="relative flex flex-col min-w-0 bg-surface bg-clip-border rounded-lg shadow-elev-1">
                        <div class="grow shrink basis-auto p-6 md:p-12 text-center">
                            <h1 class="mb-4 text-2xl font-medium">{{ __('auth.verify_done_heading') }}</h1>

                            <p class="text-muted mb-6">{{ __('auth.verify_done_body') }}</p>

                            <x-ui.button variant="danger" :href="lroute('login')" class="w-full max-md:min-h-12">
                                {{ __('auth.verify_done_login_btn') }}
                            </x-ui.button>

                            <div class="mt-4">
                                <a href="{{ lroute('home') }}" class="text-muted">{{ __('auth.verify_done_home') }}</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </x-ui.container>
    </section>
@endsection
