@extends('admin.layouts.app')

@section('title', 'Languages')

@section('content')

    <h1>languages</h1>

    <div class="fc-tab">add language</div>
    <div class="fc-box">
        <form method="post" action="{{ route('admin.languages.store') }}">
            @csrf

            <div class="fc-row">
                <label style="width:120px;">code</label>
                <input class="fc-input" name="code" placeholder="e.g. es" required>
            </div>

            <div class="fc-row">
                <label style="width:120px;">name</label>
                <input class="fc-input" name="name" placeholder="Spanish" required>
            </div>

            <div style="text-align:right; margin-top:10px;">
                <button class="fc-btn">create language</button>
            </div>
        </form>
    </div>

    <div class="fc-tab">existing languages</div>
    <div class="fc-box">

        @forelse($languages as $lang)
            <div
                style="
            display:flex;
            justify-content:space-between;
            align-items:flex-start;
            padding:15px 0;
            border-bottom:1px solid #ddd;
        ">

                {{-- LEFT INFO --}}
                <div style="min-width:200px;">
                    <div style="font-size:16px; font-weight:bold;">
                        {{ strtoupper($lang->code) }} — {{ $lang->name }}

                        @if ($lang->is_default)
                            <span
                                style="
                            background:#28a745;
                            color:#fff;
                            padding:2px 6px;
                            border-radius:4px;
                            font-size:12px;
                            margin-left:6px;
                        ">
                                default
                            </span>
                        @endif
                    </div>

                    <div style="margin-top:5px; font-size:13px; color:#666;">
                        status: {{ $lang->is_active ? 'active' : 'inactive' }}
                    </div>
                </div>

                <div style="flex:1; max-width:500px;">

                    <form method="post" action="{{ route('admin.languages.update', $lang) }}"
                        style="display:flex; gap:10px; align-items:center; margin-bottom:6px;">
                        @csrf
                        @method('PUT')

                        <input class="fc-input" name="name" value="{{ $lang->name }}" style="max-width:180px;">

                        <label style="display:flex; align-items:center; gap:5px;">
                            <input type="checkbox" name="is_active" {{ $lang->is_active ? 'checked' : '' }}>
                            active
                        </label>

                        <button class="fc-btn">save</button>
                    </form>

                    @if (!$lang->is_default)
                        <div style="display:flex; gap:8px;">

                            <form method="post" action="{{ route('admin.languages.default', $lang) }}">
                                @csrf
                                <button class="fc-btn" type="submit">
                                    set default
                                </button>
                            </form>

                            <form method="post" action="{{ route('admin.languages.destroy', $lang) }}"
                                onsubmit="return confirm('delete this language?')">
                                @csrf
                                @method('DELETE')
                                <button class="fc-btn" type="submit">
                                    delete
                                </button>
                            </form>

                        </div>
                    @endif

                </div>

            </div>

        @empty
            <div style="padding:20px; text-align:center; color:#777;">
                no languages created yet
            </div>
        @endforelse

    </div>

@endsection
