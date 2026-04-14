@extends('admin.layouts.app')

@section('title', 'Dashboard')

@push('head')
<style>
    .dash-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 16px;
        margin-bottom: 24px;
    }
    .stat-card {
        background: #fff;
        border: 1px solid #ddd;
        border-radius: 4px;
        padding: 20px;
    }
    .stat-card h3 {
        margin: 0 0 8px 0;
        font-size: 13px;
        color: #666;
        text-transform: uppercase;
    }
    .stat-card .value {
        font-size: 28px;
        font-weight: bold;
        color: #333;
    }
    .stat-card .sub {
        font-size: 12px;
        color: #888;
        margin-top: 4px;
    }
    .stat-card.users { border-left: 4px solid #3498db; }
    .stat-card.petitions { border-left: 4px solid #2ecc71; }
    .stat-card.signatures { border-left: 4px solid #9b59b6; }
    .stat-card.languages { border-left: 4px solid #e67e22; }

    .dash-section {
        background: #fff;
        border: 1px solid #ddd;
        border-radius: 4px;
        padding: 20px;
        margin-bottom: 20px;
    }
    .dash-section h2 {
        margin: 0 0 16px 0;
        font-size: 18px;
        color: #333;
        border-bottom: 1px solid #eee;
        padding-bottom: 10px;
    }

    .table-list {
        width: 100%;
        border-collapse: collapse;
    }
    .table-list th, .table-list td {
        padding: 10px 12px;
        text-align: left;
        border-bottom: 1px solid #eee;
    }
    .table-list th {
        background: #f9f9f9;
        font-weight: 600;
        font-size: 12px;
        text-transform: uppercase;
        color: #666;
    }
    .table-list tr:hover {
        background: #fafafa;
    }
    .status-badge {
        display: inline-block;
        padding: 3px 8px;
        border-radius: 3px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
    }
    .status-published { background: #d4edda; color: #155724; }
    .status-draft { background: #f8d7da; color: #721c24; }
    .status-pending { background: #fff3cd; color: #856404; }
    .status-verified { background: #d4edda; color: #155724; }

    .health-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 12px;
    }
    .health-item {
        display: flex;
        justify-content: space-between;
        padding: 8px 12px;
        background: #f9f9f9;
        border-radius: 4px;
    }
    .health-item .label { color: #666; }
    .health-item .val { font-weight: 600; color: #333; }

    .quick-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }
    .quick-btn {
        padding: 10px 20px;
        background: #3498db;
        color: #fff;
        text-decoration: none;
        border-radius: 4px;
        font-size: 13px;
    }
    .quick-btn:hover { background: #2980b9; }
</style>
@endpush

@section('content')
<h2>Dashboard</h2>

<div class="dash-grid">
    <div class="stat-card users">
        <h3>Total Users</h3>
        <div class="value">{{ number_format($stats['users_total']) }}</div>
        <div class="sub">{{ number_format($stats['users_verified']) }} verified ({{ round(($stats['users_verified'] / max($stats['users_total'], 1)) * 100) }}%)</div>
    </div>
    <div class="stat-card petitions">
        <h3>Total Petitions</h3>
        <div class="value">{{ number_format($stats['petitions_total']) }}</div>
        <div class="sub">{{ number_format($stats['petitions_published']) }} published</div>
    </div>
    <div class="stat-card signatures">
        <h3>Total Signatures</h3>
        <div class="value">{{ number_format($stats['signatures_total']) }}</div>
        <div class="sub">{{ number_format($stats['signatures_today']) }} today</div>
    </div>
    <div class="stat-card languages">
        <h3>Active Languages</h3>
        <div class="value">{{ $stats['languages_active'] }}</div>
        <div class="sub">enabled</div>
    </div>
</div>

<div class="dash-grid">
    <div class="stat-card" style="border-left: 4px solid #e74c3c;">
        <h3>New Users (Today)</h3>
        <div class="value">{{ number_format($stats['users_new_today']) }}</div>
        <div class="sub">{{ number_format($stats['users_new_week']) }} this week</div>
    </div>
    <div class="stat-card" style="border-left: 4px solid #1abc9c;">
        <h3>New Petitions (Today)</h3>
        <div class="value">{{ number_format($stats['petitions_new_today']) }}</div>
        <div class="sub">{{ number_format($stats['petitions_pending']) }} pending review</div>
    </div>
    <div class="stat-card" style="border-left: 4px solid #9b59b6;">
        <h3>Signatures (This Week)</h3>
        <div class="value">{{ number_format($stats['signatures_week']) }}</div>
        <div class="sub">trend</div>
    </div>
</div>

<div class="dash-grid">
    <div class="stat-card" style="border-left: 4px solid #34495e;">
        <h3>Logs</h3>
        <div class="value">{{ number_format($quickStats['logs_count'] ?? 0) }}</div>
        <div class="sub">entries</div>
    </div>
    <div class="stat-card" style="border-left: 4px solid #e67e22;">
        <h3>Banned IPs</h3>
        <div class="value">{{ number_format($quickStats['banned_ips'] ?? 0) }}</div>
        <div class="sub">blocked</div>
    </div>
    <div class="stat-card" style="border-left: 4px solid #16a085;">
        <h3>Categories</h3>
        <div class="value">{{ number_format($quickStats['categories'] ?? 0) }}</div>
        <div class="sub">active</div>
    </div>
    <div class="stat-card" style="border-left: 4px solid #8e44ad;">
        <h3>Pages</h3>
        <div class="value">{{ number_format($quickStats['pages'] ?? 0) }}</div>
        <div class="sub">static</div>
    </div>
</div>

<div class="dash-section">
    <h2>Quick Actions</h2>
    <div class="quick-actions">
        <a href="{{ route('admin.petitions') }}" class="quick-btn">Manage Petitions</a>
        <a href="{{ route('admin.users') }}" class="quick-btn">Manage Users</a>
        <a href="{{ route('admin.options.global') }}" class="quick-btn">Global Settings</a>
        <a href="{{ route('admin.stats') }}" class="quick-btn">View Stats</a>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
    <div class="dash-section">
        <h2>Top Petitions</h2>
        <table class="table-list">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Signatures</th>
                    <th>Goal</th>
                </tr>
            </thead>
            <tbody>
                @forelse($topPetitions as $petition)
                <tr>
                    <td>#{{ $petition->id }}</td>
                    <td>{{ number_format($petition->signature_count) }}</td>
                    <td>{{ number_format($petition->goal) }}</td>
                </tr>
                @empty
                <tr><td colspan="3">No petitions yet</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="dash-section">
        <h2>System Health</h2>
        <div class="health-grid">
            <div class="health-item">
                <span class="label">PHP Version</span>
                <span class="val">{{ $systemHealth['php_version'] }}</span>
            </div>
            <div class="health-item">
                <span class="label">Laravel</span>
                <span class="val">{{ $systemHealth['laravel_version'] }}</span>
            </div>
            <div class="health-item">
                <span class="label">Cache</span>
                <span class="val">{{ $systemHealth['cache_driver'] }}</span>
            </div>
            <div class="health-item">
                <span class="label">Queue</span>
                <span class="val">{{ $systemHealth['queue_driver'] }}</span>
            </div>
            <div class="health-item">
                <span class="label">Database</span>
                <span class="val">{{ $systemHealth['database_type'] }}</span>
            </div>
            <div class="health-item">
                <span class="label">Database Size</span>
                <span class="val">{{ $systemHealth['database_size'] }}</span>
            </div>
            <div class="health-item">
                <span class="label">Disk Free</span>
                <span class="val">{{ $systemHealth['disk_free_space'] }}</span>
            </div>
        </div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
    <div class="dash-section">
        <h2>Recent Users</h2>
        <table class="table-list">
            <thead>
                <tr>
                    <th>Email</th>
                    <th>Status</th>
                    <th>Joined</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentActivity['users'] as $user)
                <tr>
                    <td>{{ $user->email }}</td>
                    <td>
                        @if($user->verified)
                        <span class="status-badge status-verified">Verified</span>
                        @else
                        <span class="status-badge status-draft">Pending</span>
                        @endif
                    </td>
                    <td>{{ $user->created_at->diffForHumans() }}</td>
                </tr>
                @empty
                <tr><td colspan="3">No users yet</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="dash-section">
        <h2>Recent Petitions</h2>
        <table class="table-list">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Status</th>
                    <th>Created</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentActivity['petitions'] as $petition)
                <tr>
                    <td>#{{ $petition->id }}</td>
                    <td>
                        <span class="status-badge status-{{ $petition->status }}">{{ $petition->status }}</span>
                    </td>
                    <td>{{ $petition->created_at->diffForHumans() }}</td>
                </tr>
                @empty
                <tr><td colspan="3">No petitions yet</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
