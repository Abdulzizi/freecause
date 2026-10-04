@extends('layouts.legacy')

@section('title', $user->display_name . ' - FreeCause')

@section('content')

    <section class="py-12!">
        <div class="mx-auto! w-full! px-3! max-w-[540px]! md:max-w-[720px]! lg:max-w-[960px]! xl:max-w-[1140px]! 2xl:max-w-[1320px]!">

            <div class="mb-6!">
                <h1 class="mb-2! text-2xl! font-semibold! break-words!">
                    {{ $user->display_name }}
                </h1>
                <div class="text-sm!">
                    <a class="text-brand!" href="/{{ app()->getLocale() }}">{{ __('userprofile.home') }}</a>
                    <span class="text-[#6c757d]!"> / </span>
                    <span class="text-[#6c757d]! break-words!">{{ $user->display_name }}</span>
                </div>
            </div>

            <div class="bg-white! shadow-sm! rounded-lg! p-4! border! border-[#eee]!">

                <div class="relative! capitalize! text-lg! pb-2.5! mb-5! font-semibold! after:absolute! after:content-['']! after:h-px! after:bg-[linear-gradient(to_right,#000_30%,#ce2020_30%)]! after:w-full! after:left-0! after:bottom-0! font-bold! break-words!">{{ $user->display_name }}</div>

                <div class="flex! flex-wrap! -mx-3!">

                    <div class="w-full! md:w-1/3! shrink-0! grow-0! px-3!">
                        <table class="w-full! text-sm! border-collapse!">
                            <tr class="border-b! border-[#eee]!">
                                <td class="py-2.5! text-[#555]!">{{ __('userprofile.registration_date') }}</td>
                                <td class="py-2.5! text-right! font-medium!">
                                    {{ optional($user->created_at)->format('n/j/y H:i') }}
                                </td>
                            </tr>
                            <tr class="border-b! border-[#eee]!">
                                <td class="py-2.5! text-[#555]!">{{ __('userprofile.petitions') }}</td>
                                <td class="py-2.5! text-right! font-medium!">
                                    {{ $petitionCount }}
                                </td>
                            </tr>
                            <tr>
                                <td class="py-2.5! text-[#555]!">{{ __('userprofile.signatures') }}</td>
                                <td class="py-2.5! text-right! font-medium!">
                                    {{ $signatureCount }}
                                </td>
                            </tr>
                        </table>
                    </div>

                    <div class="w-full! md:w-2/3! shrink-0! grow-0! px-3!">

                        <div class="mb-6!">
                            <h2 class="text-base! font-bold! mb-4! break-words!">
                                {{ __('userprofile.recent_petitions', ['name' => $user->display_name]) }}
                            </h2>

                            @forelse ($recentPetitions as $p)
                                <div class="py-1.5! border-b! border-[#f2f2f2]! text-sm!">
                                    <a class="text-brand! break-words!" href="{{ route('petition.show', ['slug' => $p->slug, 'id' => $p->id]) }}">
                                        {{ $p->title }}
                                    </a>
                                </div>
                            @empty
                                <div class="text-[13px]! text-[#999]!">{{ __('userprofile.no_petitions') }}</div>
                            @endforelse
                        </div>

                        <div>
                            <h2 class="text-base! font-bold! mb-4! break-words!">
                                {{ __('userprofile.recent_signatures', ['name' => $user->display_name]) }}
                            </h2>

                            @forelse ($recentSignatures as $s)
                                <div class="py-1.5! border-b! border-[#f2f2f2]! text-sm!">
                                    <a class="text-brand! break-words!" href="{{ route('petition.show', ['slug' => $s->slug, 'id' => $s->petition_id]) }}">
                                        {{ $s->title }}
                                    </a>
                                </div>
                            @empty
                                <div class="text-[13px]! text-[#999]!">{{ __('userprofile.no_signatures') }}</div>
                            @endforelse
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </section>

@endsection
