@extends('layouts.legacy')

@section('title', 'FAQs - FreeCause - Online Petition')

@php
    $faq = [
        'General Information' => [
            [
                'q' => 'Are online petitions useful?',
                'a' => '"Online" petitions can be an effective way to seek support for an issue or to get attention on a certain question. Many of the petitions that we host have collected hundreds of thousands of signatures. Those who were the recipients of these petitions have been forced to take note of the public will and to resolve the problem. However, because a petition could be effective, and because it could be created actually a change, you need to activate yourself to promote your petition. You should send email to your friends and colleagues, and thus to promote the petition in discussion groups and other public websites, where users may be interested in your same problem. Many web campaigns have changed in a positive way the life of small communities and countries. Nothing has the strength popularization as Internet, we raise the volume of your voice.'
            ],
            ['q' => 'Why you should prefer an online petition to a paper instead?', 'a' => 'First, because an online petition can be accessed in seconds by hundreds of millions of people through the internet and around the world. Just give to your friends the address FreeCause - Online Petition and from that moment your petition will be spread across the globe. From that moment you\'ll be an activist in the globalized world. Do you have th intention to make a paper petition? Do you prefer to stay on a street corner to collect signatures? At most you will be able to collect several hundred signatures and have sore legs. Secondly, you can use online marketing to get more users willing to sign. Each visitor to your petition may invite a friend to sign the petition. The great mass of people on the internet will make your voice heard around the world!'],
            ['q' => 'Can FreeCause - Online Petition be used for referendum proposals?', 'a' => 'Yes, if accompanied by documentation of the signatories. In addition, a campaign on FreeCause - Online Petition can also have an effect on governments and politicians. Many of our demands are addressed to individuals or institutions and governments. Although not legally bound, they have an impact on public opinion in an important way. We give voice to an idea, many campaigns started from FreeCause - Online Petition have moved governments.'],
            ['q' => 'How do I get more information about FreeCause - Online Petition?', 'a' => ''],
            ['q' => 'Tips on how to subscribe. For example, what it should be done if the system doesn\'t accept your email address or password you typed?', 'a' => 'To register, simply specify the name, your e-mail address and password. If your email address, for some reason, it is already in our database, the recording can not continue. In our database, your email address can appear and can be associated only with one registration. After sending the data, you will need to wait for the activation email that contains a link to click to prove that you are the true owner of the email address used. If you have a Facebook, Google or Twitter account you can access very simply by clicking the appropriate button.'],
        ],

        'For signatories of a petition' => [
            ['q' => 'FreeCause - Online Petition apparently sent me an email, but I have not received it. Why?', 'a' => ''],
            ['q' => 'Is it guaranteed the confidentiality of my personal data?', 'a' => ''],
            ['q' => 'How do I know if the petition I\'ve signed has been successful?', 'a' => ''],
            ['q' => 'How do I report when a petition violates the respect of the law for its content?', 'a' => ''],
            ['q' => 'How do I create a petition?', 'a' => ''],

            // extra rows seen in prod list
            ['q' => 'What happened to my signature? I signed a petition, but I can\'t find it in the list of signatories.', 'a' => ''],
            ['q' => 'I signed a petition. My data and my personal information will be treated confidentially?', 'a' => ''],
            ['q' => 'My name has appeared on your site or Google. How can I remove it?', 'a' => ''],
            ['q' => 'Facebook and Twitter statistics. Why Facebook users prefer to click "like" and not sign? What happened to the "like" of facebook?', 'a' => ''],
            ['q' => 'Can I hide my petition from public directories or Google? Can I make changes?', 'a' => ''],
            ['q' => 'Time zone. What time zone is used for dating the signatures?', 'a' => ''],
            ['q' => 'How can I close my account FreeCause - Online Petition? Closing the account.', 'a' => ''],
        ],

        'For promoters of petitions' => [
            ['q' => 'How do I activate my membership once I decided to start a campaign?', 'a' => ''],
            ['q' => 'Is it free the creation of a petition?', 'a' => ''],
            ['q' => 'Do I need technical knowledge to create a petition?', 'a' => ''],
            ['q' => 'Can I import a petition on your site that was opened on another site?', 'a' => ''],
            ['q' => 'Can I choose the graphical style of my petition?', 'a' => ''],
            ['q' => 'Can I add multiple petitions?', 'a' => ''],
            ['q' => 'FreeCause - Online Petition will use the information and signatures I gathered, for other purposes?', 'a' => ''],
            ['q' => 'Can my petition be put in the "Featured Petition" or in evidence on the home page of FreeCause - Online Petition?', 'a' => ''],
            ['q' => 'How can I delete a petition?', 'a' => ''],
            ['q' => 'I\'ve created a petition but I can\'t find it. Where is my petition?', 'a' => ''],
            ['q' => 'What is the best way to write a petition?', 'a' => ''],
            ['q' => 'How can I convince people to sign my petition?', 'a' => ''],
            ['q' => 'How do I put a link that points to FreeCause - Online Petition?', 'a' => ''],
            ['q' => 'Donations to FreeCause - Online Petition. How can I do a donation?', 'a' => ''],
        ],
    ];
@endphp

@section('content')
    <section class="py-5">
        <div class="container">

            <div class="mb-4">
                <h1 class="mb-2" style="font-size:24px;font-weight:600;">Faq</h1>
                <div style="font-size:14px;">
                    <a class="red" href="/{{ app()->getLocale() }}">Home</a>
                    <span class="text-muted"> / </span>
                    <span class="text-muted">Faq</span>
                </div>
            </div>

            <div class="bg-white shadow-sm rounded-3 p-4" style="border:1px solid #eee;">
                <div class="mb-2" style="font-weight:700;">FAQs</div>
                <div style="height:2px;background:#e9ecef;position:relative;margin-bottom:22px;">
                    <div style="height:2px;width:100%;background:linear-gradient(to right, black, red);position:absolute;left:0;top:0;"></div>
                </div>

                @php
                    $accId = 'faqAcc';
                    $i = 0;
                @endphp

                @foreach($faq as $sectionTitle => $items)
                    <h2 class="mb-3" style="font-size:20px;font-weight:700;">{{ $sectionTitle }}</h2>

                    <div class="accordion mb-4" id="{{ $accId }}-{{ \Illuminate\Support\Str::slug($sectionTitle) }}">
                        @foreach($items as $item)
                            @php
                                $i++;
                                $collapseId = 'faqCollapse' . $i;
                                $headingId = 'faqHeading' . $i;
                                $hasAnswer = trim($item['a'] ?? '') !== '';
                            @endphp

                            <div class="accordion-item" style="border:1px solid #e9ecef;">
                                <h2 class="accordion-header" id="{{ $headingId }}">
                                    <button class="accordion-button collapsed" type="button"
                                        data-bs-toggle="collapse" data-bs-target="#{{ $collapseId }}"
                                        aria-expanded="false" aria-controls="{{ $collapseId }}">
                                        {{ $item['q'] }}
                                    </button>
                                </h2>

                                <div id="{{ $collapseId }}"
                                    class="accordion-collapse collapse"
                                    data-bs-parent="#{{ $accId }}-{{ \Illuminate\Support\Str::slug($sectionTitle) }}"
                                    aria-labelledby="{{ $headingId }}">
                                    <div class="accordion-body">
                                        @if($hasAnswer)
                                            <p class="mb-0">{{ $item['a'] }}</p>
                                        @else
                                            <p class="mb-0 text-muted">...</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endforeach

            </div>
        </div>
    </section>
@endsection
