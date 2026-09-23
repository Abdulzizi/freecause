@extends('layouts.legacy')

@section('title', 'Personal Data Processing - FreeCause')
@section('body_class', 'personal-data-processing')

@section('content')
    <section class="breadcrumb-section">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <h2 class="page-titles">Personal Data Processing</h2>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb m-0 p-0">
                            <li class="breadcrumb-item">
                                <a href="{{ lroute('home') }}">Home</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Personal Data Processing</li>
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
                                <h4 class="headings">Consent to the processing of personal data</h4>

                                <div class="privacy-inner">
                                    <p>Authorization for communication and/or transfer of data to partners and third parties operating in the fields of services, editorial, energy, telephony, tourism, communication, entertainment, financial, insurance, automotive, large-scale consumption, humanitarian and charitable organizations Advertising material and other promotional and commercial purposes by means of mail or telephone and/or automated call or call communication systems without the intervention of an operator and/or electronic communications such as e-mail.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection