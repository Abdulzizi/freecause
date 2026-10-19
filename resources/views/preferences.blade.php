@extends('layouts.legacy')

@section('title', __('preferences.title'))

@section('content')
    <x-ui.page-head
        :title="__('preferences.title')"
        :lead="$state === 'preferences' ? __('preferences.lead') : null"
        :breadcrumb="[
            ['label' => 'Home', 'url' => lroute('home')],
            ['label' => __('preferences.title')],
        ]"
        :band="true"
    />

    <x-ui.section>
        {{-- ad: leaderboard, desktop only --}}
        <div class="mb-4 text-center hidden lg:block">
            <x-ads.slot name="leaderboard" />
        </div>
        {{-- ad: mobile banner, mobile only --}}
        <div class="mb-4 text-center lg:hidden">
            <x-ads.slot name="mobile" />
        </div>

        <div class="mx-auto max-w-3xl">
            @if ($state === 'unsubscribed')
                <x-ui.alert variant="success">
                    {{ __('preferences.unsubscribed') }}
                </x-ui.alert>

                <p class="mt-3 text-sm leading-relaxed text-muted">{{ __('preferences.unsubscribed_help') }}</p>

                <div class="mt-6 flex flex-col gap-3 sm:flex-row">
                    @auth
                        <x-ui.button :href="lroute('preferences.show')" class="max-md:min-h-12">
                            {{ __('preferences.manage_preferences') }}
                        </x-ui.button>
                    @else
                        <x-ui.button :href="lroute('login')" class="max-md:min-h-12">
                            {{ __('preferences.sign_in') }}
                        </x-ui.button>
                    @endauth

                    <x-ui.button :href="lroute('home')" variant="secondary" class="max-md:min-h-12">
                        {{ __('preferences.back_home') }}
                    </x-ui.button>
                </div>
            @elseif ($state === 'invalid')
                <x-ui.alert variant="warning">
                    {{ __('preferences.invalid_token') }}
                </x-ui.alert>

                <div class="mt-6 flex flex-col gap-3 sm:flex-row">
                    @auth
                        <x-ui.button :href="lroute('preferences.show')" class="max-md:min-h-12">
                            {{ __('preferences.manage_preferences') }}
                        </x-ui.button>
                    @else
                        <x-ui.button :href="lroute('login')" class="max-md:min-h-12">
                            {{ __('preferences.sign_in') }}
                        </x-ui.button>
                    @endauth

                    <x-ui.button :href="lroute('home')" variant="secondary" class="max-md:min-h-12">
                        {{ __('preferences.back_home') }}
                    </x-ui.button>
                </div>
            @else
                @if ($user->unsubscribed_at)
                    <x-ui.alert variant="info" class="mb-6">
                        {{ __('preferences.currently_unsubscribed') }}
                    </x-ui.alert>
                @endif

                <div class="rounded-lg bg-surface p-4 sm:p-6 shadow-elev-1">
                    <form method="POST" action="{{ lroute('preferences.update') }}" class="space-y-6">
                        @csrf

                        <div class="space-y-2">
                            <label class="flex max-md:min-h-12 items-start gap-3 py-2">
                                <input type="hidden" name="notify_milestones" value="0">
                                <input type="checkbox" name="notify_milestones" value="1"
                                    @checked(old('notify_milestones', $user->notify_milestones))
                                    class="mt-1 h-5 w-5 shrink-0 rounded-sm border-border text-brand focus:ring-brand/40">
                                <span class="min-w-0">
                                    <span class="block text-base font-medium text-ink">{{ __('preferences.milestones_title') }}</span>
                                    <span class="mt-1 block text-sm leading-relaxed text-muted">{{ __('preferences.milestones_help') }}</span>
                                </span>
                            </label>

                            <label class="flex max-md:min-h-12 items-start gap-3 py-2">
                                <input type="hidden" name="notify_new_signatures" value="0">
                                <input type="checkbox" name="notify_new_signatures" value="1"
                                    @checked(old('notify_new_signatures', $user->notify_new_signatures))
                                    class="mt-1 h-5 w-5 shrink-0 rounded-sm border-border text-brand focus:ring-brand/40">
                                <span class="min-w-0">
                                    <span class="block text-base font-medium text-ink">{{ __('preferences.new_signatures_title') }}</span>
                                    <span class="mt-1 block text-sm leading-relaxed text-muted">{{ __('preferences.new_signatures_help') }}</span>
                                </span>
                            </label>

                            <label class="flex max-md:min-h-12 items-start gap-3 py-2">
                                <input type="hidden" name="notify_product" value="0">
                                <input type="checkbox" name="notify_product" value="1"
                                    @checked(old('notify_product', $user->notify_product))
                                    class="mt-1 h-5 w-5 shrink-0 rounded-sm border-border text-brand focus:ring-brand/40">
                                <span class="min-w-0">
                                    <span class="block text-base font-medium text-ink">{{ __('preferences.product_title') }}</span>
                                    <span class="mt-1 block text-sm leading-relaxed text-muted">{{ __('preferences.product_help') }}</span>
                                </span>
                            </label>
                        </div>

                        <x-ui.alert variant="info">
                            {{ __('preferences.transactional_note') }}
                        </x-ui.alert>

                        <div>
                            <x-ui.button type="submit" class="w-full max-md:min-h-12 sm:w-auto">
                                {{ __('preferences.save') }}
                            </x-ui.button>
                        </div>
                    </form>
                </div>

                <div class="mt-6 rounded-lg border border-border bg-surface-2 p-6">
                    <h2 class="text-base font-semibold text-ink">{{ __('preferences.unsubscribe_all_title') }}</h2>
                    <p class="mt-1 text-sm leading-relaxed text-muted">{{ __('preferences.unsubscribe_all_help') }}</p>

                    <form method="POST" action="{{ lroute('preferences.update') }}" class="mt-4">
                        @csrf
                        <input type="hidden" name="unsubscribe_all" value="1">

                        <x-ui.button type="submit" variant="secondary" class="w-full max-md:min-h-12 sm:w-auto">
                            {{ __('preferences.unsubscribe_all') }}
                        </x-ui.button>
                    </form>
                </div>
            @endif
        </div>
    </x-ui.section>
@endsection
