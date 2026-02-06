@php
    $isAuthed = auth()->check();
    $c = $navbarContent ?? [];
@endphp

<nav class="navbar navbar-expand-lg bg-white shadow-sm fc-navbar">
    <div class="container">
        <a class="navbar-brand" href="{{ lroute('home') }}">
            <img src="{{ asset('legacy/images/logo7.png') }}" class="img-fluid" alt="logo">
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#fcNav"
            aria-controls="fcNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="fcNav">
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-lg-center">
                <li class="nav-item">
                    <a class="nav-link" href="{{ lroute('petitions.index') }}">
                        {{ $c['nav_explore'] ?? 'Explore petitions' }}
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="{{ lroute('magazine') }}">
                        {{ $c['nav_magazine'] ?? 'Magazine' }}
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="{{ lroute('faqs') }}">
                        {{ $c['nav_help'] ?? 'Help' }}
                    </a>
                </li>

                @if (!$isAuthed)
                    <li class="nav-item">
                        <a class="nav-link" href="{{ lroute('login') }}">
                            {{ $c['nav_login'] ?? 'Login' }}
                        </a>
                    </li>
                @else
                    <li class="nav-item">
                        <a class="nav-link" href="{{ lroute('profile') }}">{{ auth()->user()->name }}</a>
                    </li>

                    <li class="nav-item ms-lg-2 me-lg-3">
                        <form method="POST" action="{{ lroute('logout') }}" class="d-inline">
                            @csrf
                            <input type="hidden" name="locale" value="{{ app()->getLocale() }}">
                            <button type="submit" class="nav-link btn btn-link p-0 fc-logout">
                                {{ $c['nav_logout'] ?? 'Logout' }}
                            </button>
                        </form>
                    </li>
                @endif

                <li class="nav-item ms-lg-3">
                    <a class="btn btn-primary fc-startfree" href="#">
                        {{ $c['nav_startfree'] ?? 'Start Free' }}
                    </a>
                </li>

                <li class="nav-item ms-lg-4">
                    @include('partials.language_switch')
                </li>
            </ul>
        </div>
    </div>
</nav>
