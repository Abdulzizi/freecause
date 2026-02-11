@extends('admin.layouts.app')

@section('title', 'Users')

@section('content')
    @php
        $icoBan = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none">
    <circle cx="12" cy="12" r="8" stroke="#c00" stroke-width="2"/>
    <path d="M8 8l8 8" stroke="#c00" stroke-width="2"/>
</svg>';

        $icoX = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none">
    <path d="M6 6l12 12" stroke="#d23b2a" stroke-width="2" stroke-linecap="round"/>
    <path d="M18 6L6 18" stroke="#d23b2a" stroke-width="2" stroke-linecap="round"/>
</svg>';
    @endphp

    <h1>users ({{ number_format($approxTotal) }})</h1>

    @if (session('success'))
        <div class="fc-success">{{ session('success') }}</div>
    @endif

    <x-admin.filter-box title="filter users" :action="route('admin.users')" :reset="route('admin.users')">
        <input class="fc-input" style="max-width:90px;" name="id" placeholder="ID" value="{{ $filters['id'] }}">

        <input class="fc-input" style="max-width:160px;" name="name" placeholder="First name"
            value="{{ $filters['name'] }}">

        <input class="fc-input" style="max-width:160px;" name="last_name" placeholder="Last name"
            value="{{ $filters['last_name'] }}">

        <input class="fc-input" style="max-width:220px;" name="email" placeholder="Email" value="{{ $filters['email'] }}">

        <input class="fc-input" style="max-width:130px;" name="ip" placeholder="IP" value="{{ $filters['ip'] }}">

        <select class="fc-select" name="level" style="max-width:140px;">
            @foreach ($levels as $k => $label)
                <option value="{{ $k }}" {{ $filters['level'] === $k ? 'selected' : '' }}>{{ $label }}
                </option>
            @endforeach
        </select>

        <select class="fc-select" name="locale" style="max-width:140px;">
            @foreach ($locales as $k => $label)
                <option value="{{ $k }}" {{ $filters['locale'] === $k ? 'selected' : '' }}>{{ $label }}
                </option>
            @endforeach
        </select>
    </x-admin.filter-box>

    <x-admin.list-table-box empty-text="no users found, try clearing filters" :p="$users" :bulk="[
        'banRoute' => route('admin.users.bulkBan'),
        'banLabel' => 'Banned',
        'banConfirm' => 'Ban selected users?',
    ]">
        <x-slot:thead>
            <tr style="border-bottom:1px solid #ccc;" onmouseover="this.style.background='#f7f7f7'"
                onmouseout="this.style.background=''">
                <th style="text-align:left; width:90px;">id</th>
                <th style="width:26px;"></th>
                <th style="width:26px; text-align:left;">v</th>
                <th style="text-align:left;">email</th>
                <th style="text-align:left;">first name</th>
                <th style="text-align:left;">last name</th>
                <th style="text-align:left; width:90px;">locale</th>
                <th style="text-align:left; width:160px;">registered</th>
            </tr>
        </x-slot:thead>

        <x-slot:tbody>
            @foreach ($users as $u)
                <tr style="border-bottom:1px solid #eee;">
                    <td>
                        <a href="{{ route('admin.users', array_merge(request()->query(), ['select' => $u->id])) }}"
                            style="text-decoration:none; color:#000;">
                            {{ $u->id }}
                        </a>
                    </td>

                    <td style="text-align:left;">
                        <input type="checkbox" class="bulk-checkbox" value="{{ $u->id }}">
                    </td>

                    <td style="text-align:left;">
                        @if ($u->is_verified ?? 0)
                            <span style="color:#5c8f3a;">✔</span>
                        @else
                            <span style="color:#c00;">✖</span>
                        @endif
                    </td>

                    <td><a href="mailto:{{ $u->email }}">{{ $u->email }}</a></td>
                    <td>{{ $u->name }}</td>
                    <td>{{ $u->last_name }}</td>
                    <td>{{ $u->locale }}</td>
                    <td>{{ $u->created_at }}</td>
                </tr>
            @endforeach
        </x-slot:tbody>

        <x-slot:footer>
            <div style="display:flex; gap:10px; align-items:flex-end;">

                <button type="button" id="bulk-banned" class="bulk-action" title="Ban Selected"
                    style="width:52px; height:44px; border:1px solid #bbb; background:#fff;
                   display:flex; flex-direction:column; align-items:center; justify-content:center; gap:2px; cursor:pointer;">
                    {!! $icoBan !!}
                    <div style="font-size:10px; color:#666;">Ban</div>
                </button>

                <a href="{{ route('admin.users', request()->except('select')) }}" title="Clear Selection"
                    style="display:flex; flex-direction:column; align-items:center; justify-content:center;
                  gap:2px; width:44px; height:44px; border:1px solid #bbb; background:#fff;
                  text-decoration:none;">
                    {!! $icoX !!}
                    <div style="font-size:10px; color:#666;">Clear</div>
                </a>

            </div>
        </x-slot:footer>


    </x-admin.list-table-box>

    <x-admin.detail-panel title="user">
        <form method="post" action="{{ route('admin.users.save') }}" style="margin:0;">
            @csrf
            <input type="hidden" name="id" value="{{ $selectedUser->id ?? '' }}">

            <div class="fc-tab" style="margin-top:0;">Access Data</div>
            <div class="fc-box">
                <div class="fc-row">
                    <label>username</label>
                    <input class="fc-input" type="text" name="username" value="">
                </div>
                <div class="fc-row">
                    <label>password</label>
                    <input class="fc-input" type="text" name="password" value="">
                </div>
                <div class="fc-row">
                    <label>level</label>
                    <select class="fc-select" name="level" style="max-width:180px;">
                        @foreach ($levels as $k => $label)
                            @if ($k !== '')
                                <option value="{{ $k }}"
                                    {{ ($selectedUser->level ?? '') === $k ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endif
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="fc-tab">other</div>
            <div class="fc-box">
                <div class="fc-row">
                    <label>first name</label>
                    <input class="fc-input" type="text" name="name" value="{{ $selectedUser->name ?? '' }}">
                </div>
                <div class="fc-row">
                    <label>last name</label>
                    <input class="fc-input" type="text" name="last_name"
                        value="{{ $selectedUser->last_name ?? '' }}">
                </div>

                <div class="fc-row">
                    <label>verified</label>
                    <input type="checkbox" name="verified" value="1"
                        {{ $selectedUser->is_verified ?? 0 ? 'checked' : '' }}>
                </div>

                <div class="fc-row">
                    <label>email</label>
                    <input class="fc-input" type="text" name="email" value="{{ $selectedUser->email ?? '' }}">
                </div>

                <div class="fc-row">
                    <label>locale</label>
                    <select class="fc-select" name="locale" style="max-width:180px;">
                        @foreach ($locales as $k => $label)
                            @if ($k !== '')
                                <option value="{{ $k }}"
                                    {{ ($selectedUser->locale ?? '') === $k ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endif
                        @endforeach
                    </select>
                </div>
            </div>

            <div style="display:flex; justify-content:flex-end; margin-top:6px;">
                <button class="fc-btn" type="submit">save</button>
            </div>
        </form>
    </x-admin.detail-panel>
@endsection

@include('admin.partials.bulk-js', [
    'banRoute' => route('admin.users.bulkBan'),
    'noun' => 'users',
    'emptyMsg' => 'No users selected',
])
