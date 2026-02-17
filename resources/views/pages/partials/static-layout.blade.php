<section class="breadcrumb-section">
    <div class="container">
        <div class="row">
            <div class="col-md-12">

                <h2 class="page-titles">{{ $title }}</h2>

                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb m-0 p-0">
                        <li class="breadcrumb-item">
                            <a href="{{ lroute('home') }}">Home</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">
                            {{ $title }}
                        </li>
                    </ol>
                </nav>

            </div>
        </div>
    </div>
</section>

<section class="petitions-list py-5">
    <div class="container">
        <div class="row">
            <div class="col-md-12">

                <div class="card">
                    <div class="card-body">

                        <div class="mb-4">
                            <h4 class="headings">{{ $title }}</h4>

                            <div class="privacy-inner">
                                {!! $content !!}
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </div>
</section>
