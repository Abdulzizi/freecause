@extends('admin.layouts.app')

@section('title', 'Dashboard')

@section('content')
<x-blatui.page-header title="Dashboard" class="mb-6" />

<div class="grid grid-cols-[repeat(auto-fit,minmax(240px,1fr))] gap-4 mb-6 max-sm:grid-cols-1 max-sm:gap-3">
    <x-blatui.card class="p-5 max-sm:p-4">
        <h2 class="m-0 mb-2 text-sm text-muted uppercase">Total Users</h2>
        <div class="text-2xl font-bold text-ink max-sm:text-xl">{{ number_format($stats['users_total']) }}</div>
        <div class="text-xs text-muted mt-1">{{ number_format($stats['users_verified']) }} verified ({{ round(($stats['users_verified'] / max($stats['users_total'], 1)) * 100) }}%)</div>
    </x-blatui.card>
    <x-blatui.card class="p-5 max-sm:p-4">
        <h2 class="m-0 mb-2 text-sm text-muted uppercase">Total Petitions</h2>
        <div class="text-2xl font-bold text-ink max-sm:text-xl">{{ number_format($stats['petitions_total']) }}</div>
        <div class="text-xs text-muted mt-1">{{ number_format($stats['petitions_published']) }} published</div>
    </x-blatui.card>
    <x-blatui.card class="p-5 max-sm:p-4">
        <h2 class="m-0 mb-2 text-sm text-muted uppercase">Total Signatures</h2>
        <div class="text-2xl font-bold text-ink max-sm:text-xl">{{ number_format($stats['signatures_total']) }}</div>
        <div class="text-xs text-muted mt-1">{{ number_format($stats['signatures_today']) }} today</div>
    </x-blatui.card>
    <x-blatui.card class="p-5 max-sm:p-4">
        <h2 class="m-0 mb-2 text-sm text-muted uppercase">Active Languages</h2>
        <div class="text-2xl font-bold text-ink max-sm:text-xl">{{ $stats['languages_active'] }}</div>
        <div class="text-xs text-muted mt-1">enabled</div>
    </x-blatui.card>
</div>

<div class="grid grid-cols-[repeat(auto-fit,minmax(240px,1fr))] gap-4 mb-6 max-sm:grid-cols-1 max-sm:gap-3">
    <x-blatui.card class="p-5 max-sm:p-4">
        <h2 class="m-0 mb-2 text-sm text-muted uppercase">New Users (Today)</h2>
        <div class="text-2xl font-bold text-ink max-sm:text-xl">{{ number_format($stats['users_new_today']) }}</div>
        <div class="text-xs text-muted mt-1">{{ number_format($stats['users_new_week']) }} this week</div>
    </x-blatui.card>
    <x-blatui.card class="p-5 max-sm:p-4">
        <h2 class="m-0 mb-2 text-sm text-muted uppercase">New Petitions (Today)</h2>
        <div class="text-2xl font-bold text-ink max-sm:text-xl">{{ number_format($stats['petitions_new_today']) }}</div>
        <div class="text-xs text-muted mt-1">{{ number_format($stats['petitions_pending']) }} pending review</div>
    </x-blatui.card>
    <x-blatui.card class="p-5 max-sm:p-4">
        <h2 class="m-0 mb-2 text-sm text-muted uppercase">Signatures (This Week)</h2>
        <div class="text-2xl font-bold text-ink max-sm:text-xl">{{ number_format($stats['signatures_week']) }}</div>
        <div class="text-xs text-muted mt-1">trend</div>
    </x-blatui.card>
</div>

<div class="grid grid-cols-[repeat(auto-fit,minmax(240px,1fr))] gap-4 mb-6 max-sm:grid-cols-1 max-sm:gap-3">
    <x-blatui.card class="p-5 max-sm:p-4">
        <h2 class="m-0 mb-2 text-sm text-muted uppercase">Logs</h2>
        <div class="text-2xl font-bold text-ink max-sm:text-xl">{{ number_format($quickStats['logs_count'] ?? 0) }}</div>
        <div class="text-xs text-muted mt-1">entries</div>
    </x-blatui.card>
    <x-blatui.card class="p-5 max-sm:p-4">
        <h2 class="m-0 mb-2 text-sm text-muted uppercase">Banned IPs</h2>
        <div class="text-2xl font-bold text-ink max-sm:text-xl">{{ number_format($quickStats['banned_ips'] ?? 0) }}</div>
        <div class="text-xs text-muted mt-1">blocked</div>
    </x-blatui.card>
    <x-blatui.card class="p-5 max-sm:p-4">
        <h2 class="m-0 mb-2 text-sm text-muted uppercase">Categories</h2>
        <div class="text-2xl font-bold text-ink max-sm:text-xl">{{ number_format($quickStats['categories'] ?? 0) }}</div>
        <div class="text-xs text-muted mt-1">active</div>
    </x-blatui.card>
    <x-blatui.card class="p-5 max-sm:p-4">
        <h2 class="m-0 mb-2 text-sm text-muted uppercase">Pages</h2>
        <div class="text-2xl font-bold text-ink max-sm:text-xl">{{ number_format($quickStats['pages'] ?? 0) }}</div>
        <div class="text-xs text-muted mt-1">static</div>
    </x-blatui.card>
</div>

<x-blatui.card class="p-5 mb-5">
    <h2 class="m-0 mb-4 text-lg text-ink border-b border-border pb-2.5">Quick Actions</h2>
    <div class="flex gap-2.5 flex-wrap max-sm:flex-col">
        @if(admin_can('petitions', 'view'))
        <x-blatui.button variant="outline" size="sm" :href="route('admin.petitions')">Manage Petitions</x-blatui.button>
        @endif
        @if(admin_can('users', 'view'))
        <x-blatui.button variant="outline" size="sm" :href="route('admin.users')">Manage Users</x-blatui.button>
        @endif
        @if(admin_can('options', 'view'))
        <x-blatui.button variant="outline" size="sm" :href="route('admin.options.global')">Global Settings</x-blatui.button>
        @endif
        @if(admin_can('stats', 'view'))
        <x-blatui.button variant="outline" size="sm" :href="route('admin.stats')">View Stats</x-blatui.button>
        @endif
    </div>
</x-blatui.card>

<div class="grid grid-cols-2 gap-5 mb-5 max-sm:grid-cols-1 max-sm:gap-4">
    <x-blatui.card class="p-5 mb-5">
        <h2 class="m-0 mb-4 text-lg text-ink border-b border-border pb-2.5">Top Petitions</h2>
        <table class="w-full border-collapse overflow-x-auto block text-sm">
            <thead>
                <tr>
                    <th class="p-2.5 px-3 text-left border-b border-border whitespace-nowrap bg-surface-2 font-semibold text-xs uppercase text-muted">ID</th>
                    <th class="p-2.5 px-3 text-left border-b border-border whitespace-nowrap bg-surface-2 font-semibold text-xs uppercase text-muted">Signatures</th>
                    <th class="p-2.5 px-3 text-left border-b border-border whitespace-nowrap bg-surface-2 font-semibold text-xs uppercase text-muted">Goal</th>
                </tr>
            </thead>
            <tbody>
                @forelse($topPetitions as $petition)
                <tr data-admin-row-hover class="hover:bg-surface-2">
                    <td class="p-2.5 px-3 text-left border-b border-border whitespace-nowrap">#{{ $petition->id }}</td>
                    <td class="p-2.5 px-3 text-left border-b border-border whitespace-nowrap">{{ number_format($petition->signature_count) }}</td>
                    <td class="p-2.5 px-3 text-left border-b border-border whitespace-nowrap">{{ number_format($petition->goal_signatures) }}</td>
                </tr>
                @empty
                <tr data-admin-row-hover class="hover:bg-surface-2"><td class="p-2.5 px-3 text-left border-b border-border whitespace-nowrap" colspan="3">No petitions yet</td></tr>
                @endforelse
            </tbody>
        </table>
    </x-blatui.card>

    <x-blatui.card class="p-5 mb-5">
        <h2 class="m-0 mb-4 text-lg text-ink border-b border-border pb-2.5">System Health</h2>
        <div class="grid grid-cols-[repeat(auto-fit,minmax(200px,1fr))] gap-3 text-sm">
            <div class="flex justify-between p-2 px-3 bg-surface-2 rounded-sm">
                <span class="text-muted">PHP Version</span>
                <span class="font-semibold text-ink">{{ $systemHealth['php_version'] }}</span>
            </div>
            <div class="flex justify-between p-2 px-3 bg-surface-2 rounded-sm">
                <span class="text-muted">Laravel</span>
                <span class="font-semibold text-ink">{{ $systemHealth['laravel_version'] }}</span>
            </div>
            <div class="flex justify-between p-2 px-3 bg-surface-2 rounded-sm">
                <span class="text-muted">Cache</span>
                <span class="font-semibold text-ink">{{ $systemHealth['cache_driver'] }}</span>
            </div>
            <div class="flex justify-between p-2 px-3 bg-surface-2 rounded-sm">
                <span class="text-muted">Queue</span>
                <span class="font-semibold text-ink">{{ $systemHealth['queue_driver'] }}</span>
            </div>
            <div class="flex justify-between p-2 px-3 bg-surface-2 rounded-sm">
                <span class="text-muted">Database</span>
                <span class="font-semibold text-ink">{{ $systemHealth['database_type'] }}</span>
            </div>
            <div class="flex justify-between p-2 px-3 bg-surface-2 rounded-sm">
                <span class="text-muted">Database Size</span>
                <span class="font-semibold text-ink">{{ $systemHealth['database_size'] }}</span>
            </div>
            <div class="flex justify-between p-2 px-3 bg-surface-2 rounded-sm">
                <span class="text-muted">Disk Free</span>
                <span class="font-semibold text-ink">{{ $systemHealth['disk_free_space'] }}</span>
            </div>
        </div>
    </x-blatui.card>
</div>

<div class="grid grid-cols-2 gap-5 mb-5 max-sm:grid-cols-1 max-sm:gap-4">
    <x-blatui.card class="p-5 mb-5">
        <h2 class="m-0 mb-4 text-lg text-ink border-b border-border pb-2.5">Recent Users</h2>
        <table class="w-full border-collapse overflow-x-auto block text-sm">
            <thead>
                <tr>
                    <th class="p-2.5 px-3 text-left border-b border-border whitespace-nowrap bg-surface-2 font-semibold text-xs uppercase text-muted">Email</th>
                    <th class="p-2.5 px-3 text-left border-b border-border whitespace-nowrap bg-surface-2 font-semibold text-xs uppercase text-muted">Status</th>
                    <th class="p-2.5 px-3 text-left border-b border-border whitespace-nowrap bg-surface-2 font-semibold text-xs uppercase text-muted">Joined</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentActivity['users'] as $user)
                <tr data-admin-row-hover class="hover:bg-surface-2">
                    <td class="p-2.5 px-3 text-left border-b border-border whitespace-nowrap">{{ $user->email }}</td>
                    <td class="p-2.5 px-3 text-left border-b border-border whitespace-nowrap">
                        @if($user->verified)
                        <x-blatui.badge tone="success">Verified</x-blatui.badge>
                        @else
                        <x-blatui.badge tone="danger">Pending</x-blatui.badge>
                        @endif
                    </td>
                    <td class="p-2.5 px-3 text-left border-b border-border whitespace-nowrap">{{ $user->created_at->diffForHumans() }}</td>
                </tr>
                @empty
                <tr data-admin-row-hover class="hover:bg-surface-2"><td class="p-2.5 px-3 text-left border-b border-border whitespace-nowrap" colspan="3">No users yet</td></tr>
                @endforelse
            </tbody>
        </table>
    </x-blatui.card>

    <x-blatui.card class="p-5 mb-5">
        <h2 class="m-0 mb-4 text-lg text-ink border-b border-border pb-2.5">Recent Petitions</h2>
        <table class="w-full border-collapse overflow-x-auto block text-sm">
            <thead>
                <tr>
                    <th class="p-2.5 px-3 text-left border-b border-border whitespace-nowrap bg-surface-2 font-semibold text-xs uppercase text-muted">ID</th>
                    <th class="p-2.5 px-3 text-left border-b border-border whitespace-nowrap bg-surface-2 font-semibold text-xs uppercase text-muted">Status</th>
                    <th class="p-2.5 px-3 text-left border-b border-border whitespace-nowrap bg-surface-2 font-semibold text-xs uppercase text-muted">Created</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentActivity['petitions'] as $petition)
                <tr data-admin-row-hover class="hover:bg-surface-2">
                    <td class="p-2.5 px-3 text-left border-b border-border whitespace-nowrap">#{{ $petition->id }}</td>
                    <td class="p-2.5 px-3 text-left border-b border-border whitespace-nowrap">
                        <x-blatui.badge :tone="$petition->status === 'published' ? 'success' : ($petition->status === 'draft' ? 'warning' : 'danger')">{{ $petition->status }}</x-blatui.badge>
                    </td>
                    <td class="p-2.5 px-3 text-left border-b border-border whitespace-nowrap">{{ $petition->created_at->diffForHumans() }}</td>
                </tr>
                @empty
                <tr data-admin-row-hover class="hover:bg-surface-2"><td class="p-2.5 px-3 text-left border-b border-border whitespace-nowrap" colspan="3">No petitions yet</td></tr>
                @endforelse
            </tbody>
        </table>
    </x-blatui.card>
</div>
@endsection
