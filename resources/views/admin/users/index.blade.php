@extends('admin.layouts.app')

@section('title', 'Utenti')

@section('content')
    <h1>utenti ({{ number_format($approxTotal) }})</h1>

    @if(session('success'))
        <div class="fc-success">{{ session('success') }}</div>
    @endif

    <div class="fc-tab">filtra utenti</div>
    <div class="fc-box">
        <form method="get" action="{{ route('admin.users') }}"
            style="margin:0; display:flex; gap:6px; align-items:center; flex-wrap:wrap;">
            <input class="fc-input" style="max-width:90px;" name="id" placeholder="ID" value="{{ $filters['id'] }}">
            <input class="fc-input" style="max-width:160px;" name="name" placeholder="Nome" value="{{ $filters['name'] }}">
            <input class="fc-input" style="max-width:160px;" name="surname" placeholder="Cognome"
                value="{{ $filters['surname'] }}">
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

            <button class="fc-btn" type="submit">applica</button>
            <a class="fc-btn" href="{{ route('admin.users') }}" style="text-decoration:none;">reset</a>
        </form>
    </div>

    <div class="fc-box" style="margin-top:10px;">
        <div style="display:flex; justify-content:flex-end; margin-bottom:6px;">
            @include('admin.partials.simple-window-pagination', ['p' => $users])
        </div>

        <table style="width:100%; border-collapse:collapse;">
            <thead>
                <tr style="border-bottom:1px solid #ccc;">
                    <th style="text-align:left; width:90px;">&nbsp;</th>
                    <th style="width:30px;">v</th>
                    <th style="text-align:left;">email</th>
                    <th style="text-align:left;">nome</th>
                    <th style="text-align:left;">cognome</th>
                    <th style="text-align:left; width:90px;">locale</th>
                    <th style="text-align:left; width:160px;">registrato</th>
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
                        <td><input type="checkbox" name="bulk[]" value="{{ $u->id }}"></td>
                        <td><a href="mailto:{{ $u->email }}">{{ $u->email }}</a></td>
                        <td>{{ $u->name }}</td>
                        <td>{{ $u->surname }}</td>
                        <td>{{ $u->locale }}</td>
                        <td>{{ $u->created_at }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div style="display:flex; justify-content:flex-end; margin-top:6px;">
            @include('admin.partials.simple-window-pagination', ['p' => $users])
        </div>
    </div>

    {{-- lower form placeholder (like legacy "Utente") --}}
    <div class="fc-tab" style="margin-top:14px;">utente</div>
    <div class="fc-box">
        <form method="post" action="{{ route('admin.users.save') }}" style="margin:0;">
            @csrf
            <input type="hidden" name="id" value="{{ $selectedUser->id ?? '' }}">

            <div class="fc-tab" style="margin-top:0;">dati di accesso</div>
            <div class="fc-box">
                <div class="fc-row">
                    <label>nome utente</label>
                    <input class="fc-input" type="text" name="username" value="">
                </div>
                <div class="fc-row">
                    <label>password</label>
                    <input class="fc-input" type="text" name="password" value="">
                </div>
                <div class="fc-row">
                    <label>livello</label>
                    <select class="fc-select" name="level" style="max-width:180px;">
                        @foreach($levels as $k => $label)
                            @if($k !== '')
                                <option value="{{ $k }}">{{ $label }}</option>
                            @endif
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="fc-tab">altro</div>
            <div class="fc-box">
                <div class="fc-row">
                    <label>nome</label>
                    <input class="fc-input" type="text" name="name" value="{{ $selectedUser->name ?? '' }}">
                </div>
                <div class="fc-row">
                    <label>cognome</label>
                    <input class="fc-input" type="text" name="surname" value="{{ $selectedUser->surname ?? '' }}">
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
                                <option value="{{ $k }}" {{ ($selectedUser->locale ?? '') === $k ? 'selected' : '' }}>{{ $label }}
                                </option>
                            @endif
                        @endforeach
                    </select>
                </div>
            </div>

            <div style="display:flex; justify-content:flex-end; margin-top:6px;">
                <button class="fc-btn" type="submit">aggiungi</button>
            </div>
        </form>
    </div>
@endsection
