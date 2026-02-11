@extends('admin.layouts.app')

@section('title', 'Users')

@section('content')
    <h1>users ({{ number_format($approxTotal) }})</h1>

    {{-- @if(collect($filters)->filter(fn($v) => $v !== '')->count())
        <div style="font-size:11px; color:#900; margin-bottom:6px;">
            filters active
        </div>
    @endif --}}

    @if(session('success'))
        <div class="fc-success">{{ session('success') }}</div>
    @endif

    <div class="fc-tab">filter users</div>
    <div class="fc-box">
        <form method="get" action="{{ route('admin.users') }}"
            style="margin:0; display:flex; gap:6px; align-items:center; flex-wrap:wrap;">

            <input class="fc-input" style="max-width:90px;" name="id" placeholder="ID" value="{{ $filters['id'] }}">

            <input class="fc-input" style="max-width:160px;" name="name" placeholder="First name"
                value="{{ $filters['name'] }}">

            <input class="fc-input" style="max-width:160px;" name="last_name" placeholder="Last name"
                value="{{ $filters['last_name'] }}">

            <input class="fc-input" style="max-width:220px;" name="email" placeholder="Email"
                value="{{ $filters['email'] }}">

            <input class="fc-input" style="max-width:130px;" name="ip" placeholder="IP" value="{{ $filters['ip'] }}">

            <select class="fc-select" name="level" style="max-width:140px;">
                @foreach($levels as $k => $label)
                    <option value="{{ $k }}" {{ $filters['level'] === $k ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>

            <select class="fc-select" name="locale" style="max-width:140px;">
                @foreach($locales as $k => $label)
                    <option value="{{ $k }}" {{ $filters['locale'] === $k ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>

            <button class="fc-btn" type="submit">apply</button>
            <a class="fc-btn" href="{{ route('admin.users') }}" style="text-decoration:none;">reset</a>
        </form>
    </div>

    <div class="fc-box" style="margin-top:10px;">
        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:6px;">
            @if($users->hasPages())
                @include('admin.partials.simple-window-pagination', ['p' => $users])
            @endif
        </div>

        <table style="width:100%; border-collapse:collapse;">
            <thead>
                <tr style="border-bottom:1px solid #ccc;" onmouseover="this.style.background='#f7f7f7'" onmouseout="this.style.background=''">
                    <th style="text-align:left; width:90px;">id</th>
                    <th style="width:26px;"></th>
                    <th style="width:26px; text-align:left;">v</th>
                    <th style="text-align:left;">email</th>
                    <th style="text-align:left;">first name</th>
                    <th style="text-align:left;">last name</th>
                    <th style="text-align:left; width:90px;">locale</th>
                    <th style="text-align:left; width:160px;">registered</th>
                </tr>
            </thead>

            <tbody>
                @foreach($users as $u)
                    <tr style="border-bottom:1px solid #eee;">
                        <td>
                            <a href="{{ route('admin.users', array_merge(request()->query(), ['select' => $u->id])) }}"
                                style="text-decoration:none; color:#000;">
                                {{ $u->id }}
                            </a>
                        </td>

                        <td style="text-align:left;">
                            <input type="checkbox" class="bulk-checkbox" name="bulk[]" value="{{ $u->id }}">
                        </td>

                        <td style="text-align:left;">
                            @if(($u->is_verified ?? 0))
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
            </tbody>
        </table>
        <div style="margin-top:6px; font-size:11px; color:#555;">
            @include('admin.partials.bulk-toolbar', [
                'banRoute' => route('admin.users.bulkBan'),
                'banLabel' => 'Banned',
                'banConfirm' => 'Ban selected users?'
            ])
        </div>

    </div>

    <div class="fc-tab" style="margin-top:14px;">user</div>
    <div class="fc-box">
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
                        @foreach($levels as $k => $label)
                            @if($k !== '')
                                <option value="{{ $k }}" {{ ($selectedUser->level ?? '') === $k ? 'selected' : '' }}>
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
                    <input class="fc-input" type="text" name="last_name" value="{{ $selectedUser->last_name ?? '' }}">
                </div>

                <div class="fc-row">
                    <label>verified</label>
                    <input type="checkbox" name="verified" value="1" {{ ($selectedUser->is_verified ?? 0) ? 'checked' : '' }}>
                </div>

                <div class="fc-row">
                    <label>email</label>
                    <input class="fc-input" type="text" name="email" value="{{ $selectedUser->email ?? '' }}">
                </div>
                <div class="fc-row">
                    <label>locale</label>
                    <select class="fc-select" name="locale" style="max-width:180px;">
                        @foreach($locales as $k => $label)
                            @if($k !== '')
                                <option value="{{ $k }}" {{ ($selectedUser->locale ?? '') === $k ? 'selected' : '' }}>
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
    </div>
@endsection

<script>
    document.addEventListener('DOMContentLoaded', function () {
        function getBoxes() {
            return Array.from(document.querySelectorAll('.bulk-checkbox'));
        }

        document.getElementById('bulk-all')?.addEventListener('click', function () {
            getBoxes().forEach(cb => cb.checked = true);
        });

        document.getElementById('bulk-none')?.addEventListener('click', function () {
            getBoxes().forEach(cb => cb.checked = false);
        });

        document.getElementById('bulk-invert')?.addEventListener('click', function () {
            getBoxes().forEach(cb => cb.checked = !cb.checked);
        });

        document.getElementById('bulk-banned')?.addEventListener('click', function () {
            const boxes = getBoxes();
            const ids = boxes.filter(cb => cb.checked).map(cb => cb.value);

            if (!ids.length) {
                alert('No users selected');
                return;
            }

            const msg = ids.length === 1
                ? 'Ban 1 selected user?'
                : 'Ban ' + ids.length + ' selected users?';

            if (!confirm(msg)) {
                return;
            }

            fetch('{{ route('admin.users.bulkBan') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ ids })
            })
            .then(r => r.json())
            .then(res => {
                if (res.ok) {
                    location.reload();
                } else {
                    alert('Operation failed');
                }
            })
            .catch(() => alert('Network error'));
        });

        function updateBulkState() {
            const anyChecked = getBoxes().some(cb => cb.checked);
            const ban = document.getElementById('bulk-banned');
            ban.style.opacity = anyChecked ? '1' : '.4';
            ban.style.pointerEvents = anyChecked ? 'auto' : 'none';
        }

        getBoxes().forEach(cb => cb.addEventListener('change', updateBulkState));
        updateBulkState();
    });
</script>
