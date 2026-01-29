<nav class="navbar navbar-expand-lg bg-white shadow-sm fc-navbar">
    <div class="container">
        <a class="navbar-brand" href="/{{ app()->getLocale() }}">
            <img src="{{ asset('legacy/images/logo7.png') }}" class="img-fluid" alt="logo">
        </a>

        {{-- mobile toggler --}}
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#fcNav"
            aria-controls="fcNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="fcNav">
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-lg-center">
                <li class="nav-item"><a class="nav-link" href="/{{ app()->getLocale() }}/petitions">Explore
                        petitions</a></li>
                <li class="nav-item"><a class="nav-link" href="/magazine/">Magazine</a></li>
                <li class="nav-item"><a class="nav-link" href="/{{ app()->getLocale() }}/faqs">Help</a></li>
                <li class="nav-item"><a class="nav-link" href="/{{ app()->getLocale() }}/login">Login</a></li>

                <li class="nav-item ms-lg-3">
                    <a class="btn btn-primary fc-startfree" href="#">Start Free</a>
                </li>

                {{-- flag switcher on far right --}}
                <li class="nav-item ms-lg-4">
                    @include('partials.language_switch')
                </li>
            </ul>
        </div>
    </div>
</nav>
