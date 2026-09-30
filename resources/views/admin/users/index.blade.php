@extends('admin.layouts.app')

@section('title', 'Users')

@section('content')
    @php
        $icoX = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none">
            <path d="M6 6l12 12" stroke="#d23b2a" stroke-width="2" stroke-linecap="round"/>
            <path d="M18 6L6 18" stroke="#d23b2a" stroke-width="2" stroke-linecap="round"/>
        </svg>';

        $icoUnban = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none">
            <circle cx="12" cy="12" r="8" stroke="#5c8f3a" stroke-width="2"/>
            <path d="M8 12h8" stroke="#5c8f3a" stroke-width="2"/>
        </svg>';

        $icoDelete = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none">
            <path d="M5 7h14" stroke="#d23b2a" stroke-width="2"/>
            <path d="M9 7V5h6v2" stroke="#d23b2a" stroke-width="2"/>
            <path d="M9 10v6M15 10v6" stroke="#d23b2a" stroke-width="2"/>
            <path d="M6 7l1 12h10l1-12" stroke="#d23b2a" stroke-width="2"/>
        </svg>';
    @endphp

    <h1>users ({{ number_format($approxTotal) }})</h1>

    @php
        $activeFilterCount = collect($filters)->filter(fn($v) => $v !== '' && $v !== null)->count();
    @endphp

    <x-admin.filter-box title="filter users" :action="route('admin.users')" :reset="route('admin.users')" :activeCount="$activeFilterCount">

        <input class="w-full! box-border! border! border-solid! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white! max-w-[90px]! max-md:max-w-none! max-md:flex-[1_1_140px]!" name="id" placeholder="ID" value="{{ $filters['id'] }}">
        <input class="w-full! box-border! border! border-solid! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white! max-w-[160px]! max-md:max-w-none! max-md:flex-[1_1_140px]!" name="first_name" placeholder="First name" value="{{ $filters['first_name'] }}">
        <input class="w-full! box-border! border! border-solid! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white! max-w-[160px]! max-md:max-w-none! max-md:flex-[1_1_140px]!" name="last_name" placeholder="Last name"value="{{ $filters['last_name'] }}">
        <input class="w-full! box-border! border! border-solid! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white! max-w-[140px]! max-md:max-w-none! max-md:flex-[1_1_140px]!" name="nickname" placeholder="Nickname" value="{{ $filters['nickname'] }}">
        <input class="w-full! box-border! border! border-solid! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white! max-w-[220px]! max-md:max-w-none! max-md:flex-[1_1_140px]!" name="email" placeholder="Email" value="{{ $filters['email'] }}">
        <input class="w-full! box-border! border! border-solid! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white! max-w-[130px]! max-md:max-w-none! max-md:flex-[1_1_140px]!" name="ip" placeholder="IP" value="{{ $filters['ip'] }}">

        <select class="w-full! box-border! border! border-solid! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white! max-w-[130px]! max-md:max-w-none! max-md:flex-[1_1_140px]!" name="status">
            <option value="">status...</option>
            <option value="verified" {{ $filters['status'] === 'verified' ? 'selected' : '' }}>verified</option>
            <option value="unverified" {{ $filters['status'] === 'unverified' ? 'selected' : '' }}>unverified</option>
        </select>

        <input class="w-full! box-border! border! border-solid! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white! max-w-[150px]! max-md:max-w-none! max-md:flex-[1_1_140px]!" type="date" name="registered_from" value="{{ $filters['registered_from'] }}">
        <input class="w-full! box-border! border! border-solid! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white! max-w-[150px]! max-md:max-w-none! max-md:flex-[1_1_140px]!" type="date" name="registered_to" value="{{ $filters['registered_to'] }}">

        <select class="w-full! box-border! border! border-solid! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white! max-w-[140px]! max-md:max-w-none! max-md:flex-[1_1_140px]!" name="level">
            @foreach ($levels as $k => $label)
                <option value="{{ $k }}" {{ $filters['level'] === $k ? 'selected' : '' }}>{{ $label }}
                </option>
            @endforeach
        </select>

        <select class="w-full! box-border! border! border-solid! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white! max-w-[140px]! max-md:max-w-none! max-md:flex-[1_1_140px]!" name="locale">
            @foreach ($locales as $k => $label)
                <option value="{{ $k }}" {{ $filters['locale'] === $k ? 'selected' : '' }}>{{ $label }}
                </option>
            @endforeach
        </select>
    </x-admin.filter-box>

    <x-admin.list-table-box empty-text="no users found, try clearing filters" :p="$users" :bulk="[
        'banRoute'        => route('admin.users.bulkBan'),
        'banLabel'        => 'Banned',
        'banConfirm'      => 'Ban selected users?',
        'banWithDetails'  => true,
    ]">
        <x-slot:thead>
            <tr class="border-b! border-solid! border-[#ccc]!" onmouseover="this.style.background='#f7f7f7'"
                onmouseout="this.style.background=''">
                <th class="text-left! w-[90px]!">id</th>
                <th width="26" data-hide-mobile></th>
                <th class="w-[26px]! text-left!" data-hide-mobile>v</th>
                <th class="text-left!">email</th>
                <th class="text-left!">username</th>
                <th class="text-left!" data-hide-mobile>first name</th>
                <th class="text-left!" data-hide-mobile>last name</th>
                <th class="text-left! w-[90px]!" data-hide-mobile>locale</th>
                <th class="text-left! w-[160px]!">registered</th>
            </tr>
        </x-slot:thead>

        <x-slot:tbody>
            @foreach ($users as $u)
                @php
                    $isBanned = $u->level?->name === 'banned';
                @endphp
                <tr class="border-b! border-solid! border-[#eee]! {{ $isBanned ? 'bg-[#fff3f3]! [&_td]:text-[#900]!' : '' }}">
                    <td>
                        <a href="{{ route('admin.users', array_merge(request()->query(), ['select' => $u->id])) }}"
                            class="no-underline! text-black!">
                            {{ $u->id }}
                        </a>
                    </td>

                    <td data-hide-mobile class="text-left!">
                        {{-- <input type="checkbox" class="bulk-checkbox" value="{{ $u->id }}" data-level="{{ $u->level?->name }}" {{ $u->hasLevel('admin') ? 'disabled' : '' }}> --}}
                        <input type="checkbox" class="bulk-checkbox" value="{{ $u->id }}"
                            data-level="{{ $u->level?->name }}"
                            {{ $u->hasLevel('admin') || $u->id === auth()->id() ? 'disabled' : '' }}>
                    </td>

                    <td data-hide-mobile class="text-left!">
                        @if ($u->verified ?? 0)
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#5c8f3a" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        @else
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#c00" stroke-width="2.5" stroke-linecap="round"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
                        @endif
                    </td>

                    <td>
                        <a href="mailto:{{ $u->email }}"
                            style="{{ $isBanned ? 'color:#c00; font-weight:bold;' : '' }}">
                            {{ $u->email }}
                        </a>
                    </td>

                    <td>
                        @php
                            $isAdmin = $u->hasLevel('admin');
                        @endphp

                        @if (!$isAdmin)
                            <a target="_blank" rel="noopener" href="{{ route('user.profile', ['locale' => app()->getLocale(), 'slug' => $u->username ?? \Str::slug($u->name), 'id' => $u->id]) }}"
                                class="text-black!">
                                {{ $u->name }}
                            </a>
                        @else
                            <span>{{ $u->name }}</span>
                        @endif

                        @if ($isBanned)
                            <span
                                class="ml-1.5! text-[10px]! px-1.5! py-0.5! bg-[#c00]! text-white! rounded-[2px]!"
                            title="{{ $u->banned_reason ? 'Reason: ' . $u->banned_reason : 'No reason given' }}{{ $u->banned_until ? ' · Until: ' . $u->banned_until : ' · Permanent' }}">
                                BANNED
                            </span>
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
            <div class="flex! gap-2! items-center! flex-wrap! max-md:gap-1.5! max-md:w-full! max-md:justify-end!">
                <div class="flex! gap-0.5! p-0.5! bg-[#f8f8f8]! border! border-solid! border-[#eee]! rounded! max-md:gap-px!">
                    <button type="button" id="bulk-unban" class="w-[52px]! h-[44px]! border! border-solid! border-[#bbb]! bg-white! flex! flex-col! items-center! justify-center! gap-0.5! cursor-pointer! transition-colors! transition-opacity! duration-150! hover:bg-[#f7f7f7]! active:bg-[#dcdcdc]! disabled:opacity-35! disabled:pointer-events-none! max-md:w-10! max-md:h-[38px]! max-md:gap-px! max-md:[&_svg]:w-4! max-md:[&_svg]:h-4!" title="Unban">
                        {!! $icoUnban !!}
                        <span class="text-[10px]! text-[#666]! leading-none! max-md:hidden!">Unban</span>
                    </button>

                    <button type="button" id="bulk-delete" class="w-[52px]! h-[44px]! border! border-solid! border-[#bbb]! bg-white! flex! flex-col! items-center! justify-center! gap-0.5! cursor-pointer! transition-colors! transition-opacity! duration-150! hover:bg-[#f7f7f7]! active:bg-[#dcdcdc]! disabled:opacity-35! disabled:pointer-events-none! max-md:w-10! max-md:h-[38px]! max-md:gap-px! max-md:[&_svg]:w-4! max-md:[&_svg]:h-4!" title="Delete">
                        {!! $icoDelete !!}
                        <span class="text-[10px]! text-[#666]! leading-none! max-md:hidden!">Delete</span>
                    </button>
                </div>

                <a href="{{ route('admin.users', request()->except('select')) }}"
                    title="Clear selection" class="w-[52px]! h-[44px]! border! border-solid! border-[#bbb]! bg-white! flex! flex-col! items-center! justify-center! gap-0.5! cursor-pointer! transition-colors! transition-opacity! duration-150! hover:bg-[#f7f7f7]! active:bg-[#dcdcdc]! disabled:opacity-35! disabled:pointer-events-none! max-md:w-10! max-md:h-[38px]! max-md:gap-px! max-md:[&_svg]:w-4! max-md:[&_svg]:h-4! no-underline! text-[#666]!">
                    {!! $icoX !!}
                    <span class="text-[10px]! text-[#666]! leading-none! max-md:hidden!">Clear</span>
                </a>
            </div>
        </x-slot:footer>

    </x-admin.list-table-box>

    <x-admin.detail-panel title="user" :closeRoute="route('admin.users', request()->except('select'))">
        <form method="post" action="{{ route('admin.users.save') }}" class="m-0!">
            @csrf
            <input type="hidden" name="id" value="{{ $selectedUser->id ?? '' }}">

            <div class="inline-block! bg-[#e6e6e6]! border! border-solid! border-[#9e9e9e]! border-b-0! px-2.5! py-[3px]! rounded-tl! rounded-tr! font-bold! text-sm! mt-0! max-md:text-xs! max-md:px-2! max-md:py-1!">Access Data</div>
            <div class="border! border-solid! border-[#9e9e9e]! rounded! p-2.5! mb-2.5! max-md:p-2!">
                <div class="grid! grid-cols-[160px_1fr]! gap-3! items-center! py-1.5! [&_label]:font-bold! [&_label]:text-sm! max-md:grid-cols-1! max-md:gap-1.5! max-md:[&_label]:text-xs!">
                    <label>username</label>
                    <input class="w-full! box-border! border! border-solid! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white!" type="text" name="username" value="{{ $selectedUser->name ?? '' }}">
                </div>

                <div class="grid! grid-cols-[160px_1fr]! gap-3! items-center! py-1.5! [&_label]:font-bold! [&_label]:text-sm! max-md:grid-cols-1! max-md:gap-1.5! max-md:[&_label]:text-xs!">
                    <label>password</label>
                    <div>
                        <input class="w-full! box-border! border! border-solid! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white!" type="password" name="password" value="">
                        @error('password') <span class="text-[#c00]! text-xs!">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="grid! grid-cols-[160px_1fr]! gap-3! items-center! py-1.5! [&_label]:font-bold! [&_label]:text-sm! max-md:grid-cols-1! max-md:gap-1.5! max-md:[&_label]:text-xs!">
                    <label>level</label>
                    <select class="w-full! box-border! border! border-solid! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white!" name="level">
                        @foreach ($levels as $k => $label)
                            @if ($k !== '')
                                <option value="{{ $k }}"
                                    {{ ($selectedUser?->level?->name ?? '') === $k ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endif
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="inline-block! bg-[#e6e6e6]! border! border-solid! border-[#9e9e9e]! border-b-0! px-2.5! py-[3px]! rounded-tl! rounded-tr! font-bold! text-sm! mt-2.5! max-md:text-xs! max-md:px-2! max-md:py-1!">other</div>
            <div class="border! border-solid! border-[#9e9e9e]! rounded! p-2.5! mb-2.5! max-md:p-2!">

                <div class="grid! grid-cols-[160px_1fr]! gap-3! items-center! py-1.5! [&_label]:font-bold! [&_label]:text-sm! max-md:grid-cols-1! max-md:gap-1.5! max-md:[&_label]:text-xs!">
                    <label>first name</label>
                    <input class="w-full! box-border! border! border-solid! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white!" type="text" name="first_name"
                        value="{{ $selectedUser->first_name ?? '' }}">
                </div>

                <div class="grid! grid-cols-[160px_1fr]! gap-3! items-center! py-1.5! [&_label]:font-bold! [&_label]:text-sm! max-md:grid-cols-1! max-md:gap-1.5! max-md:[&_label]:text-xs!">
                    <label>last name</label>
                    <input class="w-full! box-border! border! border-solid! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white!" type="text" name="last_name"
                        value="{{ $selectedUser->last_name ?? '' }}">
                </div>

                <div class="grid! grid-cols-[160px_1fr]! gap-3! items-center! py-1.5! [&_label]:font-bold! [&_label]:text-sm! max-md:grid-cols-1! max-md:gap-1.5! max-md:[&_label]:text-xs!">
                    <label>nickname</label>
                    <input class="w-full! box-border! border! border-solid! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white!" type="text" name="nickname"
                        value="{{ $selectedUser->nickname ?? '' }}">
                </div>

                <div class="grid! grid-cols-[160px_1fr]! gap-3! items-center! py-1.5! [&_label]:font-bold! [&_label]:text-sm! max-md:grid-cols-1! max-md:gap-1.5! max-md:[&_label]:text-xs!">
                    <label>location</label>
                    <input class="w-full! box-border! border! border-solid! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white!" type="text" name="city"
                        value="{{ $selectedUser->city ?? '' }}">
                </div>

                <div class="grid! grid-cols-[160px_1fr]! gap-3! items-center! py-1.5! [&_label]:font-bold! [&_label]:text-sm! max-md:grid-cols-1! max-md:gap-1.5! max-md:[&_label]:text-xs!">
                    <label>identify as</label>
                    <select class="w-full! box-border! border! border-solid! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white!" name="identify_mode">
                        <option value="full" {{ ($selectedUser->identify_mode ?? 'full') === 'full' ? 'selected' : '' }}>full name</option>
                        <option value="name" {{ ($selectedUser->identify_mode ?? '') === 'name' ? 'selected' : '' }}>first name</option>
                        <option value="nick" {{ ($selectedUser->identify_mode ?? '') === 'nick' ? 'selected' : '' }}>nickname</option>
                    </select>
                </div>

                <div class="grid! grid-cols-[160px_1fr]! gap-3! items-center! py-1.5! [&_label]:font-bold! [&_label]:text-sm! max-md:grid-cols-1! max-md:gap-1.5! max-md:[&_label]:text-xs!">
                    <label>verified</label>
                    <input type="checkbox" name="verified" value="1"
                        {{ $selectedUser->verified ?? 0 ? 'checked' : '' }}>
                </div>

                <div class="grid! grid-cols-[160px_1fr]! gap-3! items-center! py-1.5! [&_label]:font-bold! [&_label]:text-sm! max-md:grid-cols-1! max-md:gap-1.5! max-md:[&_label]:text-xs!">
                    <label>email</label>
                    <input class="w-full! box-border! border! border-solid! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white!" type="text" name="email" value="{{ $selectedUser->email ?? '' }}">
                </div>

                <div class="grid! grid-cols-[160px_1fr]! gap-3! items-center! py-1.5! [&_label]:font-bold! [&_label]:text-sm! max-md:grid-cols-1! max-md:gap-1.5! max-md:[&_label]:text-xs!">
                    <label>locale</label>
                    <select class="w-full! box-border! border! border-solid! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white!" name="locale">
                        @foreach ($locales as $k => $label)
                            @if ($k !== '')
                                <option value="{{ $k }}"
                                    {{ \App\Support\Locale::toShort($selectedUser->locale ?? '') === $k ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endif
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="flex! justify-end! mt-1.5!">
                <button class="border! border-solid! border-[#7d7d7d]! bg-[#e6e6e6]! px-2.5! py-1! rounded-[3px]! cursor-pointer! hover:bg-[#dcdcdc]! max-md:text-xs! max-md:px-3! max-md:py-1.5!" type="submit">save</button>
            </div>
        </form>
    </x-admin.detail-panel>
@endsection

@include('admin.partials.bulk-js', [
    'banRoute' => route('admin.users.bulkBan'),
    'deleteRoute' => route('admin.users.bulkDelete'),
    'unbanRoute' => route('admin.users.bulkUnban'),
    'noun' => 'users',
    'emptyMsg' => 'No users selected',
])
