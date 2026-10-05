{{--
    Admin sidebar — one data-driven loop, normalized spacing lives in app.css
    (.fc-menu rules). Collapse behavior (fc-group/group-title) is unchanged:
    element-level inline styles applied by admin.js, key by group label.
--}}
<nav class="fc-menu w-[210px]! bg-[#d7d7d7]! border! border-[#9e9e9e]! rounded! p-1.5! box-border! shrink-0! max-md:fixed! max-md:top-0! max-md:left-0! max-md:bottom-0! max-md:max-w-[85vw]! max-md:rounded-none! max-md:border-r! max-md:border-l-0! max-md:border-t-0! max-md:border-b-0! max-md:overflow-y-auto! max-md:pt-[58px]!" id="fc-menu">
    <div class="hidden! max-md:block! max-md:p-[10px_8px]! max-md:-m-1.5! max-md:mb-3! max-md:border-b! max-md:border-[#9e9e9e]!">
        <strong>{{ admin_user()?->name ?? 'admin' }}</strong>
        <form method="POST" action="{{ route('admin.logout') }}" class="inline!">
            @csrf
            <button type="submit" class="fc-abtn mt-1.5! max-md:text-xs!">exit</button>
        </form>
    </div>

    @php
        $groups = [
            [
                'label' => 'overview',
                'links' => [
                    ['route' => 'admin.dashboard', 'label' => 'dashboard', 'is' => 'admin.dashboard', 'module' => null],
                ],
            ],
            [
                'label' => 'site',
                'links' => [
                    ['route' => 'admin.options.global', 'label' => 'site settings', 'is' => 'admin.options.global', 'module' => 'options'],
                    ['route' => 'admin.options.language', 'label' => 'localized content', 'is' => 'admin.options.language', 'module' => 'options'],
                    ['route' => 'admin.ads', 'label' => 'ads.txt', 'is' => 'admin.ads', 'module' => 'options'],
                    ['route' => 'admin.users', 'label' => 'users', 'is' => 'admin.users', 'module' => 'users'],
                    ['route' => 'admin.categories', 'label' => 'categories', 'is' => 'admin.categories', 'module' => 'categories'],
                    ['route' => 'admin.petitions', 'label' => 'petitions', 'is' => 'admin.petitions', 'module' => 'petitions'],
                    ['route' => 'admin.signatures', 'label' => 'signatures', 'is' => 'admin.signatures', 'module' => 'signatures'],
                    ['route' => 'admin.pages', 'label' => 'pages', 'is' => 'admin.pages', 'module' => 'pages'],
                    ['route' => 'admin.spam', 'label' => 'spam', 'is' => 'admin.spam', 'module' => 'spam'],
                    ['route' => 'admin.stats', 'label' => 'statistics', 'is' => 'admin.stats', 'module' => 'stats'],
                    ['route' => 'admin.logs', 'label' => 'logs', 'is' => 'admin.logs', 'module' => 'logs'],
                    ['route' => 'admin.languages.index', 'label' => 'languages', 'is' => 'admin.languages.index', 'module' => 'languages'],
                ],
            ],
        ];

        $systemLinks = [
            ['route' => 'admin.system.user_info', 'label' => 'user information', 'is' => 'admin.system.user_info', 'module' => null],
            ['route' => 'admin.system.user_levels', 'label' => 'user levels', 'is' => 'admin.system.user_levels', 'module' => null],
            ['route' => 'admin.system.permissions', 'label' => 'permissions', 'is' => 'admin.system.permissions', 'module' => null],
        ];
    @endphp

    @foreach ($groups as $group)
        <div class="fc-group fc-side-group">
            <div class="group-title">{{ $group['label'] }}</div>
            @foreach ($group['links'] as $link)
                @if ($link['module'] === null || admin_can($link['module'], 'view'))
                    <a href="{{ route($link['route']) }}"
                        class="fc-side-link {{ request()->routeIs($link['is']) ? 'active' : '' }}">{{ $link['label'] }}</a>
                @endif
            @endforeach
        </div>
    @endforeach

    @if (admin_can('system', 'manage'))
        <div class="fc-group fc-side-group">
            <div class="group-title">system</div>
            @foreach ($systemLinks as $link)
                <a href="{{ route($link['route']) }}"
                    class="fc-side-link {{ request()->routeIs($link['is']) ? 'active' : '' }}">{{ $link['label'] }}</a>
            @endforeach
        </div>
    @endif
</nav>
