@extends('admin.layouts.app')

@section('title', 'Dashboard')


@section('content')
<x-admin.page-header title="Dashboard" />

<div class="grid! grid-cols-[repeat(auto-fit,minmax(240px,1fr))]! gap-4! mb-6! max-sm:grid-cols-1! max-sm:gap-3!">
    <div class="bg-surface! border! border-border! rounded! p-5! max-sm:p-[15px]!">
        <h2 class="m-0! mb-2! text-[13px]! text-muted! uppercase!">Total Users</h2>
        <div class="text-[28px]! font-bold! text-ink! max-sm:text-[22px]!">{{ number_format($stats['users_total']) }}</div>
        <div class="text-xs! text-muted! mt-1!">{{ number_format($stats['users_verified']) }} verified ({{ round(($stats['users_verified'] / max($stats['users_total'], 1)) * 100) }}%)</div>
    </div>
    <div class="bg-surface! border! border-border! rounded! p-5! max-sm:p-[15px]!">
        <h2 class="m-0! mb-2! text-[13px]! text-muted! uppercase!">Total Petitions</h2>
        <div class="text-[28px]! font-bold! text-ink! max-sm:text-[22px]!">{{ number_format($stats['petitions_total']) }}</div>
        <div class="text-xs! text-muted! mt-1!">{{ number_format($stats['petitions_published']) }} published</div>
    </div>
    <div class="bg-surface! border! border-border! rounded! p-5! max-sm:p-[15px]!">
        <h2 class="m-0! mb-2! text-[13px]! text-muted! uppercase!">Total Signatures</h2>
        <div class="text-[28px]! font-bold! text-ink! max-sm:text-[22px]!">{{ number_format($stats['signatures_total']) }}</div>
        <div class="text-xs! text-muted! mt-1!">{{ number_format($stats['signatures_today']) }} today</div>
    </div>
    <div class="bg-surface! border! border-border! rounded! p-5! max-sm:p-[15px]!">
        <h2 class="m-0! mb-2! text-[13px]! text-muted! uppercase!">Active Languages</h2>
        <div class="text-[28px]! font-bold! text-ink! max-sm:text-[22px]!">{{ $stats['languages_active'] }}</div>
        <div class="text-xs! text-muted! mt-1!">enabled</div>
    </div>
</div>

<div class="grid! grid-cols-[repeat(auto-fit,minmax(240px,1fr))]! gap-4! mb-6! max-sm:grid-cols-1! max-sm:gap-3!">
    <div class="bg-surface! border! border-border! rounded! p-5! max-sm:p-[15px]!">
        <h2 class="m-0! mb-2! text-[13px]! text-muted! uppercase!">New Users (Today)</h2>
        <div class="text-[28px]! font-bold! text-ink! max-sm:text-[22px]!">{{ number_format($stats['users_new_today']) }}</div>
        <div class="text-xs! text-muted! mt-1!">{{ number_format($stats['users_new_week']) }} this week</div>
    </div>
    <div class="bg-surface! border! border-border! rounded! p-5! max-sm:p-[15px]!">
        <h2 class="m-0! mb-2! text-[13px]! text-muted! uppercase!">New Petitions (Today)</h2>
        <div class="text-[28px]! font-bold! text-ink! max-sm:text-[22px]!">{{ number_format($stats['petitions_new_today']) }}</div>
        <div class="text-xs! text-muted! mt-1!">{{ number_format($stats['petitions_pending']) }} pending review</div>
    </div>
    <div class="bg-surface! border! border-border! rounded! p-5! max-sm:p-[15px]!">
        <h2 class="m-0! mb-2! text-[13px]! text-muted! uppercase!">Signatures (This Week)</h2>
        <div class="text-[28px]! font-bold! text-ink! max-sm:text-[22px]!">{{ number_format($stats['signatures_week']) }}</div>
        <div class="text-xs! text-muted! mt-1!">trend</div>
    </div>
</div>

<div class="grid! grid-cols-[repeat(auto-fit,minmax(240px,1fr))]! gap-4! mb-6! max-sm:grid-cols-1! max-sm:gap-3!">
    <div class="bg-surface! border! border-border! rounded! p-5! max-sm:p-[15px]!">
        <h2 class="m-0! mb-2! text-[13px]! text-muted! uppercase!">Logs</h2>
        <div class="text-[28px]! font-bold! text-ink! max-sm:text-[22px]!">{{ number_format($quickStats['logs_count'] ?? 0) }}</div>
        <div class="text-xs! text-muted! mt-1!">entries</div>
    </div>
    <div class="bg-surface! border! border-border! rounded! p-5! max-sm:p-[15px]!">
        <h2 class="m-0! mb-2! text-[13px]! text-muted! uppercase!">Banned IPs</h2>
        <div class="text-[28px]! font-bold! text-ink! max-sm:text-[22px]!">{{ number_format($quickStats['banned_ips'] ?? 0) }}</div>
        <div class="text-xs! text-muted! mt-1!">blocked</div>
    </div>
    <div class="bg-surface! border! border-border! rounded! p-5! max-sm:p-[15px]!">
        <h2 class="m-0! mb-2! text-[13px]! text-muted! uppercase!">Categories</h2>
        <div class="text-[28px]! font-bold! text-ink! max-sm:text-[22px]!">{{ number_format($quickStats['categories'] ?? 0) }}</div>
        <div class="text-xs! text-muted! mt-1!">active</div>
    </div>
    <div class="bg-surface! border! border-border! rounded! p-5! max-sm:p-[15px]!">
        <h2 class="m-0! mb-2! text-[13px]! text-muted! uppercase!">Pages</h2>
        <div class="text-[28px]! font-bold! text-ink! max-sm:text-[22px]!">{{ number_format($quickStats['pages'] ?? 0) }}</div>
        <div class="text-xs! text-muted! mt-1!">static</div>
    </div>
</div>

<div class="bg-surface! border! border-border! rounded! p-5! mb-5!">
    <h2 class="m-0! mb-4! text-lg! text-ink! border-b! border-border! pb-2.5!">Quick Actions</h2>
    <div class="flex! gap-2.5! flex-wrap! max-sm:flex-col!">
        @if(admin_can('petitions', 'view'))
        <a href="{{ route('admin.petitions') }}" class="fc-abtn">Manage Petitions</a>
        @endif
        @if(admin_can('users', 'view'))
        <a href="{{ route('admin.users') }}" class="fc-abtn">Manage Users</a>
        @endif
        @if(admin_can('options', 'view'))
        <a href="{{ route('admin.options.global') }}" class="fc-abtn">Global Settings</a>
        @endif
        @if(admin_can('stats', 'view'))
        <a href="{{ route('admin.stats') }}" class="fc-abtn">View Stats</a>
        @endif
    </div>
</div>

<div class="grid! grid-cols-2! gap-5! mb-5! max-sm:grid-cols-1! max-sm:gap-4!">
    <div class="bg-surface! border! border-border! rounded! p-5! mb-5!">
        <h2 class="m-0! mb-4! text-lg! text-ink! border-b! border-border! pb-2.5!">Top Petitions</h2>
        <table class="w-full! border-collapse! overflow-x-auto! block!">
            <thead>
                <tr>
                    <th class="p-2.5! px-3! text-left! border-b! border-border! whitespace-nowrap! bg-[#f9f9f9]! font-semibold! text-xs! uppercase! text-muted!">ID</th>
                    <th class="p-2.5! px-3! text-left! border-b! border-border! whitespace-nowrap! bg-[#f9f9f9]! font-semibold! text-xs! uppercase! text-muted!">Signatures</th>
                    <th class="p-2.5! px-3! text-left! border-b! border-border! whitespace-nowrap! bg-[#f9f9f9]! font-semibold! text-xs! uppercase! text-muted!">Goal</th>
                </tr>
            </thead>
            <tbody>
                @forelse($topPetitions as $petition)
                <tr data-admin-row-hover class="hover:bg-[#fafafa]!">
                    <td class="p-2.5! px-3! text-left! border-b! border-border! whitespace-nowrap!">#{{ $petition->id }}</td>
                    <td class="p-2.5! px-3! text-left! border-b! border-border! whitespace-nowrap!">{{ number_format($petition->signature_count) }}</td>
                    <td class="p-2.5! px-3! text-left! border-b! border-border! whitespace-nowrap!">{{ number_format($petition->goal_signatures) }}</td>
                </tr>
                @empty
                <tr data-admin-row-hover class="hover:bg-[#fafafa]!"><td class="p-2.5! px-3! text-left! border-b! border-border! whitespace-nowrap!" colspan="3">No petitions yet</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="bg-surface! border! border-border! rounded! p-5! mb-5!">
        <h2 class="m-0! mb-4! text-lg! text-ink! border-b! border-border! pb-2.5!">System Health</h2>
        <div class="grid! grid-cols-[repeat(auto-fit,minmax(200px,1fr))]! gap-3!">
            <div class="flex! justify-between! p-2! px-3! bg-[#f9f9f9]! rounded!">
                <span class="text-muted!">PHP Version</span>
                <span class="font-semibold! text-ink!">{{ $systemHealth['php_version'] }}</span>
            </div>
            <div class="flex! justify-between! p-2! px-3! bg-[#f9f9f9]! rounded!">
                <span class="text-muted!">Laravel</span>
                <span class="font-semibold! text-ink!">{{ $systemHealth['laravel_version'] }}</span>
            </div>
            <div class="flex! justify-between! p-2! px-3! bg-[#f9f9f9]! rounded!">
                <span class="text-muted!">Cache</span>
                <span class="font-semibold! text-ink!">{{ $systemHealth['cache_driver'] }}</span>
            </div>
            <div class="flex! justify-between! p-2! px-3! bg-[#f9f9f9]! rounded!">
                <span class="text-muted!">Queue</span>
                <span class="font-semibold! text-ink!">{{ $systemHealth['queue_driver'] }}</span>
            </div>
            <div class="flex! justify-between! p-2! px-3! bg-[#f9f9f9]! rounded!">
                <span class="text-muted!">Database</span>
                <span class="font-semibold! text-ink!">{{ $systemHealth['database_type'] }}</span>
            </div>
            <div class="flex! justify-between! p-2! px-3! bg-[#f9f9f9]! rounded!">
                <span class="text-muted!">Database Size</span>
                <span class="font-semibold! text-ink!">{{ $systemHealth['database_size'] }}</span>
            </div>
            <div class="flex! justify-between! p-2! px-3! bg-[#f9f9f9]! rounded!">
                <span class="text-muted!">Disk Free</span>
                <span class="font-semibold! text-ink!">{{ $systemHealth['disk_free_space'] }}</span>
            </div>
        </div>
    </div>
</div>

<div class="grid! grid-cols-2! gap-5! mb-5! max-sm:grid-cols-1! max-sm:gap-4!">
    <div class="bg-surface! border! border-border! rounded! p-5! mb-5!">
        <h2 class="m-0! mb-4! text-lg! text-ink! border-b! border-border! pb-2.5!">Recent Users</h2>
        <table class="w-full! border-collapse! overflow-x-auto! block!">
            <thead>
                <tr>
                    <th class="p-2.5! px-3! text-left! border-b! border-border! whitespace-nowrap! bg-[#f9f9f9]! font-semibold! text-xs! uppercase! text-muted!">Email</th>
                    <th class="p-2.5! px-3! text-left! border-b! border-border! whitespace-nowrap! bg-[#f9f9f9]! font-semibold! text-xs! uppercase! text-muted!">Status</th>
                    <th class="p-2.5! px-3! text-left! border-b! border-border! whitespace-nowrap! bg-[#f9f9f9]! font-semibold! text-xs! uppercase! text-muted!">Joined</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentActivity['users'] as $user)
                <tr data-admin-row-hover class="hover:bg-[#fafafa]!">
                    <td class="p-2.5! px-3! text-left! border-b! border-border! whitespace-nowrap!">{{ $user->email }}</td>
                    <td class="p-2.5! px-3! text-left! border-b! border-border! whitespace-nowrap!">
                        @if($user->verified)
                        <span class="inline-block! py-0.5! px-2! rounded-[3px]! text-[11px]! font-semibold! uppercase! bg-[#d4edda]! text-[#155724]!">Verified</span>
                        @else
                        <span class="inline-block! py-0.5! px-2! rounded-[3px]! text-[11px]! font-semibold! uppercase! bg-[#f8d7da]! text-[#721c24]!">Pending</span>
                        @endif
                    </td>
                    <td class="p-2.5! px-3! text-left! border-b! border-border! whitespace-nowrap!">{{ $user->created_at->diffForHumans() }}</td>
                </tr>
                @empty
                <tr data-admin-row-hover class="hover:bg-[#fafafa]!"><td class="p-2.5! px-3! text-left! border-b! border-border! whitespace-nowrap!" colspan="3">No users yet</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="bg-surface! border! border-border! rounded! p-5! mb-5!">
        <h2 class="m-0! mb-4! text-lg! text-ink! border-b! border-border! pb-2.5!">Recent Petitions</h2>
        <table class="w-full! border-collapse! overflow-x-auto! block!">
            <thead>
                <tr>
                    <th class="p-2.5! px-3! text-left! border-b! border-border! whitespace-nowrap! bg-[#f9f9f9]! font-semibold! text-xs! uppercase! text-muted!">ID</th>
                    <th class="p-2.5! px-3! text-left! border-b! border-border! whitespace-nowrap! bg-[#f9f9f9]! font-semibold! text-xs! uppercase! text-muted!">Status</th>
                    <th class="p-2.5! px-3! text-left! border-b! border-border! whitespace-nowrap! bg-[#f9f9f9]! font-semibold! text-xs! uppercase! text-muted!">Created</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentActivity['petitions'] as $petition)
                <tr data-admin-row-hover class="hover:bg-[#fafafa]!">
                    <td class="p-2.5! px-3! text-left! border-b! border-border! whitespace-nowrap!">#{{ $petition->id }}</td>
                    <td class="p-2.5! px-3! text-left! border-b! border-border! whitespace-nowrap!">
                        <span class="inline-block! py-0.5! px-2! rounded-[3px]! text-[11px]! font-semibold! uppercase! {{ $petition->status === 'published' ? 'bg-[#d4edda]! text-[#155724]!' : ($petition->status === 'draft' ? 'bg-[#fff3cd]! text-[#856404]!' : 'bg-[#f8d7da]! text-[#721c24]!') }}">{{ $petition->status }}</span>
                    </td>
                    <td class="p-2.5! px-3! text-left! border-b! border-border! whitespace-nowrap!">{{ $petition->created_at->diffForHumans() }}</td>
                </tr>
                @empty
                <tr data-admin-row-hover class="hover:bg-[#fafafa]!"><td class="p-2.5! px-3! text-left! border-b! border-border! whitespace-nowrap!" colspan="3">No petitions yet</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
