<nav class="fc-menu">
    <div class="group-title">overview</div>
    <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">dashboard</a>

    <div class="group-title">site</div>
    @if (admin_can('options', 'view'))
        <a href="{{ route('admin.options.global') }}" class="{{ request()->routeIs('admin.options.global') ? 'active' : '' }}">global options</a>
        <a href="{{ route('admin.options.language') }}" class="{{ request()->routeIs('admin.options.language') ? 'active' : '' }}">homepage content</a>
        <a href="{{ route('admin.ads') }}" class="{{ request()->routeIs('admin.ads') ? 'active' : '' }}">ads.txt</a>
    @endif
    @if (admin_can('users', 'view'))
        <a href="{{ route('admin.users') }}" class="{{ request()->routeIs('admin.users') ? 'active' : '' }}">users</a>
    @endif
    @if (admin_can('categories', 'view'))
        <a href="{{ route('admin.categories') }}" class="{{ request()->routeIs('admin.categories') ? 'active' : '' }}">categories</a>
    @endif
    @if (admin_can('petitions', 'view'))
        <a href="{{ route('admin.petitions') }}" class="{{ request()->routeIs('admin.petitions') ? 'active' : '' }}">petitions</a>
    @endif
    @if (admin_can('signatures', 'view'))
        <a href="{{ route('admin.signatures') }}" class="{{ request()->routeIs('admin.signatures') ? 'active' : '' }}">signatures</a>
    @endif
    @if (admin_can('pages', 'view'))
        <a href="{{ route('admin.pages') }}" class="{{ request()->routeIs('admin.pages') ? 'active' : '' }}">pages</a>
    @endif
    @if (admin_can('spam', 'view'))
        <a href="{{ route('admin.spam') }}" class="{{ request()->routeIs('admin.spam') ? 'active' : '' }}">spam</a>
    @endif
    @if (admin_can('stats', 'view'))
        <a href="{{ route('admin.stats') }}" class="{{ request()->routeIs('admin.stats') ? 'active' : '' }}">statistics</a>
    @endif
    @if (admin_can('logs', 'view'))
        <a href="{{ route('admin.logs') }}" class="{{ request()->routeIs('admin.logs') ? 'active' : '' }}">logs</a>
    @endif
    @if (admin_can('languages', 'view'))
        <a href="{{ route('admin.languages.index') }}" class="{{ request()->routeIs('admin.languages.index') ? 'active' : '' }}">languages</a>
    @endif

    @if (admin_can('system', 'manage'))
        <div class="group-title">system</div>
        <a href="{{ route('admin.system.user_info') }}" class="{{ request()->routeIs('admin.system.user_info') ? 'active' : '' }}">user information</a>
        <a href="{{ route('admin.system.user_levels') }}" class="{{ request()->routeIs('admin.system.user_levels') ? 'active' : '' }}">user levels</a>
        <a href="{{ route('admin.system.permissions') }}" class="{{ request()->routeIs('admin.system.permissions') ? 'active' : '' }}">permissions</a>
    @endif
</nav>
