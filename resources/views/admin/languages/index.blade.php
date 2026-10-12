@extends('admin.layouts.app')

@section('title', 'Languages')

@section('content')

    <x-admin.page-header title="languages" />

    <x-admin.section-panel title="add language">
        <form method="post" action="{{ route('admin.languages.store') }}">
            @csrf

            <x-admin.form-field label="code">
                <x-ui.input name="code" placeholder="e.g. es" required class="w-full box-border border border-border rounded px-2 py-1.5 text-sm bg-white" />
            </x-admin.form-field>

            <x-admin.form-field label="name">
                <x-ui.input name="name" placeholder="Spanish" required class="w-full box-border border border-border rounded px-2 py-1.5 text-sm bg-white" />
            </x-admin.form-field>

            <div class="text-right mt-2.5">
                <x-blatui.button type="submit">create language</x-blatui.button>
            </div>
        </form>
    </x-admin.section-panel>

    <x-admin.section-panel title="existing languages">

        @forelse($languages as $lang)
            <div x-data="{ editing: false }" class="flex flex-wrap items-center gap-3 py-4 border-b border-border last:border-b-0">

                <div class="w-full shrink-0 md:w-[190px]">
                    <div class="text-[14px] font-semibold text-ink">
                        {{ strtoupper($lang->code) }}: {{ $lang->name }}

                        @if ($lang->is_default)
                            <x-blatui.badge tone="success" class="ml-1.5">default</x-blatui.badge>
                        @endif
                    </div>

                    <div class="mt-0.5 text-[12px] text-muted">
                        status: {{ $lang->is_active ? 'active' : 'inactive' }}
                    </div>
                </div>

                <div x-show="!editing" class="ms-auto flex items-center gap-2">
                    <x-blatui.button variant="outline" size="sm" type="button" @click="editing = true">edit</x-blatui.button>

                    @if (!$lang->is_default)
                        <form method="post" action="{{ route('admin.languages.default', $lang) }}">
                            @csrf
                            <x-blatui.button variant="ghost" size="sm" type="submit">set default</x-blatui.button>
                        </form>
                    @endif
                </div>

                <form x-show="editing" x-cloak method="post" action="{{ route('admin.languages.update', $lang) }}"
                    class="ms-auto flex flex-wrap items-center gap-2">
                    @csrf
                    @method('PUT')

                    <x-ui.input name="name" :value="$lang->name" aria-label="Language name" class="fc-filter-field w-[200px] max-w-full" />

                    <label class="flex items-center gap-1.5 text-[13px] whitespace-nowrap">
                        <span class="fc-switch">
                            <input type="checkbox" name="is_active" {{ $lang->is_active ? 'checked' : '' }}>
                            <span class="fc-switch__track" aria-hidden="true"></span>
                        </span>
                        active
                    </label>

                    <x-blatui.button size="sm" type="submit">save</x-blatui.button>
                    <x-blatui.button variant="ghost" size="sm" type="button" @click="editing = false">cancel</x-blatui.button>
                </form>

            </div>

        @empty
            <x-admin.empty-state message="no languages created yet" class="p-5 text-center" />
        @endforelse

    </x-admin.section-panel>

@endsection
