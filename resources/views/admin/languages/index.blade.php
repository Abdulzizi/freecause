@extends('admin.layouts.app')

@section('title', 'Languages')

@section('content')

    <x-admin.page-header title="languages" />

    <x-admin.section-panel title="add language">
        <form method="post" action="{{ route('admin.languages.store') }}">
            @csrf

            <x-admin.form-field label="code">
                <x-ui.input name="code" placeholder="e.g. es" required class="w-full! box-border! border! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white!" />
            </x-admin.form-field>

            <x-admin.form-field label="name">
                <x-ui.input name="name" placeholder="Spanish" required class="w-full! box-border! border! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white!" />
            </x-admin.form-field>

            {{-- <div class="grid! grid-cols-1! gap-1.5! py-1.5! md:grid-cols-[160px_1fr]! md:gap-3! md:items-center!">
                <label class="font-bold! text-sm!">flag filename</label>
                <input class="w-full! box-border! border! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white!" name="flag" placeholder="en_US.png">
            </div> --}}

            <div class="text-right! mt-2.5!">
                <button class="fc-abtn">create language</button>
            </div>
        </form>
    </x-admin.section-panel>

    <x-admin.section-panel title="existing languages">

        @forelse($languages as $lang)
            <div
                class="flex! justify-between! items-start! py-[15px]! border-b! border-[#ddd]!">

                {{-- LEFT INFO --}}
                <div class="min-w-[200px]!">
                    <div class="text-base! font-bold!">
                        {{ strtoupper($lang->code) }}: {{ $lang->name }}

                        @if ($lang->is_default)
                            <span
                                class="bg-[#28a745]! text-white! px-1.5! py-0.5! rounded! text-xs! ml-1.5!">
                                default
                            </span>
                        @endif
                    </div>

                    <div class="mt-[5px]! text-[13px]! text-[#666]!">
                        status: {{ $lang->is_active ? 'active' : 'inactive' }}
                    </div>
                </div>

                <div class="flex-1! max-w-[500px]!">

                    <form method="post" action="{{ route('admin.languages.update', $lang) }}"
                        class="flex! gap-2.5! items-center! mb-1.5!">
                        @csrf
                        @method('PUT')

                        <x-admin.form-field label="name" class="min-w-0! flex-1!">
                            <x-ui.input name="name" :value="$lang->name" class="fc-filter-field" />
                        </x-admin.form-field>

                        {{-- <input class="fc-filter-field" name="flag" value="{{ $lang->flag }}"> --}}

                        <label class="flex! items-center! gap-[5px]!">
                            <span class="fc-switch">
                                <input type="checkbox" name="is_active" {{ $lang->is_active ? 'checked' : '' }}>
                                <span class="fc-switch__track" aria-hidden="true"></span>
                            </span>
                            active
                        </label>

                        <button class="fc-abtn">save</button>
                    </form>

                    @if (!$lang->is_default)
                        <div class="flex! gap-2!">

                            <form method="post" action="{{ route('admin.languages.default', $lang) }}">
                                @csrf
                                <button type="submit" class="fc-abtn">set default</button>
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
