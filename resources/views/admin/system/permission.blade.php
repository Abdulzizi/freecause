@extends('admin.layouts.app')

@section('title', 'Permissions')

@section('content')

    <h1 class="m-0! mb-2! text-lg!">permissions</h1>

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

                <select name="module" onchange="this.form.submit()" class="w-full! box-border! border! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white! min-w-[220px]!">

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

                <select name="level" onchange="this.form.submit()" class="w-full! box-border! border! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white! min-w-[220px]!">

                    @foreach ($levels as $level)
                        <option value="{{ $level->id }}" {{ $selectedLevel == $level->id ? 'selected' : '' }}>
                            {{ $level->name }}
                        </option>
                    @endforeach

                </select>
            </div>

        </form>

    </div>

    <div class="inline-block! bg-[#e6e6e6]! border! border-[#9e9e9e]! border-b-0! px-2.5! py-[3px]! rounded-tl! rounded-tr! font-bold! text-sm! mt-2.5! mr-0! mb-0! ml-0!">
        privileges for <strong>{{ ucfirst($selectedModule) }}</strong>
    </div>
    <div class="border! border-[#9e9e9e]! rounded! p-2.5! mb-2.5! p-[25px]!">

        <form method="post" action="{{ route('admin.system.permissions.store') }}">
            @csrf

            <input type="hidden" name="module" value="{{ $selectedModule }}">
            <input type="hidden" name="level_id" value="{{ $selectedLevel }}">

            <div
                class="grid! gap-3! grid-cols-[repeat(auto-fill,minmax(200px,1fr))]!">

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
                <button class="border! border-[#7d7d7d]! bg-[#e6e6e6]! px-3! py-1.5! rounded-[3px]! cursor-pointer! hover:bg-[#dcdcdc]! text-xs! md:py-1! md:px-2.5! md:text-sm!">
                    save
                </button>
            </div>

        </form>

    </div>

@endsection
