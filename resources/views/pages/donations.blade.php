@extends('layouts.legacy')

@section('title', 'Donations - FreeCause')
@section('body_class', 'donations')

@section('content')
    <section class="bg-[#fbfbfb]! py-[30px]!">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <h1 class="text-xl! mb-[5px]!">Donations</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb m-0 p-0">
                            <li class="breadcrumb-item">
                                <a href="{{ lroute('home') }}">Home</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Donations</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </section>

    <section class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="mb-4">
                                <h2 class="relative! capitalize! text-lg! pb-2.5! mb-5! font-semibold! mt-[30px]! after:absolute! after:content-['']! after:h-px! after:bg-[linear-gradient(to_right,#000_30%,#ce2020_30%)]! after:w-full! after:left-0! after:bottom-0!">Donations</h2>

                                <div>
                                    <p><strong class="text-muted">ONLINE DONATIONS</strong></p>

                                    <h2 class="relative! capitalize! text-lg! pb-2.5! mb-5! font-semibold! mt-[30px]! after:absolute! after:content-['']! after:h-px! after:bg-[linear-gradient(to_right,#000_30%,#ce2020_30%)]! after:w-full! after:left-0! after:bottom-0!">FreeCause - Online Petition is an independent site. Please help support it with a donation through one of the methods described on this page.</h2>

                                    <p><strong>FreeCause - Online Petition</strong> does not accept direct sponsorship of any campaign, nor does it sell the names or emails of those who sign the petitions. It is and will always remain free.</p>

                                    <p>We don't want to subject ourselves to the dictates of the free market: ideals are priceless and cannot be bought or sold.<br />For this reason we ask for your help.</p>

                                    <p>Many campaigns started here at <strong>FreeCause - Online Petition</strong> have become victorious and have changed the lives of small communities or even entire countries. There is nothing like the grassroots awareness that the Internet can achieve, touching consciences and moving people to action.</p>

                                    <p>Send us your contribution today and join us in continuing to offer people around the world the possibility, the tools and the information necessary to create change within their communities.</p>

                                    <p>It is only through voluntary support from contributors like you that we can continue to function and expand the scope of our projects.</p>

                                    <p>All donations we receive are used exclusively for the maintenance of the <strong>FreeCause - Online Petition</strong> website across the world and for the promotion of individual petitions.</p>

                                    <p>By following our code of ethics we have become an important partner to non-profit organizations, non-governmental organizations, and regular citizens in many countries.<br />We are the place where non-violent resistance begins.</p>

                                    <h2 class="relative! capitalize! text-lg! pb-2.5! mb-5! font-semibold! mt-[30px]! after:absolute! after:content-['']! after:h-px! after:bg-[linear-gradient(to_right,#000_30%,#ce2020_30%)]! after:w-full! after:left-0! after:bottom-0!">Even a small donation helps. Please support our cause</h2>

                                    <h2 class="relative! capitalize! text-lg! pb-2.5! mb-5! font-semibold! mt-[30px]! after:absolute! after:content-['']! after:h-px! after:bg-[linear-gradient(to_right,#000_30%,#ce2020_30%)]! after:w-full! after:left-0! after:bottom-0!">You can make a donation to FreeCause - Online Petition through:</h2>

                                    <p><strong class="text-muted">ONLINE DONATIONS</strong></p>

                                    <p class="text-muted">The amount can be changed as you desire and feel appropriate.</p>

                                    <p><strong class="text-muted">OFFLINE DONATIONS</strong></p>

                                    <p class="text-muted">You can also send us a check or make a payment via bank transfer.<br />Write to us at <a href="mailto:donation@internetpetition.org">donation@internetpetition.org</a> and we will provide the necessary details.<br />We can provide, on request, documentation of your donation for tax purposes.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
