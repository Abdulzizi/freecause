@extends('admin.layouts.app')

@section('title', 'Permissions')

@section('content')

    <x-admin.page-header title="permissions" />

    {{-- @if (session('success'))
        <div class="border! border-[#77c777]! bg-[#e9ffe9]! text-[#135f13]! px-1.5! py-1! rounded! my-1.5! mb-2.5!">
            {{ session('success') }}
        </div>
    @endif --}}

    <div class="border! border-[#9e9e9e]! rounded! p-2.5! mb-2.5! p-5! mb-5!">

        <form method="get" action="{{ route('admin.system.permissions') }}"
            class="flex! gap-10! items-end! flex-wrap!">

            <div>
                <label class="block! mb-1.5!">
                    Module
                </label>

                <select name="module" data-admin-autosubmitclass="fc-filter-field min-w-[220px]!">

                    @foreach ($modules as $module => $actions)
                        <option value="{{ $module }}" {{ $selectedModule == $module ? 'selected' : '' }}>
                            {{ ucfirst($module) }}
                        </option>
                    @endforeach

                </select>
            </div>

            <div>
                <label class="block! mb-1.5!">
                    Level
                </label>

                <select name="level" data-admin-autosubmitclass="fc-filter-field min-w-[220px]!">

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

            <div class="grid! gap-3! grid-cols-[repeat(auto-fill,minmax(200px,1fr))]!">

                @foreach ($modules[$selectedModule] as $action)
                    <label
                        class="flex! items-center! gap-2! px-2.5! py-2! border! border-[#e5e5e5]! bg-[#fafafa]! rounded!">

                        <input type="checkbox" name="actions[]" value="{{ $action }}"
                            {{ in_array($action, $permissions) ? 'checked' : '' }}>

                        <span class="capitalize!">
                            {{ $action }}
                        </span>

                    </label>
                @endforeach

            </div>

            <div class="flex! justify-end! mt-[25px]!">
                <button type="submit" class="fc-abtn">save</button>
            </div>

        </form>
    </x-admin.section-panel>

@endsection
