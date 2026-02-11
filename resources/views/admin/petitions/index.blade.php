@extends('admin.layouts.app')

@section('title', 'Petitions')

@section('content')

    <h1>petitions ({{ number_format($approxTotal) }})</h1>

    @if(session('success'))
        <div class="fc-success">{{ session('success') }}</div>
    @endif

    <x-admin.filter-box title="filter petitions" :action="route('admin.petitions')" :reset="route('admin.petitions')">
        <input type="hidden" name="locale" value="{{ $locale }}">

        <input class="fc-input" name="id" placeholder="ID" value="{{ $filters['id'] ?? '' }}" style="max-width:90px;">
        <input class="fc-input" name="title" placeholder="Title" value="{{ $filters['title'] ?? '' }}"
            style="max-width:220px;">

        <label>
            <input type="checkbox" name="featured" value="1" {{ ($filters['featured'] ?? '') !== '' ? 'checked' : '' }}>
            featured only
        </label>
    </x-admin.filter-box>

    <x-admin.list-table-box :p="$petitions" :bulk="[
            'banRoute' => route('admin.petitions.bulkBan'),
            'banLabel' => 'Banned',
            'banConfirm' => 'Ban selected petitions?',
        ]">
        <x-slot:thead>
            <tr style="border-bottom:1px solid #ccc;" onmouseover="this.style.background='#f7f7f7'"
                onmouseout="this.style.background=''">

                <th style="text-align:left;">id</th>
                <th style="width:26px;"></th>
                <th style="width:26px;">A</th>
                <th style="width:26px;">P</th>
                <th style="width:26px;">F</th>
                <th>Signatures</th>
                <th>Title</th>
                <th>Date</th>
            </tr>
        </x-slot:thead>

        <x-slot:tbody>
            @foreach($petitions as $p)
                <tr style="border-bottom:1px solid #eee;">
                    <td>
                        <a href="{{ route('admin.petitions', array_merge(request()->query(), ['select' => $p->id])) }}">
                            {{ $p->id }}
                        </a>
                    </td>

                    <td style="text-align:left;">
                        <input type="checkbox" class="bulk-checkbox" value="{{ $p->id }}">
                    </td>

                    <td>
                        @if($p->is_active)
                            <span style="color:#5c8f3a; font-weight:bold;">✔</span>
                        @else
                            <span style="color:#c00;">✖</span>
                        @endif
                    </td>

                    <td>
                        @if($p->status === 'published')
                            <span style="color:#5c8f3a; font-weight:bold;">✔</span>
                        @else
                            <span style="color:#c00;">✖</span>
                        @endif
                    </td>

                    <td>
                        @if($p->is_featured)
                            <span style="color:#5c8f3a; font-weight:bold;">✔</span>
                        @else
                            <span style="color:#c00;">✖</span>
                        @endif
                    </td>

                    <td>{{ $p->signature_count }} / {{ $p->goal_signatures }}</td>
                    <td>{{ $p->title }}</td>
                    <td>{{ $p->created_at }}</td>
                </tr>
            @endforeach
        </x-slot:tbody>
    </x-admin.list-table-box>

    <x-admin.detail-panel title="petition">
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
                    <input class="fc-input" type="number" name="goal_signatures"
                        value="{{ $selectedPetition->goal_signatures ?? 100 }}">
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
    </x-admin.detail-panel>

@endsection

@include('admin.partials.bulk-js', [
    'banRoute' => route('admin.petitions.bulkBan'),
    'noun' => 'petitions',
    'emptyMsg' => 'No petitions selected',
])
