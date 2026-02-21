@extends('admin.layouts.app')

@section('title', 'Permissions')

@section('content')

    <h1>permissions</h1>

    {{-- @if (session('success'))
        <div class="fc-success" style="margin-bottom:15px;">
            {{ session('success') }}
        </div>
    @endif --}}

    <div class="fc-box" style="padding:20px; margin-bottom:20px;">

        <form method="get" action="{{ route('admin.system.permissions') }}"
            style="display:flex; gap:40px; align-items:flex-end; flex-wrap:wrap;">

            <div>
                <label style="display:block; margin-bottom:6px;">
                    Module
                </label>

                <select name="module" onchange="this.form.submit()" class="fc-input" style="min-width:220px;">

                    @foreach ($modules as $module => $actions)
                        <option value="{{ $module }}" {{ $selectedModule == $module ? 'selected' : '' }}>
                            {{ ucfirst($module) }}
                        </option>
                    @endforeach

                </select>
            </div>

            <div>
                <label style="display:block; margin-bottom:6px;">
                    Level
                </label>

                <select name="level" onchange="this.form.submit()" class="fc-input" style="min-width:220px;">

                    @foreach ($levels as $level)
                        <option value="{{ $level->id }}" {{ $selectedLevel == $level->id ? 'selected' : '' }}>
                            {{ $level->name }}
                        </option>
                    @endforeach

                </select>
            </div>

        </form>

    </div>

    <div class="fc-tab">
        privileges for <strong>{{ ucfirst($selectedModule) }}</strong>
    </div>
    <div class="fc-box" style="padding:25px;">

        <form method="post" action="{{ route('admin.system.permissions.store') }}">
            @csrf

            <input type="hidden" name="module" value="{{ $selectedModule }}">
            <input type="hidden" name="level_id" value="{{ $selectedLevel }}">

            <div
                style="
                display:grid;
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
                gap:12px;
            ">

                @foreach ($modules[$selectedModule] as $action)
                    <label
                        style="
                        display:flex;
                        align-items:center;
                        gap:8px;
                        padding:8px 10px;
                        border:1px solid #e5e5e5;
                        background:#fafafa;
                        border-radius:4px;
                    ">

                        <input type="checkbox" name="actions[]" value="{{ $action }}"
                            {{ in_array($action, $permissions) ? 'checked' : '' }}>

                        <span style="text-transform:capitalize;">
                            {{ $action }}
                        </span>

                    </label>
                @endforeach

            </div>

            <div style="display:flex; justify-content:flex-end; margin-top:25px;">
                <button class="fc-btn">
                    save
                </button>
            </div>

        </form>

    </div>

@endsection
