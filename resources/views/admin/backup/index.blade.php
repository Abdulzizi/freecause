@extends('admin.layouts.app')

@section('title', 'Database Backup')

@push('head')
<style>
    .backup-list { width: 100%; border-collapse: collapse; }
    .backup-list th, .backup-list td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; }
    .backup-list th { background: #f9f9f9; font-weight: 600; font-size: 12px; text-transform: uppercase; color: #666; }
    .backup-actions { display: flex; gap: 8px; }
    .backup-size { color: #888; font-size: 12px; }
</style>
@endpush

@section('content')
<h1>database backup</h1>

<div class="dash-section" style="margin-bottom: 20px;">
    <h2>Create New Backup</h2>
    <p style="color: #666; margin-bottom: 15px;">
        Create a backup of your database. Backups are stored in <code>storage/app/backups/</code>.
    </p>
    
    <form method="post" action="{{ route('admin.backup.create') }}">
        @csrf
        <button class="fc-btn" type="submit">Create Backup</button>
    </form>
</div>

<div class="dash-section">
    <h2>Existing Backups</h2>
    
    @if(count($backups) > 0)
    <table class="backup-list">
        <thead>
            <tr>
                <th>Filename</th>
                <th>Size</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($backups as $backup)
            <tr>
                <td>{{ $backup['name'] }}</td>
                <td class="backup-size">{{ number_format($backup['size'] / 1024, 1) }} KB</td>
                <td>{{ date('Y-m-d H:i:s', $backup['modified']) }}</td>
                <td class="backup-actions">
                    <a href="{{ route('admin.backup.download', $backup['name']) }}" class="fc-btn">Download</a>
                    <form method="post" action="{{ route('admin.backup.delete', $backup['name']) }}" style="display:inline;">
                        @csrf
                        <button class="fc-btn" type="submit" onclick="return confirm('Delete this backup?')">Delete</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <p style="color: #888; padding: 20px; text-align: center;">No backups found.</p>
    @endif
</div>
@endsection
