@extends('admin.layouts.app')

@section('title', 'Translation Manager')

@push('head')
<style>
    .trans-manager { margin-top: 20px; }
    .trans-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px; }
    .trans-controls { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; }
    .trans-table { width: 100%; border-collapse: collapse; background: #fff; }
    .trans-table th, .trans-table td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; }
    .trans-table th { background: #f5f5f5; font-weight: 600; font-size: 13px; }
    .trans-table tr:hover { background: #f9f9f9; }
    .trans-key { font-family: monospace; font-size: 13px; color: #666; }
    .trans-value { min-width: 300px; }
    .trans-input { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; }
    .trans-input:focus { border-color: #007bff; outline: none; }
    .trans-missing { background: #fff3cd !important; }
    .trans-missing .trans-input { border-color: #ffc107; }
    .trans-default { font-size: 12px; color: #999; margin-top: 4px; }
    .trans-actions { white-space: nowrap; }
    .trans-btn { padding: 6px 12px; border: none; border-radius: 4px; cursor: pointer; font-size: 13px; }
    .trans-btn-primary { background: #007bff; color: #fff; }
    .trans-btn-success { background: #28a745; color: #fff; }
    .trans-btn-warning { background: #ffc107; color: #000; }
    .trans-btn-danger { background: #dc3545; color: #fff; }
    .trans-btn:hover { opacity: 0.9; }
    .trans-group-tabs { display: flex; gap: 5px; margin-bottom: 20px; flex-wrap: wrap; }
    .trans-group-tab { padding: 8px 16px; border: 1px solid #ddd; border-radius: 4px; cursor: pointer; background: #fff; font-size: 13px; }
    .trans-group-tab.active { background: #007bff; color: #fff; border-color: #007bff; }
    .trans-group-tab:hover:not(.active) { background: #f5f5f5; }
    .trans-search { padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; width: 250px; }
    .trans-stats { display: flex; gap: 20px; margin-bottom: 20px; font-size: 14px; color: #666; }
    .trans-stat { display: flex; align-items: center; gap: 5px; }
    .trans-stat strong { color: #333; }
</style>
@endpush

@section('content')
<div class="trans-manager">
    <div class="trans-header">
        <h1>Translation Manager</h1>
        <div class="trans-controls">
            <select class="fc-select" onchange="window.location.href='{{ route('admin.translation-manager.index') }}?locale='+this.value+'&group={{ $group }}'">
                @foreach($languages as $lang)
                    <option value="{{ $lang->code }}" {{ $lang->code === $locale ? 'selected' : '' }}>
                        {{ strtoupper($lang->code) }} - {{ $lang->name }}
                    </option>
                @endforeach
            </select>
            
            <form method="GET" class="d-inline">
                <input type="hidden" name="locale" value="{{ $locale }}">
                <input type="text" name="search" class="trans-search" placeholder="Search keys..." value="{{ $search }}">
                <button type="submit" class="trans-btn trans-btn-primary">Search</button>
            </form>

            <a href="{{ route('admin.translation-manager.export', ['locale' => $locale]) }}" class="trans-btn trans-btn-success">Export JSON</a>
            
            <form method="POST" action="{{ route('admin.translation-manager.import') }}" enctype="multipart/form-data" class="d-inline">
                @csrf
                <input type="hidden" name="locale" value="{{ $locale }}">
                <input type="file" name="file" accept=".json" style="display: inline-block; width: auto;">
                <button type="submit" class="trans-btn trans-btn-warning">Import JSON</button>
            </form>

            <form method="POST" action="{{ route('admin.translation-manager.clear.cache') }}" class="d-inline">
                @csrf
                <button type="submit" class="trans-btn trans-btn-danger">Clear Cache</button>
            </form>
        </div>
    </div>

    <div class="trans-group-tabs">
        @foreach($groups as $g)
            <a href="{{ route('admin.translation-manager.index', ['locale' => $locale, 'group' => $g]) }}" 
               class="trans-group-tab {{ $group === $g ? 'active' : '' }}">
                {{ $g }}
            </a>
        @endforeach
    </div>

    <div class="trans-stats">
        <div class="trans-stat">Total keys: <strong>{{ count($defaultTranslations) }}</strong></div>
        <div class="trans-stat">Translated: <strong>{{ count($translations) }}</strong></div>
        <div class="trans-stat">Missing: <strong>{{ count($missingKeys) }}</strong></div>
        <div class="trans-stat">Coverage: <strong>{{ count($defaultTranslations) > 0 ? round((count($translations) / count($defaultTranslations)) * 100) : 0 }}%</strong></div>
    </div>

    @if(count($missingKeys) > 0)
        <div style="margin-bottom: 20px;">
            <form method="POST" action="{{ route('admin.translation-manager.copy.source') }}">
                @csrf
                <input type="hidden" name="locale" value="{{ $locale }}">
                <input type="hidden" name="group" value="{{ $group }}">
                <button type="submit" class="trans-btn trans-btn-warning">
                    Copy {{ count($missingKeys) }} missing keys from {{ config('app.locale', 'en') }}
                </button>
            </form>
        </div>
    @endif

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <form method="POST" action="{{ route('admin.translation-manager.bulk.update') }}">
        @csrf
        <input type="hidden" name="locale" value="{{ $locale }}">
        <input type="hidden" name="group" value="{{ $group }}">
        
        <table class="trans-table">
            <thead>
                <tr>
                    <th style="width: 250px;">Key</th>
                    <th>{{ strtoupper(config('app.locale', 'en')) }} (Source)</th>
                    <th>{{ strtoupper($locale) }}</th>
                    <th style="width: 100px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($defaultTranslations as $key => $defaultValue)
                    @if($search && stripos($key, $search) === false && stripos($defaultValue, $search) === false)
                        @continue
                    @endif
                    
                    @php
                        $value = $translations[$key] ?? '';
                        $isMissing = !isset($translations[$key]);
                    @endphp
                    
                    <tr class="{{ $isMissing ? 'trans-missing' : '' }}">
                        <td class="trans-key">{{ $key }}</td>
                        <td>
                            <div class="trans-default">{{ $defaultValue }}</div>
                        </td>
                        <td class="trans-value">
                            <input type="text" 
                                   name="translations[{{ $key }}]" 
                                   class="trans-input" 
                                   value="{{ $value }}"
                                   placeholder="{{ $isMissing ? 'Missing translation' : '' }}">
                        </td>
                        <td class="trans-actions">
                            @if(!$isMissing)
                                <button type="button" 
                                        class="trans-btn trans-btn-danger" 
                                        onclick="deleteTranslation({{ $translations[$key] ?? '' }})"
                                        style="padding: 4px 8px; font-size: 12px;">
                                    Delete
                                </button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" style="text-align: center; padding: 40px;">
                            No translations found for this group.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div style="margin-top: 20px; text-align: right;">
            <button type="submit" class="trans-btn trans-btn-primary" style="padding: 10px 20px; font-size: 14px;">
                Save All Changes
            </button>
        </div>
    </form>
</div>

<script>
function deleteTranslation(id) {
    if (confirm('Are you sure you want to delete this translation?')) {
        fetch(`/admin/translation-manager/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            }
        }).then(response => {
            if (response.ok) {
                location.reload();
            }
        });
    }
}
</script>
@endsection
