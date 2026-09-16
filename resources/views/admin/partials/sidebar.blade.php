<nav class="fc-menu">
    <div class="group-title">overview</div>
    <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">dashboard</a>

    <div class="group-title">site</div>
    <a href="{{ route('admin.petitions') }}" class="{{ request()->routeIs('admin.petitions') ? 'active' : '' }}">petitions</a>
    <a href="{{ route('admin.users') }}" class="{{ request()->routeIs('admin.users') ? 'active' : '' }}">users</a>
    <a href="{{ route('admin.categories') }}" class="{{ request()->routeIs('admin.categories') ? 'active' : '' }}">categories</a>
    <a href="{{ route('admin.signatures') }}" class="{{ request()->routeIs('admin.signatures') ? 'active' : '' }}">signatures</a>
    <a href="{{ route('admin.languages.index') }}" class="{{ request()->routeIs('admin.languages.index') ? 'active' : '' }}">languages</a>
</nav>
