@extends('admin.layouts.app')

@section('title', 'Petitions')

@section('content')

    <h1>petitions ({{ number_format($approxTotal) }})</h1>

    @if(session('success'))
        <div class="fc-success">{{ session('success') }}</div>
    @endif

    <div class="fc-tab">filter petitions</div>
    <div class="fc-box">
        <form method="get" action="{{ route('admin.petitions') }}" style="display:flex; gap:6px; flex-wrap:wrap;">
            <input type="hidden" name="locale" value="{{ $locale }}">

            <input class="fc-input" name="id" placeholder="ID" value="{{ $filters['id'] ?? '' }}" style="max-width:90px;">
            <input class="fc-input" name="title" placeholder="Title" value="{{ $filters['title'] ?? '' }}"
                style="max-width:220px;">

            <label>
                <input type="checkbox" name="featured" value="1" {{ ($filters['featured'] ?? '') !== '' ? 'checked' : '' }}>
                featured only
            </label>

            <button class="fc-btn" type="submit">apply</button>
            <a class="fc-btn" href="{{ route('admin.petitions') }}" style="text-decoration:none;">reset</a>
        </form>
    </div>

    <div class="fc-box" style="margin-top:10px;">

        @include('admin.partials.simple-window-pagination', ['p' => $petitions])

        <table style="width:100%; border-collapse:collapse;">
            <thead>
                <tr style="border-bottom:1px solid #ccc;">
                    <th>ID</th>
                    <th>A</th>
                    <th>P</th>
                    <th>F</th>
                    <th>Signatures</th>
                    <th>Title</th>
                    <th>Date</th>
                </tr>
            </thead>

            <tbody>
                @foreach($petitions as $p)
                    <tr style="border-bottom:1px solid #eee;">
                        <td>
                            <a href="{{ route('admin.petitions', array_merge(request()->query(), ['select' => $p->id])) }}">
                                {{ $p->id }}
                            </a>
                        </td>

                        <td>{!! $p->is_active ? '✔' : '✖' !!}</td>
                        <td>{!! $p->status === 'published' ? '✔' : '✖' !!}</td>
                        <td>{!! $p->is_featured ? '✔' : '✖' !!}</td>

                        <td>{{ $p->signature_count }} / {{ $p->goal_signatures }}</td>
                        <td>{{ $p->title }}</td>
                        <td>{{ $p->created_at }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

    </div>

    <div class="fc-tab" style="margin-top:14px;">petition</div>
    <div class="fc-box">

        @if($selectedPetition)

            <form method="post" action="{{ route('admin.petitions.save') }}">
                @csrf
                <input type="hidden" name="id" value="{{ $selectedPetition->id }}">
                <input type="hidden" name="locale" value="{{ $locale }}">

                <div class="fc-row">
                    <label>active</label>
                    <input type="checkbox" name="is_active" value="1" {{ $selectedPetition->is_active ? 'checked' : '' }}>
                </div>

                <div class="fc-row">
                    <label>status</label>
                    <select class="fc-select" name="status">
                        <option value="draft" {{ ($selectedPetition->status ?? '') === 'draft' ? 'selected' : '' }}>
                            draft
                        </option>
                        <option value="published" {{ ($selectedPetition->status ?? '') === 'published' ? 'selected' : '' }}>
                            published
                        </option>
                    </select>
                </div>

                <div class="fc-row">
                    <label>featured</label>
                    <input type="checkbox" name="is_featured" value="1" {{ $selectedPetition->is_featured ? 'checked' : '' }}>
                </div>

                <div class="fc-row">
                    <label>title</label>
                    <input class="fc-input" type="text" name="title" value="{{ $selectedTranslation->title ?? '' }}">
                </div>

                <div class="fc-row">
                    <label>url slug</label>
                    <input class="fc-input" type="text" name="slug" value="{{ $selectedTranslation->slug ?? '' }}">
                </div>

                <div class="fc-row">
                    <label>signature goal</label>
                    <input class="fc-input" type="number" name="goal_signatures" value="{{ $selectedPetition->goal_signatures ?? 100 }}">
                </div>

                <div class="fc-row">
                    <label>text</label>
                    <textarea class="fc-input" name="text" rows="8">{{ $selectedTranslation->description ?? '' }}</textarea>
                </div>

                <div style="display:flex; justify-content:flex-end;">
                    <button class="fc-btn" type="submit">save</button>
                </div>
            </form>

        @else
            <div style="color:#777;">select a petition to edit</div>
        @endif

    </div>

@endsection
