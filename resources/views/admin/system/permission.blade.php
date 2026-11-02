@extends('admin.layouts.app')

@section('title', 'Permissions')

@section('content')

    <x-admin.page-header title="permissions" />

    <div class="border border-border rounded-sm p-2.5 mb-2.5 p-5 mb-5">

        <form method="get" action="{{ route('admin.system.permissions') }}"
            class="flex gap-10 items-end flex-wrap">

            <div>
                <label class="block mb-1.5">
                    Module
                </label>

                <select name="module" aria-label="module" class="fc-filter-field" style="min-width:220px;" data-admin-autosubmit>

                    @foreach ($modules as $module => $actions)
                        <option value="{{ $module }}" {{ $selectedModule == $module ? 'selected' : '' }}>
                            {{ ucfirst($module) }}
                        </option>
                    @endforeach

                </select>
            </div>

            <div>
                <label class="block mb-1.5">
                    Level
                </label>

                <select name="level" aria-label="level" class="fc-filter-field" style="min-width:220px;" data-admin-autosubmit>

                    @foreach ($levels as $level)
                        <option value="{{ $level->id }}" {{ $selectedLevel == $level->id ? 'selected' : '' }}>
                            {{ $level->name }}
                        </option>
                    @endforeach

                </select>
            </div>

        </form>

    </div>

    <x-admin.section-panel title="privileges for {{ ucfirst($selectedModule) }}">
        <form method="post" action="{{ route('admin.system.permissions.store') }}">
            @csrf

            <input type="hidden" name="module" value="{{ $selectedModule }}">
            <input type="hidden" name="level_id" value="{{ $selectedLevel }}">

            <div class="grid gap-3 grid-cols-[repeat(auto-fill,minmax(200px,1fr))]">

                @foreach ($modules[$selectedModule] as $action)
                    <label
                        class="flex items-center gap-2 px-2.5 py-2 border border-border bg-surface-2 rounded-sm">

                        <input type="checkbox" name="actions[]" value="{{ $action }}"
                            {{ in_array($action, $permissions) ? 'checked' : '' }}>

                        <span class="capitalize">
                            {{ $action }}
                        </span>

                    </label>
                @endforeach

            </div>

            <div class="flex justify-end mt-6">
                <x-blatui.button type="submit">save</x-blatui.button>
            </div>

        </form>
    </x-admin.section-panel>

@endsection
