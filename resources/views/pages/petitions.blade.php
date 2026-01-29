@extends('layouts.legacy')

@section('title', 'All the petitions - FreeCause')

@php
    // static mock data
    $petitions = [
        'Request for Public Libraries in Algeria',
        'Combata a desinformação sobre o Credo cristão',
        'Substituição do material didático de inglês',
        'PETITION FOR A MORE EFFICIENT AND ACCESSIBLE CONSULAR CARD ISSUANCE PROCESS',
        'Петиция об отстранении заместителя директора Авиазера',
        'Bring a Trader Joe’s to New Tampa & Wesley Chapel',
        'Petition to Support Fair Crypto Taxation & Web3 Development in India',
        'PÉTITION À L’ATTENTION DE LA COMMISSIONCANTINE',
        'Our voices matter: reevaluate and reinstate ED Manager Miriana “Mimi” Pascas',
        'REJECTION OF THE EAC SEXUAL AND REPRODUCTIVE HEALTH BILL, 2024',
        'Add 2 sentences to NHS thrush page | Warning about the link to vulvodynia',
        'Safe Bike Baku',
        'SAVE OUR KIDS – KEEP OUR NEIGHBORHOOD AT HERITAGE ELEMENTARY',
    ];
    $demoPetitionUrl = url('/' . app()->getLocale() . '/petition/stop-using-plastics-in-our-oceans/75241');
@endphp


@section('content')
    <section class="py-5">
        <div class="container">

            <div class="mb-4">
                <h1 class="mb-2" style="font-size:24px;font-weight:600;">Petitions</h1>
            </div>

            <div class="bg-white shadow-sm rounded-3 p-4" style="border:1px solid #eee;">

                <div class="mb-2" style="font-weight:700;">Petitions</div>
                <div style="height:2px;background:#e9ecef;position:relative;margin-bottom:22px;">
                    <div
                        style="height:2px;width:100%;background:linear-gradient(to right, black, red);position:absolute;left:0;top:0;">
                    </div>
                </div>

                <div class="fc-petitions-list">
                    @foreach($petitions as $title)
                        <a href="{{ $demoPetitionUrl }}" class="fc-petition-row">
                            {{ $title }}
                        </a>
                    @endforeach
                </div>

                {{-- Pagination --}}
                <div class="d-flex gap-1 mt-4">
                    <a class="fc-page active" href="#">1</a>
                    <a class="fc-page" href="#">2</a>
                    <a class="fc-page" href="#">3</a>
                    <a class="fc-page" href="#">4</a>
                    <a class="fc-page" href="#">5</a>
                    <a class="fc-page" href="#">6</a>
                    <a class="fc-page" href="#">7</a>
                    <span class="fc-page disabled">…</span>
                    <a class="fc-page" href="#">»</a>
                </div>

            </div>
        </div>
    </section>
@endsection
