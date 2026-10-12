@extends('admin.layouts.app')

@section('title', 'Users')

@section('content')
    <x-admin.page-header title="users" :count="$approxTotal" />

    @php
        $activeFilterCount = collect($filters)->filter(fn($v) => $v !== '' && $v !== null)->count();
    @endphp

    <x-admin.filter-box title="filter users" :action="route('admin.users')" :reset="route('admin.users')" :activeCount="$activeFilterCount">

        <input class="fc-filter-field" name="id" placeholder="ID" value="{{ $filters['id'] }}">
        <input class="fc-filter-field" name="first_name" placeholder="First name" value="{{ $filters['first_name'] }}">
        <input class="fc-filter-field" name="last_name" placeholder="Last name"value="{{ $filters['last_name'] }}">
        <input class="fc-filter-field" name="nickname" placeholder="Nickname" value="{{ $filters['nickname'] }}">
        <input class="fc-filter-field" name="email" placeholder="Email" value="{{ $filters['email'] }}">
        <select data-admin-autosubmit class="fc-filter-field" name="email_mode" aria-label="Email search mode">
            <option value="prefix" {{ ($filters['email_mode'] ?? 'prefix') === 'prefix' ? 'selected' : '' }}>email starts with</option>
            <option value="domain" {{ ($filters['email_mode'] ?? 'prefix') === 'domain' ? 'selected' : '' }}>email domain</option>
        </select>
        <input class="fc-filter-field" name="ip" placeholder="IP" value="{{ $filters['ip'] }}">

        <select data-admin-autosubmit class="fc-filter-field" name="status">
            <option value="">any status</option>
            <option value="verified" {{ $filters['status'] === 'verified' ? 'selected' : '' }}>verified</option>
            <option value="unverified" {{ $filters['status'] === 'unverified' ? 'selected' : '' }}>unverified</option>
        </select>

        <input class="fc-filter-field" type="date" name="registered_from" value="{{ $filters['registered_from'] }}">
        <input class="fc-filter-field" type="date" name="registered_to" value="{{ $filters['registered_to'] }}">

        <select data-admin-autosubmit class="fc-filter-field" name="level">
            @foreach ($levels as $k => $label)
                <option value="{{ $k }}" {{ $filters['level'] === $k ? 'selected' : '' }}>{{ $label }}
                </option>
            @endforeach
        </select>

        <select data-admin-autosubmit class="fc-filter-field" name="locale">
            @foreach ($locales as $k => $label)
                <option value="{{ $k }}" {{ $filters['locale'] === $k ? 'selected' : '' }}>{{ $label }}
                </option>
            @endforeach
        </select>
    </x-admin.filter-box>

    <div class="flex gap-2 mb-3 items-center flex-wrap">
        <x-blatui.button variant="outline" size="sm" :href="route('admin.users.export', request()->query())">export CSV</x-blatui.button>
    </div>

    <x-admin.data-table empty-text="no users found, try clearing filters" :p="$users" :emptyClearRoute="route('admin.users')" :bulk="admin_can('users', 'edit') ? [
        'banRoute'        => route('admin.users.bulkBan'),
        'banLabel'        => 'Ban',
        'banConfirm'      => 'Ban selected users?',
        'banWithDetails'  => true,
    ] : null">
        <x-slot:thead>
            <tr class="border-b border-solid border-border" data-admin-row-hover>
                <th class="text-left w-[90px]">id</th>
                <th width="26"></th>
                <th class="w-[26px] text-left" data-hide-mobile>v</th>
                <th class="text-left">email</th>
                <th class="text-left">username</th>
                <th class="text-left" data-hide-mobile>first name</th>
                <th class="text-left" data-hide-mobile>last name</th>
                <th class="text-left w-[90px]" data-hide-mobile>locale</th>
                <th class="text-left w-[160px]">registered</th>
            </tr>
        </x-slot:thead>

        <x-slot:tbody>
            @foreach ($users as $u)
                @php
                    $isBanned = $u->level?->name === 'banned';
                @endphp
                <tr class="border-b border-solid border-border {{ $isBanned ? 'bg-danger/5 [&_td]:text-danger' : '' }}" data-admin-row-hover data-detail-row data-detail-href="{{ route('admin.users', array_merge(request()->query(), ['select' => $u->id])) }}">
                    <td>
                        <a href="{{ route('admin.users', array_merge(request()->query(), ['select' => $u->id])) }}"
                            class="no-underline text-black">
                            {{ $u->id }}
                        </a>
                    </td>

                    <td class="text-left">
                        @if (admin_can('users', 'edit') || admin_can('users', 'delete'))
                            <input type="checkbox" class="bulk-checkbox" value="{{ $u->id }}"
                                data-level="{{ $u->level?->name }}"
                                aria-label="Select user {{ $u->id }}"
                                {{ $u->hasLevel('admin') || $u->id === auth()->id() ? 'disabled' : '' }}>
                        @endif
                    </td>

                    <td data-hide-mobile class="text-left">
                        @if ($u->verified ?? 0)
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#198754" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        @else
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#dc3545" stroke-width="2.5" stroke-linecap="round"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
                        @endif
                    </td>

                    <td>
                        <a href="mailto:{{ $u->email }}"
                            style="{{ $isBanned ? 'color:#dc3545; font-weight:bold;' : '' }}">
                            {{ $u->email }}
                        </a>
                    </td>

                    <td>
                        @php
                            $isAdmin = $u->hasLevel('admin');
                        @endphp

                        @if (!$isAdmin)
                            <a target="_blank" rel="noopener" href="{{ route('user.profile', ['locale' => app()->getLocale(), 'slug' => $u->username ?? \Str::slug($u->name), 'id' => $u->id]) }}"
                                class="text-black">
                                {{ $u->name }}
                            </a>
                        @else
                            <span>{{ $u->name }}</span>
                        @endif

                        @if ($isBanned)
                            <x-blatui.badge tone="danger" variant="solid" class="ml-1.5 align-middle" title="{{ $u->banned_reason ? 'Reason: ' . $u->banned_reason : 'No reason given' }}{{ $u->banned_until ? ' · Until: ' . $u->banned_until : ' · Permanent' }}">BANNED</x-blatui.badge>
                        @endif
                    </td>
                    <td data-hide-mobile>{{ $u->first_name }}</td>
                    <td data-hide-mobile>{{ $u->last_name }}</td>
                    <td data-hide-mobile>
                        @php
                            $short = \App\Support\Locale::toShort($u->locale);
                            $label = $locales[$short] ?? $short;
                        @endphp
                        {{ $label }}
                    </td>
                    <td>{{ $u->created_at }}</td>
                </tr>
            @endforeach
        </x-slot:tbody>

        <x-slot:footer>
            <div class="flex gap-2 items-center flex-wrap max-md:gap-1.5 max-md:w-full max-md:justify-end">
                @if (admin_can('users', 'edit'))
                    <x-blatui.button variant="outline" size="sm" type="button" id="bulk-unban" title="Unban">
                        <x-lucide-undo-2 aria-hidden="true" />
                        <span class="max-md:hidden">Unban</span>
                    </x-blatui.button>
                @endif

                @if (admin_can('users', 'delete'))
                    <x-blatui.button variant="destructive" size="sm" type="button" id="bulk-delete" title="Delete">
                        <x-lucide-trash-2 aria-hidden="true" />
                        <span class="max-md:hidden">Delete</span>
                    </x-blatui.button>
                @endif

                <x-blatui.button variant="outline" size="sm" :href="route('admin.users', request()->except('select'))" title="Clear selection">
                    <x-lucide-x aria-hidden="true" />
                    <span class="max-md:hidden">Clear</span>
                </x-blatui.button>
            </div>
        </x-slot:footer>

    </x-admin.data-table>

    <x-admin.detail-panel title="user" :closeRoute="route('admin.users', request()->except('select'))">
        @if ($selectedUser)
            @php
                $selectedUserIsBanned = $selectedUser->level?->name === 'banned';
                $selectedUserProtected = $selectedUser->id === auth()->id() || ($selectedUser->level?->is_system ?? false);
            @endphp
            @if (admin_can('users', 'edit'))
            <div class="mb-2.5 flex items-center gap-2 flex-wrap">
                @if (! $selectedUserProtected)
                    @if ($selectedUserIsBanned)
                        <button type="button" data-user-single-action="unban"
                            data-url="{{ route('admin.users.unban', ['user' => $selectedUser->id]) }}"
                            class="border border-[#6b8e23] bg-white px-2.5 py-1 rounded text-xs cursor-pointer">
                            Unban user
                        </button>
                    @else
                        <button type="button" data-user-single-action="ban"
                            data-url="{{ route('admin.users.ban', ['user' => $selectedUser->id]) }}"
                            class="border border-danger bg-white text-danger px-2.5 py-1 rounded text-xs cursor-pointer">
                            Ban user
                        </button>
                    @endif
                @else
                    <span class="text-muted">System accounts and your own account cannot be banned here.</span>
                @endif
            </div>
            @endif
        @endif
        @if ($selectedUser && admin_can('users', 'edit'))
        <form method="post" action="{{ route('admin.users.save') }}" class="m-0">
            @csrf
            <input type="hidden" name="id" value="{{ $selectedUser->id ?? '' }}">

            <x-admin.section-panel title="Access Data">
                <x-admin.form-field label="username">
                    <x-ui.input name="username" :value="$selectedUser->username ?? ''" class="w-full box-border border border-solid border-border rounded px-2 py-1.5 text-sm bg-white" />
                </x-admin.form-field>

                <x-admin.form-field label="password" :error="$errors->first('password')">
                    <x-ui.input name="password" type="password" value="" class="w-full box-border border border-solid border-border rounded px-2 py-1.5 text-sm bg-white" />
                </x-admin.form-field>

                <x-admin.form-field label="level">
                    <select class="fc-filter-field" name="level">
                        @if ($selectedUser?->level?->name === 'banned')
                            <option value="" selected disabled>Banned — use the unban control above</option>
                        @endif
                        @foreach ($editableLevels as $k => $label)
                            @if ($k !== '')
                                <option value="{{ $k }}"
                                    {{ ($selectedUser?->level?->name ?? '') === $k ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endif
                        @endforeach
                    </select>
                </x-admin.form-field>
            </x-admin.section-panel>

            <x-admin.section-panel title="other">

                <x-admin.form-field label="first name">
                    <x-ui.input name="first_name" :value="$selectedUser->first_name ?? ''" class="w-full box-border border border-solid border-border rounded px-2 py-1.5 text-sm bg-white" />
                </x-admin.form-field>

                <x-admin.form-field label="last name">
                    <x-ui.input name="last_name" :value="$selectedUser->last_name ?? ''" class="w-full box-border border border-solid border-border rounded px-2 py-1.5 text-sm bg-white" />
                </x-admin.form-field>

                <x-admin.form-field label="nickname">
                    <x-ui.input name="nickname" :value="$selectedUser->nickname ?? ''" class="w-full box-border border border-solid border-border rounded px-2 py-1.5 text-sm bg-white" />
                </x-admin.form-field>

                <x-admin.form-field label="location">
                    <x-ui.input name="city" :value="$selectedUser->city ?? ''" class="w-full box-border border border-solid border-border rounded px-2 py-1.5 text-sm bg-white" />
                </x-admin.form-field>

                <x-admin.form-field label="identify as">
                    <select class="fc-filter-field" name="identify_mode">
                        <option value="full" {{ ($selectedUser->identify_mode ?? 'full') === 'full' ? 'selected' : '' }}>full name</option>
                        <option value="name" {{ ($selectedUser->identify_mode ?? '') === 'name' ? 'selected' : '' }}>first name</option>
                        <option value="nick" {{ ($selectedUser->identify_mode ?? '') === 'nick' ? 'selected' : '' }}>nickname</option>
                    </select>
                </x-admin.form-field>

                <x-admin.form-field label="verified" for="user-verified">
                    <span class="fc-switch">
                        <input id="user-verified" type="checkbox" name="verified" value="1"
                            {{ $selectedUser->verified ?? 0 ? 'checked' : '' }}>
                        <span class="fc-switch__track" aria-hidden="true"></span>
                    </span>
                </x-admin.form-field>

                <x-admin.form-field label="email">
                    <x-ui.input name="email" data-live-validate :value="$selectedUser->email ?? ''" class="w-full box-border border border-solid border-border rounded px-2 py-1.5 text-sm bg-white" />
                </x-admin.form-field>

                <x-admin.form-field label="locale">
                    <select class="fc-filter-field" name="locale">
                        @foreach ($locales as $k => $label)
                            @if ($k !== '')
                                <option value="{{ $k }}"
                                    {{ \App\Support\Locale::toShort($selectedUser->locale ?? '') === $k ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endif
                        @endforeach
                    </select>
                </x-admin.form-field>
            </x-admin.section-panel>

            <div class="flex justify-end mt-1.5 fc-drawer-savebar">
                <x-blatui.button type="submit">save</x-blatui.button>
            </div>
        </form>
        @elseif ($selectedUser)
            <p class="text-sm text-muted">View-only access</p>
            <dl class="grid grid-cols-[120px_1fr] gap-2 text-sm max-md:grid-cols-[90px_1fr]">
                <dt class="font-bold">Email</dt><dd>{{ $selectedUser->email }}</dd>
                <dt class="font-bold">Username</dt><dd>{{ $selectedUser->username ?? $selectedUser->name }}</dd>
                <dt class="font-bold">Level</dt><dd>{{ $selectedUser->level?->name ?? '—' }}</dd>
                <dt class="font-bold">First name</dt><dd>{{ $selectedUser->first_name ?? '—' }}</dd>
                <dt class="font-bold">Last name</dt><dd>{{ $selectedUser->last_name ?? '—' }}</dd>
                <dt class="font-bold">Locale</dt><dd>{{ $locales[\App\Support\Locale::toShort($selectedUser->locale)] ?? $selectedUser->locale }}</dd>
                <dt class="font-bold">Registered</dt><dd>{{ $selectedUser->created_at }}</dd>
            </dl>
        @endif
    </x-admin.detail-panel>
@endsection

@include('admin.partials.bulk-js', [
    'banRoute' => route('admin.users.bulkBan'),
    'deleteRoute' => route('admin.users.bulkDelete'),
    'unbanRoute' => route('admin.users.bulkUnban'),
    'noun' => 'users',
    'emptyMsg' => 'No users selected',
])
