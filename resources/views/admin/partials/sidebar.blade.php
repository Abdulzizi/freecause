<nav class="fc-menu">
    <div class="group-title">site</div>
    <a href="{{ route('admin.options.global') }}" class="{{ request()->routeIs('admin.options.global') ? 'active' : '' }}">global options</a>
    <a href="{{ route('admin.options.language') }}" class="{{ request()->routeIs('admin.options.language') ? 'active' : '' }}">language options</a>
    <a href="{{ route('admin.ads') }}" class="{{ request()->routeIs('admin.ads') ? 'active' : '' }}">ads.txt</a>
    <a href="{{ route('admin.languages.index') }}" class="{{ request()->routeIs('admin.languages.index') ? 'active' : '' }}">languages</a>
    <a href="{{ route('admin.users') }}" class="{{ request()->routeIs('admin.users') ? 'active' : '' }}">users</a>
    <a href="{{ route('admin.categories') }}" class="{{ request()->routeIs('admin.categories') ? 'active' : '' }}">categories</a>
    <a href="{{ route('admin.petitions') }}" class="{{ request()->routeIs('admin.petitions') ? 'active' : '' }}">petitions</a>
    {{-- <a href="{{ route('admin.fanpages') }}" class="{{ request()->routeIs('admin.fanpages') ? 'active' : '' }}">fanpages</a> --}}
    <a href="{{ route('admin.signatures') }}" class="{{ request()->routeIs('admin.signatures') ? 'active' : '' }}">signatures</a>
    <a href="{{ route('admin.pages') }}" class="{{ request()->routeIs('admin.pages') ? 'active' : '' }}">pages</a>
    <a href="{{ route('admin.spam') }}" class="{{ request()->routeIs('admin.spam') ? 'active' : '' }}">spam</a>
    <a href="{{ route('admin.stats') }}" class="{{ request()->routeIs('admin.stats') ? 'active' : '' }}">stats</a>
    <a href="{{ route('admin.logs') }}" class="{{ request()->routeIs('admin.logs') ? 'active' : '' }}">logs</a>

    <div class="group-title">system</div>
    <a href="{{ route('admin.system.user_info') }}" class="{{ request()->routeIs('admin.system.user_info') ? 'active' : '' }}">user info</a>
    <a href="{{ route('admin.system.user_levels') }}" class="{{ request()->routeIs('admin.system.user_levels') ? 'active' : '' }}">user levels</a>
    <a href="{{ route('admin.system.permissions') }}" class="{{ request()->routeIs('admin.system.permissions') ? 'active' : '' }}">permissions</a>

    <div class="group-title">utilities</div>
    <a href="{{ route('admin.utils.import') }}" class="{{ request()->routeIs('admin.utils.import') ? 'active' : '' }}">import</a>
</nav>
