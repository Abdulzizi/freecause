@extends('layouts.legacy')

@php
    $pageTitle = __('sign.page_title', ['title' => $tr->title ?? __('common.petition')]);

    $h2Line1 = __('sign.h2_line1');
    $h2Line2 = __('sign.h2_line2');
    $btnText = __('sign.btn');

    $signUrl = isset($tr) && $tr ? lroute('petition.sign', ['slug' => $tr->slug, 'id' => $petition->id]) : '#';

    $signData = session('sign.'.$petition->id, []);
@endphp

@section('title', $pageTitle)

@section('content')
    <section class="py-14! md:py-24!">
        <x-ui.container>
            <div class="bg-white! shadow-sm! rounded-[.5rem]! p-12! text-center! border! border-border! max-w-[900px]! mx-auto!">

                <h1 class="mb-6! text-[1.3rem]! md:text-[28px]! font-medium! break-words! [overflow-wrap:anywhere]!">
                    {{ $h2Line1 }}<br>
                    {{ $h2Line2 }}
                </h1>

                <form action="{{ $signUrl }}" method="POST" data-sign-busy class="inline-block!">
                    @csrf
                    <x-ui.alert variant="error" class="mt-2!" data-sign-network-error hidden>{{ __('sign.connection_error') }}</x-ui.alert>

                    @if ($errors->any())
                        <x-ui.alert variant="error" class="text-start! mt-2!">
                            <ul class="mb-0!">
                                @foreach ($errors->all() as $e)
                                    <li>{{ $e }}</li>
                                @endforeach
                            </ul>
                        </x-ui.alert>
                    @endif

                    <input type="hidden" name="agree1" value="{{ data_get($signData, 'agree1', '1') }}">
                    <input type="hidden" name="agree2" value="{{ data_get($signData, 'agree2', '1') }}">
                    <input type="hidden" name="agree3" value="{{ data_get($signData, 'agree3', '1') }}">

                    <input type="hidden" name="comment" value="{{ data_get($signData, 'comment', __('sign.default_comment')) }}">
                    <button class="inline-flex! items-center! justify-center! gap-2! bg-brand! text-white! border! border-brand! rounded-[0.625rem]! px-5! py-3! text-[22px]! font-semibold! no-underline! hover:bg-[#b31b1b]! max-md:w-full! max-md:text-[18px]! max-md:px-4! max-md:py-2.5!" type="submit">
                        {{ $btnText }} <span>»</span>
                    </button>
                </form>
            </div>
        </x-ui.container>
    </section>
@endsection
