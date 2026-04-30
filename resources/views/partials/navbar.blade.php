@php
    $isAuthed = auth()->check();
    $c = $navbarContent ?? [];
@endphp
<nav class="navbar navbar-expand-lg bg-white shadow-sm fc-navbar">
    <div class="container">
        <a class="navbar-brand" href="{{ lroute('home') }}">
            <img src="{{ asset('legacy/images/xpetition-logo.png') }}" class="img-fluid" style="max-height:40px;" loading="lazy" alt="xPetition">
        </a>

        <div class="d-flex align-items-center gap-2 d-lg-none ms-auto">
            @include('partials.language_switch')
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#fcNav"
                aria-controls="fcNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
        </div>

        <div class="collapse navbar-collapse" id="fcNav">
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-lg-center">
                <li class="nav-item">
                    <a class="nav-link" href="{{ lroute('petitions.index') }}">
                        {{ $c['nav_explore'] ?? trans_db('nav.explore') }}
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/magazine">
                        {{ $c['nav_magazine'] ?? trans_db('nav.magazine') }}
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ lroute('faqs') }}">
                        {{ $c['nav_help'] ?? trans_db('nav.help') }}
                    </a>
                </li>
                @if (!$isAuthed)
                    <li class="nav-item">
                        <a class="nav-link" href="{{ lroute('login') }}">
                            {{ $c['nav_login'] ?? trans_db('auth.login') }}
                        </a>
                    </li>
                @else
                    <li class="nav-item">
                        <a class="nav-link" href="{{ lroute('profile') }}">{{ auth()->user()->display_name }}</a>
                    </li>
                    <li class="nav-item ms-lg-2 me-lg-3">
                        <form method="POST" action="{{ lroute('logout') }}" class="d-inline">
                            @csrf
                            <input type="hidden" name="locale" value="{{ app()->getLocale() }}">
                            <button type="submit" class="nav-link btn btn-link p-0 fc-logout">
                                {{ $c['nav_logout'] ?? trans_db('nav.logout') }}
                            </button>
                        </form>
                    </li>
                @endif

                <li class="nav-item mt-2 mt-lg-0 ms-lg-3">
                    <a class="btn btn-primary fc-startfree w-100 w-lg-auto" href="{{ lroute('petition.create') }}">
                        {{ $c['nav_startfree'] ?? trans_db('nav.startfree') }}
                    </a>
                </li>

                <li class="nav-item ms-lg-4 d-none d-lg-block">
                    @include('partials.language_switch')
                </li>
            </ul>
        </div>
    </div>
</nav>
