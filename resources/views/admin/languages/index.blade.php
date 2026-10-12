@extends('admin.layouts.app')

@section('title', 'Languages')

@section('content')

    <x-admin.page-header title="languages" />

    <x-admin.section-panel title="add language">
        <form method="post" action="{{ route('admin.languages.store') }}">
            @csrf

            <x-admin.form-field label="code">
                <x-ui.input name="code" placeholder="e.g. es" required class="w-full! box-border! border! border-border! rounded! px-2! py-1.5! text-sm! bg-white!" />
            </x-admin.form-field>

            <x-admin.form-field label="name">
                <x-ui.input name="name" placeholder="Spanish" required class="w-full! box-border! border! border-border! rounded! px-2! py-1.5! text-sm! bg-white!" />
            </x-admin.form-field>

            {{-- <div class="grid! grid-cols-1! gap-1.5! py-1.5! md:grid-cols-[160px_1fr]! md:gap-3! md:items-center!">
                <label class="font-bold! text-sm!">flag filename</label>
                <input class="w-full! box-border! border! border-border! rounded! px-2! py-1.5! text-sm! bg-white!" name="flag" placeholder="en_US.png">
            </div> --}}

            <div class="text-right! mt-2.5!">
                <x-blatui.button type="submit">create language</x-blatui.button>
            </div>
        </form>
    </x-admin.section-panel>

    <x-admin.section-panel title="existing languages">

        @forelse($languages as $lang)
            <div
                class="flex! justify-between! items-start! py-[15px]! border-b! border-border!">

                {{-- LEFT INFO --}}
                <div class="min-w-[200px]!">
                    <div class="text-base! font-bold!">
                        {{ strtoupper($lang->code) }}: {{ $lang->name }}

                        @if ($lang->is_default)
                            <x-blatui.badge tone="success" class="ml-1.5!">default</x-blatui.badge>
                        @endif
                    </div>

                    <div class="mt-[5px]! text-[13px]! text-muted!">
                        status: {{ $lang->is_active ? 'active' : 'inactive' }}
                    </div>
                </div>

                <div class="flex-1! max-w-[500px]!">

                    <form method="post" action="{{ route('admin.languages.update', $lang) }}"
                        class="flex! gap-3! items-center! mb-1.5!">
                        @csrf
                        @method('PUT')

                        <label for="lang-name-{{ $lang->id }}" class="text-[13px]! text-muted!">name</label>
                        <x-ui.input id="lang-name-{{ $lang->id }}" name="name" :value="$lang->name" class="fc-filter-field w-[220px]! max-w-full!" />

                        <label class="flex! items-center! gap-[5px]! text-[13px]!">
                            <span class="fc-switch">
                                <input type="checkbox" name="is_active" {{ $lang->is_active ? 'checked' : '' }}>
                                <span class="fc-switch__track" aria-hidden="true"></span>
                            </span>
                            active
                        </label>

                        <x-blatui.button type="submit" size="sm">save</x-blatui.button>
                    </form>

                    @if (!$lang->is_default)
                        <div class="flex! gap-2!">

                            <form method="post" action="{{ route('admin.languages.default', $lang) }}">
                                @csrf
                                <x-blatui.button variant="outline" size="sm" type="submit">set default</x-blatui.button>
                            </form>

                        </div>
                    @endif

                </div>

            </div>

        @empty
            <x-admin.empty-state message="no languages created yet" class="p-5! text-center!" />
        @endforelse

    </x-admin.section-panel>

@endsection
