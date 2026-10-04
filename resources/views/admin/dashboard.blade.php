@extends('admin.layouts.app')

@section('title', 'Dashboard')


@section('content')
<h1 class="mb-0! max-md:text-[15px]!">Dashboard</h1>

<div class="grid! grid-cols-[repeat(auto-fit,minmax(240px,1fr))]! gap-4! mb-6! max-sm:grid-cols-1! max-sm:gap-3!">
    <div class="bg-white! border! border-[#ddd]! rounded! p-5! border-l-4! border-l-[#3498db]! max-sm:p-[15px]!">
        <h2 class="m-0! mb-2! text-[13px]! text-[#666]! uppercase!">Total Users</h2>
        <div class="text-[28px]! font-bold! text-[#333]! max-sm:text-[22px]!">{{ number_format($stats['users_total']) }}</div>
        <div class="text-xs! text-[#888]! mt-1!">{{ number_format($stats['users_verified']) }} verified ({{ round(($stats['users_verified'] / max($stats['users_total'], 1)) * 100) }}%)</div>
    </div>
    <div class="bg-white! border! border-[#ddd]! rounded! p-5! border-l-4! border-l-[#2ecc71]! max-sm:p-[15px]!">
        <h2 class="m-0! mb-2! text-[13px]! text-[#666]! uppercase!">Total Petitions</h2>
        <div class="text-[28px]! font-bold! text-[#333]! max-sm:text-[22px]!">{{ number_format($stats['petitions_total']) }}</div>
        <div class="text-xs! text-[#888]! mt-1!">{{ number_format($stats['petitions_published']) }} published</div>
    </div>
    <div class="bg-white! border! border-[#ddd]! rounded! p-5! border-l-4! border-l-[#9b59b6]! max-sm:p-[15px]!">
        <h2 class="m-0! mb-2! text-[13px]! text-[#666]! uppercase!">Total Signatures</h2>
        <div class="text-[28px]! font-bold! text-[#333]! max-sm:text-[22px]!">{{ number_format($stats['signatures_total']) }}</div>
        <div class="text-xs! text-[#888]! mt-1!">{{ number_format($stats['signatures_today']) }} today</div>
    </div>
    <div class="bg-white! border! border-[#ddd]! rounded! p-5! border-l-4! border-l-[#e67e22]! max-sm:p-[15px]!">
        <h2 class="m-0! mb-2! text-[13px]! text-[#666]! uppercase!">Active Languages</h2>
        <div class="text-[28px]! font-bold! text-[#333]! max-sm:text-[22px]!">{{ $stats['languages_active'] }}</div>
        <div class="text-xs! text-[#888]! mt-1!">enabled</div>
    </div>
</div>

<div class="grid! grid-cols-[repeat(auto-fit,minmax(240px,1fr))]! gap-4! mb-6! max-sm:grid-cols-1! max-sm:gap-3!">
    <div class="bg-white! border! border-[#ddd]! rounded! p-5! border-l-4! border-l-[#e74c3c]! max-sm:p-[15px]!">
        <h2 class="m-0! mb-2! text-[13px]! text-[#666]! uppercase!">New Users (Today)</h2>
        <div class="text-[28px]! font-bold! text-[#333]! max-sm:text-[22px]!">{{ number_format($stats['users_new_today']) }}</div>
        <div class="text-xs! text-[#888]! mt-1!">{{ number_format($stats['users_new_week']) }} this week</div>
    </div>
    <div class="bg-white! border! border-[#ddd]! rounded! p-5! border-l-4! border-l-[#1abc9c]! max-sm:p-[15px]!">
        <h2 class="m-0! mb-2! text-[13px]! text-[#666]! uppercase!">New Petitions (Today)</h2>
        <div class="text-[28px]! font-bold! text-[#333]! max-sm:text-[22px]!">{{ number_format($stats['petitions_new_today']) }}</div>
        <div class="text-xs! text-[#888]! mt-1!">{{ number_format($stats['petitions_pending']) }} pending review</div>
    </div>
    <div class="bg-white! border! border-[#ddd]! rounded! p-5! border-l-4! border-l-[#9b59b6]! max-sm:p-[15px]!">
        <h2 class="m-0! mb-2! text-[13px]! text-[#666]! uppercase!">Signatures (This Week)</h2>
        <div class="text-[28px]! font-bold! text-[#333]! max-sm:text-[22px]!">{{ number_format($stats['signatures_week']) }}</div>
        <div class="text-xs! text-[#888]! mt-1!">trend</div>
    </div>
</div>

<div class="grid! grid-cols-[repeat(auto-fit,minmax(240px,1fr))]! gap-4! mb-6! max-sm:grid-cols-1! max-sm:gap-3!">
    <div class="bg-white! border! border-[#ddd]! rounded! p-5! border-l-4! border-l-[#34495e]! max-sm:p-[15px]!">
        <h2 class="m-0! mb-2! text-[13px]! text-[#666]! uppercase!">Logs</h2>
        <div class="text-[28px]! font-bold! text-[#333]! max-sm:text-[22px]!">{{ number_format($quickStats['logs_count'] ?? 0) }}</div>
        <div class="text-xs! text-[#888]! mt-1!">entries</div>
    </div>
    <div class="bg-white! border! border-[#ddd]! rounded! p-5! border-l-4! border-l-[#e67e22]! max-sm:p-[15px]!">
        <h2 class="m-0! mb-2! text-[13px]! text-[#666]! uppercase!">Banned IPs</h2>
        <div class="text-[28px]! font-bold! text-[#333]! max-sm:text-[22px]!">{{ number_format($quickStats['banned_ips'] ?? 0) }}</div>
        <div class="text-xs! text-[#888]! mt-1!">blocked</div>
    </div>
    <div class="bg-white! border! border-[#ddd]! rounded! p-5! border-l-4! border-l-[#16a085]! max-sm:p-[15px]!">
        <h2 class="m-0! mb-2! text-[13px]! text-[#666]! uppercase!">Categories</h2>
        <div class="text-[28px]! font-bold! text-[#333]! max-sm:text-[22px]!">{{ number_format($quickStats['categories'] ?? 0) }}</div>
        <div class="text-xs! text-[#888]! mt-1!">active</div>
    </div>
    <div class="bg-white! border! border-[#ddd]! rounded! p-5! border-l-4! border-l-[#8e44ad]! max-sm:p-[15px]!">
        <h2 class="m-0! mb-2! text-[13px]! text-[#666]! uppercase!">Pages</h2>
        <div class="text-[28px]! font-bold! text-[#333]! max-sm:text-[22px]!">{{ number_format($quickStats['pages'] ?? 0) }}</div>
        <div class="text-xs! text-[#888]! mt-1!">static</div>
    </div>
</div>

<div class="bg-white! border! border-[#ddd]! rounded! p-5! mb-5!">
    <h2 class="m-0! mb-4! text-lg! text-[#333]! border-b! border-[#eee]! pb-2.5!">Quick Actions</h2>
    <div class="flex! gap-2.5! flex-wrap! max-sm:flex-col!">
        @if(admin_can('petitions', 'view'))
        <a href="{{ route('admin.petitions') }}" class="py-2.5! px-5! bg-[#3498db]! text-white! no-underline! rounded! text-[13px]! hover:bg-[#2980b9]! max-sm:text-center!">Manage Petitions</a>
        @endif
        @if(admin_can('users', 'view'))
        <a href="{{ route('admin.users') }}" class="py-2.5! px-5! bg-[#3498db]! text-white! no-underline! rounded! text-[13px]! hover:bg-[#2980b9]! max-sm:text-center!">Manage Users</a>
        @endif
        @if(admin_can('options', 'view'))
        <a href="{{ route('admin.options.global') }}" class="py-2.5! px-5! bg-[#3498db]! text-white! no-underline! rounded! text-[13px]! hover:bg-[#2980b9]! max-sm:text-center!">Global Settings</a>
        @endif
        @if(admin_can('stats', 'view'))
        <a href="{{ route('admin.stats') }}" class="py-2.5! px-5! bg-[#3498db]! text-white! no-underline! rounded! text-[13px]! hover:bg-[#2980b9]! max-sm:text-center!">View Stats</a>
        @endif
    </div>
</div>

<div class="grid! grid-cols-2! gap-5! mb-5! max-sm:grid-cols-1! max-sm:gap-4!">
    <div class="bg-white! border! border-[#ddd]! rounded! p-5! mb-5!">
        <h2 class="m-0! mb-4! text-lg! text-[#333]! border-b! border-[#eee]! pb-2.5!">Top Petitions</h2>
        <table class="w-full! border-collapse! overflow-x-auto! block!">
            <thead>
                <tr>
                    <th class="p-2.5! px-3! text-left! border-b! border-[#eee]! whitespace-nowrap! bg-[#f9f9f9]! font-semibold! text-xs! uppercase! text-[#666]!">ID</th>
                    <th class="p-2.5! px-3! text-left! border-b! border-[#eee]! whitespace-nowrap! bg-[#f9f9f9]! font-semibold! text-xs! uppercase! text-[#666]!">Signatures</th>
                    <th class="p-2.5! px-3! text-left! border-b! border-[#eee]! whitespace-nowrap! bg-[#f9f9f9]! font-semibold! text-xs! uppercase! text-[#666]!">Goal</th>
                </tr>
            </thead>
            <tbody>
                @forelse($topPetitions as $petition)
                <tr class="hover:bg-[#fafafa]!">
                    <td class="p-2.5! px-3! text-left! border-b! border-[#eee]! whitespace-nowrap!">#{{ $petition->id }}</td>
                    <td class="p-2.5! px-3! text-left! border-b! border-[#eee]! whitespace-nowrap!">{{ number_format($petition->signature_count) }}</td>
                    <td class="p-2.5! px-3! text-left! border-b! border-[#eee]! whitespace-nowrap!">{{ number_format($petition->goal_signatures) }}</td>
                </tr>
                @empty
                <tr class="hover:bg-[#fafafa]!"><td class="p-2.5! px-3! text-left! border-b! border-[#eee]! whitespace-nowrap!" colspan="3">No petitions yet</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="bg-white! border! border-[#ddd]! rounded! p-5! mb-5!">
        <h2 class="m-0! mb-4! text-lg! text-[#333]! border-b! border-[#eee]! pb-2.5!">System Health</h2>
        <div class="grid! grid-cols-[repeat(auto-fit,minmax(200px,1fr))]! gap-3!">
            <div class="flex! justify-between! p-2! px-3! bg-[#f9f9f9]! rounded!">
                <span class="text-[#666]!">PHP Version</span>
                <span class="font-semibold! text-[#333]!">{{ $systemHealth['php_version'] }}</span>
            </div>
            <div class="flex! justify-between! p-2! px-3! bg-[#f9f9f9]! rounded!">
                <span class="text-[#666]!">Laravel</span>
                <span class="font-semibold! text-[#333]!">{{ $systemHealth['laravel_version'] }}</span>
            </div>
            <div class="flex! justify-between! p-2! px-3! bg-[#f9f9f9]! rounded!">
                <span class="text-[#666]!">Cache</span>
                <span class="font-semibold! text-[#333]!">{{ $systemHealth['cache_driver'] }}</span>
            </div>
            <div class="flex! justify-between! p-2! px-3! bg-[#f9f9f9]! rounded!">
                <span class="text-[#666]!">Queue</span>
                <span class="font-semibold! text-[#333]!">{{ $systemHealth['queue_driver'] }}</span>
            </div>
            <div class="flex! justify-between! p-2! px-3! bg-[#f9f9f9]! rounded!">
                <span class="text-[#666]!">Database</span>
                <span class="font-semibold! text-[#333]!">{{ $systemHealth['database_type'] }}</span>
            </div>
            <div class="flex! justify-between! p-2! px-3! bg-[#f9f9f9]! rounded!">
                <span class="text-[#666]!">Database Size</span>
                <span class="font-semibold! text-[#333]!">{{ $systemHealth['database_size'] }}</span>
            </div>
            <div class="flex! justify-between! p-2! px-3! bg-[#f9f9f9]! rounded!">
                <span class="text-[#666]!">Disk Free</span>
                <span class="font-semibold! text-[#333]!">{{ $systemHealth['disk_free_space'] }}</span>
            </div>
        </div>
    </div>
</div>

<div class="grid! grid-cols-2! gap-5! mb-5! max-sm:grid-cols-1! max-sm:gap-4!">
    <div class="bg-white! border! border-[#ddd]! rounded! p-5! mb-5!">
        <h2 class="m-0! mb-4! text-lg! text-[#333]! border-b! border-[#eee]! pb-2.5!">Recent Users</h2>
        <table class="w-full! border-collapse! overflow-x-auto! block!">
            <thead>
                <tr>
                    <th class="p-2.5! px-3! text-left! border-b! border-[#eee]! whitespace-nowrap! bg-[#f9f9f9]! font-semibold! text-xs! uppercase! text-[#666]!">Email</th>
                    <th class="p-2.5! px-3! text-left! border-b! border-[#eee]! whitespace-nowrap! bg-[#f9f9f9]! font-semibold! text-xs! uppercase! text-[#666]!">Status</th>
                    <th class="p-2.5! px-3! text-left! border-b! border-[#eee]! whitespace-nowrap! bg-[#f9f9f9]! font-semibold! text-xs! uppercase! text-[#666]!">Joined</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentActivity['users'] as $user)
                <tr class="hover:bg-[#fafafa]!">
                    <td class="p-2.5! px-3! text-left! border-b! border-[#eee]! whitespace-nowrap!">{{ $user->email }}</td>
                    <td class="p-2.5! px-3! text-left! border-b! border-[#eee]! whitespace-nowrap!">
                        @if($user->verified)
                        <span class="inline-block! py-0.5! px-2! rounded-[3px]! text-[11px]! font-semibold! uppercase! bg-[#d4edda]! text-[#155724]!">Verified</span>
                        @else
                        <span class="inline-block! py-0.5! px-2! rounded-[3px]! text-[11px]! font-semibold! uppercase! bg-[#f8d7da]! text-[#721c24]!">Pending</span>
                        @endif
                    </td>
                    <td class="p-2.5! px-3! text-left! border-b! border-[#eee]! whitespace-nowrap!">{{ $user->created_at->diffForHumans() }}</td>
                </tr>
                @empty
                <tr class="hover:bg-[#fafafa]!"><td class="p-2.5! px-3! text-left! border-b! border-[#eee]! whitespace-nowrap!" colspan="3">No users yet</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="bg-white! border! border-[#ddd]! rounded! p-5! mb-5!">
        <h2 class="m-0! mb-4! text-lg! text-[#333]! border-b! border-[#eee]! pb-2.5!">Recent Petitions</h2>
        <table class="w-full! border-collapse! overflow-x-auto! block!">
            <thead>
                <tr>
                    <th class="p-2.5! px-3! text-left! border-b! border-[#eee]! whitespace-nowrap! bg-[#f9f9f9]! font-semibold! text-xs! uppercase! text-[#666]!">ID</th>
                    <th class="p-2.5! px-3! text-left! border-b! border-[#eee]! whitespace-nowrap! bg-[#f9f9f9]! font-semibold! text-xs! uppercase! text-[#666]!">Status</th>
                    <th class="p-2.5! px-3! text-left! border-b! border-[#eee]! whitespace-nowrap! bg-[#f9f9f9]! font-semibold! text-xs! uppercase! text-[#666]!">Created</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentActivity['petitions'] as $petition)
                <tr class="hover:bg-[#fafafa]!">
                    <td class="p-2.5! px-3! text-left! border-b! border-[#eee]! whitespace-nowrap!">#{{ $petition->id }}</td>
                    <td class="p-2.5! px-3! text-left! border-b! border-[#eee]! whitespace-nowrap!">
                        <span class="inline-block! py-0.5! px-2! rounded-[3px]! text-[11px]! font-semibold! uppercase! {{ $petition->status === 'published' ? 'bg-[#d4edda]! text-[#155724]!' : ($petition->status === 'draft' ? 'bg-[#fff3cd]! text-[#856404]!' : 'bg-[#f8d7da]! text-[#721c24]!') }}">{{ $petition->status }}</span>
                    </td>
                    <td class="p-2.5! px-3! text-left! border-b! border-[#eee]! whitespace-nowrap!">{{ $petition->created_at->diffForHumans() }}</td>
                </tr>
                @empty
                <tr class="hover:bg-[#fafafa]!"><td class="p-2.5! px-3! text-left! border-b! border-[#eee]! whitespace-nowrap!" colspan="3">No petitions yet</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
