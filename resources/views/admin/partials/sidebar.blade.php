<nav class="fc-menu w-[210px]! bg-[#d7d7d7]! border! border-[#9e9e9e]! rounded! p-1.5! box-border! shrink-0! max-md:fixed! max-md:top-0! max-md:left-0! max-md:bottom-0! max-md:max-w-[85vw]! max-md:rounded-none! max-md:border-r! max-md:border-l-0! max-md:border-t-0! max-md:border-b-0! max-md:overflow-y-auto! max-md:pt-[58px]!" id="fc-menu">
    <div class="hidden! max-md:block! max-md:p-[10px_8px]! max-md:-m-1.5! max-md:mb-3! max-md:border-b! max-md:border-[#9e9e9e]!">
        <strong>{{ admin_user()?->name ?? 'admin' }}</strong>
        <form method="POST" action="{{ route('admin.logout') }}" class="inline!">
            @csrf
            <button type="submit" class="border! border-[#7d7d7d]! bg-[#e6e6e6]! px-2.5! py-1! rounded-[3px]! cursor-pointer! hover:bg-[#dcdcdc]! mt-1.5! max-md:text-xs! max-md:px-3! max-md:py-1.5!">exit</button>
        </form>
    </div>

    <div class="fc-group">
        <div class="group-title font-bold! p-1.5! px-2! bg-[#c7c7c7]! border! border-[#9e9e9e]! rounded-[3px]! my-1.5! relative! cursor-pointer! select-none! pr-6!">overview</div>
        <a href="{{ route('admin.dashboard') }}" class="block! p-1.5! px-2! no-underline! rounded-[3px]! text-sm! max-md:text-[13px]! max-md:p-2! max-md:px-2.5! {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">dashboard</a>
    </div>

    <div class="fc-group">
        <div class="group-title font-bold! p-1.5! px-2! bg-[#c7c7c7]! border! border-[#9e9e9e]! rounded-[3px]! my-1.5! relative! cursor-pointer! select-none! pr-6!">site</div>
        @if (admin_can('options', 'view'))
            <a href="{{ route('admin.options.global') }}" class="block! p-1.5! px-2! no-underline! rounded-[3px]! text-sm! max-md:text-[13px]! max-md:p-2! max-md:px-2.5! {{ request()->routeIs('admin.options.global') ? 'active' : '' }}">site settings</a>
            <a href="{{ route('admin.options.language') }}" class="block! p-1.5! px-2! no-underline! rounded-[3px]! text-sm! max-md:text-[13px]! max-md:p-2! max-md:px-2.5! {{ request()->routeIs('admin.options.language') ? 'active' : '' }}">localized content</a>
            <a href="{{ route('admin.ads') }}" class="block! p-1.5! px-2! no-underline! rounded-[3px]! text-sm! max-md:text-[13px]! max-md:p-2! max-md:px-2.5! {{ request()->routeIs('admin.ads') ? 'active' : '' }}">ads.txt</a>
        @endif
        @if (admin_can('users', 'view'))
            <a href="{{ route('admin.users') }}" class="block! p-1.5! px-2! no-underline! rounded-[3px]! text-sm! max-md:text-[13px]! max-md:p-2! max-md:px-2.5! {{ request()->routeIs('admin.users') ? 'active' : '' }}">users</a>
        @endif
        @if (admin_can('categories', 'view'))
            <a href="{{ route('admin.categories') }}" class="block! p-1.5! px-2! no-underline! rounded-[3px]! text-sm! max-md:text-[13px]! max-md:p-2! max-md:px-2.5! {{ request()->routeIs('admin.categories') ? 'active' : '' }}">categories</a>
        @endif
        @if (admin_can('petitions', 'view'))
            <a href="{{ route('admin.petitions') }}" class="block! p-1.5! px-2! no-underline! rounded-[3px]! text-sm! max-md:text-[13px]! max-md:p-2! max-md:px-2.5! {{ request()->routeIs('admin.petitions') ? 'active' : '' }}">petitions</a>
        @endif
        @if (admin_can('signatures', 'view'))
            <a href="{{ route('admin.signatures') }}" class="block! p-1.5! px-2! no-underline! rounded-[3px]! text-sm! max-md:text-[13px]! max-md:p-2! max-md:px-2.5! {{ request()->routeIs('admin.signatures') ? 'active' : '' }}">signatures</a>
        @endif
        @if (admin_can('pages', 'view'))
            <a href="{{ route('admin.pages') }}" class="block! p-1.5! px-2! no-underline! rounded-[3px]! text-sm! max-md:text-[13px]! max-md:p-2! max-md:px-2.5! {{ request()->routeIs('admin.pages') ? 'active' : '' }}">pages</a>
        @endif
        @if (admin_can('spam', 'view'))
            <a href="{{ route('admin.spam') }}" class="block! p-1.5! px-2! no-underline! rounded-[3px]! text-sm! max-md:text-[13px]! max-md:p-2! max-md:px-2.5! {{ request()->routeIs('admin.spam') ? 'active' : '' }}">spam</a>
        @endif
        @if (admin_can('stats', 'view'))
            <a href="{{ route('admin.stats') }}" class="block! p-1.5! px-2! no-underline! rounded-[3px]! text-sm! max-md:text-[13px]! max-md:p-2! max-md:px-2.5! {{ request()->routeIs('admin.stats') ? 'active' : '' }}">statistics</a>
        @endif
        @if (admin_can('logs', 'view'))
            <a href="{{ route('admin.logs') }}" class="block! p-1.5! px-2! no-underline! rounded-[3px]! text-sm! max-md:text-[13px]! max-md:p-2! max-md:px-2.5! {{ request()->routeIs('admin.logs') ? 'active' : '' }}">logs</a>
        @endif
        @if (admin_can('languages', 'view'))
            <a href="{{ route('admin.languages.index') }}" class="block! p-1.5! px-2! no-underline! rounded-[3px]! text-sm! max-md:text-[13px]! max-md:p-2! max-md:px-2.5! {{ request()->routeIs('admin.languages.index') ? 'active' : '' }}">languages</a>
        @endif
    </div>

    @if (admin_can('system', 'manage'))
        <div class="fc-group">
            <div class="group-title font-bold! p-1.5! px-2! bg-[#c7c7c7]! border! border-[#9e9e9e]! rounded-[3px]! my-1.5! relative! cursor-pointer! select-none! pr-6!">system</div>
            <a href="{{ route('admin.system.user_info') }}" class="block! p-1.5! px-2! no-underline! rounded-[3px]! text-sm! max-md:text-[13px]! max-md:p-2! max-md:px-2.5! {{ request()->routeIs('admin.system.user_info') ? 'active' : '' }}">user information</a>
            <a href="{{ route('admin.system.user_levels') }}" class="block! p-1.5! px-2! no-underline! rounded-[3px]! text-sm! max-md:text-[13px]! max-md:p-2! max-md:px-2.5! {{ request()->routeIs('admin.system.user_levels') ? 'active' : '' }}">user levels</a>
            <a href="{{ route('admin.system.permissions') }}" class="block! p-1.5! px-2! no-underline! rounded-[3px]! text-sm! max-md:text-[13px]! max-md:p-2! max-md:px-2.5! {{ request()->routeIs('admin.system.permissions') ? 'active' : '' }}">permissions</a>
        </div>
    @endif
</nav>
