@extends('admin.layouts.app')

@section('title', 'User Levels')

@section('content')

    <h1>user levels ({{ number_format($levels->total()) }})</h1>

    @if (session('success'))
        <div class="fc-success" style="margin-bottom:15px;">
            {{ session('success') }}
        </div>
    @endif


    <x-admin.list-table-box emptyText="no user levels found" :p="$levels">

        <x-slot:thead>
            <tr style="border-bottom:1px solid #ccc; height:44px;">
                <th style="width:70px; text-align:left;">ID</th>
                <th style="width:40px; text-align:left;"></th>
                <th style="text-align:left;">Name (system key)</th>
                <th style="text-align:right;">Visible Name</th>
                <th style="width:90px; text-align:right;">System</th>
                <th style="width:90px; text-align:right;">Users</th>
            </tr>
        </x-slot:thead>

        <x-slot:tbody>
            @foreach ($levels as $level)
                <tr style="border-bottom:1px solid #eee; height:44px;">

                    <td style="text-align:left; vertical-align:middle;">
                        {{ $level->id }}
                    </td>

                    <td style="text-align:left; vertical-align:middle;">
                        @if (!$level->is_system && $level->users_count == 0)
                            <input type="checkbox" class="bulk-checkbox" value="{{ $level->id }}">
                        @endif
                    </td>

                    <td style="text-align:left; vertical-align:middle;">
                        <strong>{{ $level->name }}</strong>
                    </td>

                    <td style="text-align:right; vertical-align:middle;">
                        {{ $level->visible_name }}
                    </td>

                    <td style="text-align:right; vertical-align:middle;">
                        {!! $level->is_system
                            ? '<span style="color:#5c8f3a;font-weight:bold;">✔</span>'
                            : '<span style="color:#c00;">✖</span>' !!}
                    </td>

                    <td style="text-align:right; vertical-align:middle;">
                        {{ $level->users_count }}
                    </td>

                </tr>
            @endforeach
        </x-slot:tbody>

        <x-slot:footer>
            <div style="display:flex; gap:12px; align-items:center;">

                <button type="button" class="fc-icon-btn bulk-action" data-action="delete">

                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                        <path d="M6 6l12 12" stroke="#d23b2a" stroke-width="2" stroke-linecap="round" />
                        <path d="M18 6L6 18" stroke="#d23b2a" stroke-width="2" stroke-linecap="round" />
                    </svg>

                    <div style="font-size:11px; color:#666;">
                        delete
                    </div>
                </button>

            </div>
        </x-slot:footer>

    </x-admin.list-table-box>

    <div class="fc-tab" style="margin-top:25px;">new level</div>
    <div class="fc-box" style="padding:20px;">

        <form method="post" action="{{ route('admin.system.user_levels.store') }}">
            @csrf

            <div class="fc-row">
                <label>Name (system key)</label>
                <input type="text" name="name" class="fc-input" required>
            </div>

            <div class="fc-row">
                <label>Visible Name</label>
                <input type="text" name="visible_name" class="fc-input" required>
            </div>

            <div style="display:flex; justify-content:flex-end; margin-top:15px;">
                <button class="fc-btn">
                    add
                </button>
            </div>
        </form>

    </div>

@endsection


@include('admin.partials.bulk-js', [
    'actionRoute' => route('admin.system.user_levels.delete'),
    'noun' => 'user levels',
    'emptyMsg' => 'No user levels selected',
])
