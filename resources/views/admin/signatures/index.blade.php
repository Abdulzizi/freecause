@extends('admin.layouts.app')

@section('title', 'Signatures')

@section('content')

    @php
        $icoLink = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                        <path d="M10 13a5 5 0 0 1 0-7l2-2a5 5 0 0 1 7 7l-1 1" stroke="#212529" stroke-width="2"/>
                        <path d="M14 11a5 5 0 0 1 0 7l-2 2a5 5 0 0 1-7-7l1-1" stroke="#212529" stroke-width="2"/>
                    </svg>';
    @endphp

    <x-admin.page-header title="signatures" :count="$approxTotal" />

    @php
        $activeFilterCount = collect($filters)->filter(fn($v) => $v !== '' && $v !== null)->count();
    @endphp

    <x-admin.filter-box title="filter signatures" :action="route('admin.signatures')" :reset="route('admin.signatures')" :activeCount="$activeFilterCount">

        <input class="fc-filter-field" name="petition_id" placeholder="Petition ID" value="{{ $filters['petition_id'] ?? '' }}">

        <input class="fc-filter-field" name="user_id" placeholder="User ID" value="{{ $filters['user_id'] ?? '' }}">

        <input class="fc-filter-field" name="email" placeholder="Email" value="{{ $filters['email'] ?? '' }}">

        <input class="fc-filter-field" name="ip" placeholder="IP" value="{{ $filters['ip'] ?? '' }}">

        <input class="fc-filter-field" name="text" placeholder="Text" value="{{ $filters['text'] ?? '' }}">

        <select data-admin-autosubmit class="fc-filter-field" name="locale">
            @foreach ($locales as $k => $label)
                <option value="{{ $k }}" {{ ($filters['locale'] ?? '') == $k ? 'selected' : '' }}>
                    {{ $label }}
                </option>
            @endforeach
        </select>

        <select data-admin-autosubmit class="fc-filter-field" name="category_id">
            <option value="">(Category)</option>
            @foreach ($categories as $c)
                <option value="{{ $c->id }}" {{ ($filters['category_id'] ?? '') == $c->id ? 'selected' : '' }}>
                    {{ $c->name }}
                </option>
            @endforeach
        </select>

        <select data-admin-autosubmit class="fc-filter-field" name="confirmed">
            <option value="">all signatures</option>
            <option value="1" {{ ($filters['confirmed'] ?? '') === '1' ? 'selected' : '' }}>confirmed</option>
            <option value="0" {{ ($filters['confirmed'] ?? '') === '0' ? 'selected' : '' }}>unconfirmed</option>
        </select>

        <select data-admin-autosubmit class="fc-filter-field" name="spam">
            <option value="">spam...</option>
            <option value="1" {{ ($filters['spam'] ?? '') === '1' ? 'selected' : '' }}>spam</option>
            <option value="0" {{ ($filters['spam'] ?? '') === '0' ? 'selected' : '' }}>not spam</option>
        </select>

        <input class="fc-filter-field" type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}">
        <input class="fc-filter-field" type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}">

    </x-admin.filter-box>

    <x-admin.data-table emptyText="no signatures found" :p="$signatures" :emptyClearRoute="route('admin.signatures')" :bulk="[]">

        <x-slot:thead>
            <tr class="border-b border-border">
                <th width="70">ID</th>
                <th width="26" title="Bulk selection" aria-label="Bulk selection"></th>
                <th width="26" data-hide-mobile title="User email verified" aria-label="User email verified">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#5b6470" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                </th>
                <th width="26" title="Signature confirmed" data-hide-mobile>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#5b6470" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                </th>
                <th>User</th>
                <th width="26" data-hide-mobile title="Open petition on site">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#5b6470" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 13a5 5 0 0 1 0-7l2-2a5 5 0 0 1 7 7l-1 1"/><path d="M14 11a5 5 0 0 1 0 7l-2 2a5 5 0 0 1-7-7l1-1"/></svg>
                </th>
                <th>Title</th>
                <th data-hide-mobile>Text</th>
                <th width="160">Date</th>
                @if (admin_can('signatures', 'edit'))
                    <th data-hide-mobile>Actions</th>
                @endif
            </tr>
        </x-slot:thead>

        <x-slot:tbody>
            @foreach ($signatures as $s)
                <tr class="border-b border-border">

                    <td>{{ $s->id }}</td>

                    <td>
                        @if (admin_can('signatures', 'edit') || admin_can('signatures', 'delete'))
                            <input type="checkbox" class="bulk-checkbox" value="{{ $s->id }}" aria-label="Select signature {{ $s->id }}">
                        @endif
                    </td>

                    <td data-hide-mobile class="text-center">
                        @if ($s->user_verified)
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#198754" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        @else
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#dc3545" stroke-width="2.5" stroke-linecap="round"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
                        @endif
                    </td>

                    <td data-hide-mobile class="text-center" data-signature-status>
                        @if ($s->is_spam)
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#dc3545" stroke-width="2.5" stroke-linecap="round" title="Rejected"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
                        @elseif ($s->confirmed)
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#198754" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" title="Confirmed"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        @else
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#dc3545" stroke-width="2.5" stroke-linecap="round" title="Unconfirmed"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
                        @endif
                    </td>

                    <td>
                        @if ($s->user_id)
                            <a href="{{ route('admin.users', ['select' => $s->user_id]) }}">
                                {{ $s->user_name }}
                            </a>
                        @elseif($s->email)
                            <a href="mailto:{{ $s->email }}">
                                {{ $s->email }}
                            </a>
                        @else
                            {{ $s->name }}
                        @endif
                    </td>

                    <td data-hide-mobile class="text-center">
                        @if ($s->petition_slug)
                            <a
                                href="{{ route('petition.show', [
                                    'locale' => $s->locale,
                                    'slug' => $s->petition_slug,
                                    'id' => $s->petition_id,
                                ]) }}">
                                {!! $icoLink !!}
                            </a>
                        @endif
                    </td>

                    <td>
                        <a href="{{ route('admin.petitions', ['id' => $s->petition_id]) }}">
                            {{ $s->petition_title ?: 'petition #' . $s->petition_id }}
                        </a>
                    </td>

                    <td data-hide-mobile class="max-w-[300px] overflow-hidden text-ellipsis whitespace-nowrap">
                        {{ $s->text }}
                    </td>

                    <td>{{ $s->created_at }}</td>

                    @if (admin_can('signatures', 'edit'))
                        <td data-hide-mobile class="fc-row-actions-cell">
                            <div class="inline-flex" data-signature-actions data-confirmed="{{ $s->confirmed && ! $s->is_spam ? '1' : '0' }}">
                                <x-admin.row-actions>
                                    <x-blatui.dropdown-menu-item type="button" data-signature-action="approve"
                                        data-url="{{ route('admin.signatures.action', ['signature' => $s->id]) }}"
                                        :disabled="$s->confirmed && ! $s->is_spam"
                                        title="{{ $s->confirmed && ! $s->is_spam ? 'Already confirmed' : 'Approve signature' }}">
                                        Approve
                                    </x-blatui.dropdown-menu-item>
                                    <x-blatui.dropdown-menu-item type="button" data-signature-action="reject"
                                        data-url="{{ route('admin.signatures.action', ['signature' => $s->id]) }}"
                                        :disabled="(bool) $s->is_spam"
                                        title="{{ $s->is_spam ? 'Already rejected' : 'Reject signature' }}">
                                        Reject
                                    </x-blatui.dropdown-menu-item>
                                </x-admin.row-actions>
                            </div>
                        </td>
                    @endif

                </tr>
            @endforeach
        </x-slot:tbody>

        <x-slot:footer>
            <div class="flex gap-2 items-center flex-wrap">
                @if (admin_can('signatures', 'edit'))
                    <x-blatui.button-group>
                        <x-blatui.button variant="outline" size="sm" type="button" class="bulk-action" data-action="approve" title="Approve">
                            <x-lucide-check aria-hidden="true" />
                            <span class="max-md:hidden">Approve</span>
                        </x-blatui.button>
                        <x-blatui.button variant="outline" size="sm" type="button" class="bulk-action" data-action="reject" title="Reject">
                            <x-lucide-x aria-hidden="true" />
                            <span class="max-md:hidden">Reject</span>
                        </x-blatui.button>
                    </x-blatui.button-group>
                @endif

                @if (admin_can('signatures', 'delete'))
                    <x-blatui.button variant="destructive" size="sm" type="button" class="bulk-action" data-action="delete" title="Delete">
                        <x-lucide-trash-2 aria-hidden="true" />
                        <span class="max-md:hidden">Delete</span>
                    </x-blatui.button>
                @endif

                <x-blatui.button variant="outline" size="sm" :href="route('admin.signatures', request()->except('select'))" title="Clear selection">
                    <x-lucide-x aria-hidden="true" />
                    <span class="max-md:hidden">Clear</span>
                </x-blatui.button>
            </div>
        </x-slot:footer>

    </x-admin.data-table>

    @include('admin.partials.bulk-js', [
        'actionRoute' => route('admin.signatures.bulkAction'),
        'noun' => 'signatures',
        'emptyMsg' => 'No signatures selected',
    ])

@endsection
