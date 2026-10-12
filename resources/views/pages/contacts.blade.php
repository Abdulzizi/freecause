@extends('layouts.legacy')

@section('title', __('contacts.title'))
@section('body_class', 'contacts')

@section('content')
    <x-ui.page-head :band="true" :title="__('contacts.title')" :breadcrumb="[
        ['label' => 'Home', 'url' => lroute('home')],
        ['label' => __('contacts.title')],
    ]" />

    <x-ui.section>
            <div class="flex flex-wrap -mx-3">
                <div class="w-full shrink-0 grow-0 px-3">

                    <div class="fcui-card relative flex flex-col min-w-0 w-full max-w-[720px] mx-auto">
                        <div class="grow shrink basis-auto p-6">

                            <h2 class="fcui-section-title capitalize text-lg">{{ __('contacts.title') }}</h2>

                            <div class="mb-6">
                                {!! __('contacts.intro') !!}
                            </div>

                            @include('pages.partials.contacts-form')

                        </div>
                    </div>

                </div>
            </div>
    </x-ui.section>
@endsection