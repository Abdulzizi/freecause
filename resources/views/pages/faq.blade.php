@extends('layouts.legacy')

@section('title', 'FAQs - FreeCause - Online Petition')

@php
    $faq = [
        'General Information' => [
            [
                'q' => 'Are online petitions useful?',
                'a' => '"Online" petitions can be an effective way to seek support for an issue or to get attention on a certain question. Many of the petitions that we host have collected hundreds of thousands of signatures. Those who were the recipients of these petitions have been forced to take note of the public will and to resolve the problem. However, because a petition could be effective, and because it could be created actually a change, you need to activate yourself to promote your petition. You should send email to your friends and colleagues, and thus to promote the petition in discussion groups and other public websites, where users may be interested in your same problem. Many web campaigns have changed in a positive way the life of small communities and countries. Nothing has the strength popularization as Internet, we raise the volume of your voice.'
            ],
            [
                'q' => 'Why you should prefer an online petition to a paper instead?',
                'a' => 'First, because an online petition can be accessed in seconds by hundreds of millions of people through the internet and around the world. Just give to your friends the address FreeCause - Online Petition and from that moment your petition will be spread across the globe. From that moment you\'ll be an activist in the globalized world.<br />Do you have th intention to make a paper petition? Do you prefer to stay on a street corner to collect signatures? At most you will be able to collect several hundred signatures and have sore legs.<br />Secondly, you can use online marketing to get more users willing to sign. Each visitor to your petition may invite a friend to sign the petition.<br />The great mass of people on the internet will make your voice heard around the world!'
            ],
            [
                'q' => 'Can FreeCause - Online Petition be used for referendum proposals?',
                'a' => 'Yes, if accompanied by documentation of the signatories. In addition, a campaign on FreeCause - Online Petition can also have an effect on governments and politicians. Many of our demands are addressed to individuals or institutions and governments. Although not legally bound, they have an impact on public opinion in an important way.<br /><br />We give voice to an idea, many campaigns started from FreeCause - Online Petition have moved governments.'
            ],
            [
                'q' => 'How do I get more information about FreeCause - Online Petition?',
                'a' => 'To learn more, go to our website and visit the Information page and our <a href="https://www.test.freecause.com/ethical-code">Ethical Code</a>.'
            ],
            [
                'q' => 'Tips on how to subscribe. For example, what it should be done if the system doesn\'t accept your email address or password you typed?',
                'a' => 'To register, simply specify the name, your e-mail address and password. If your email address, for some reason, it is already in our database, the recording can not continue. In our database, your email address can appear and can be associated only with one registration. After sending the data, you will need to wait for the activation email that contains a link to click to prove that you are the true owner of the email address used. If you have a Facebook, Google or Twitter account you can access very simply by clicking the appropriate button.'
            ],
        ],

        'For signatories of a petition' => [
            [
                'q' => 'FreeCause - Online Petition apparently sent me an email, but I have not received it. Why?',
                'a' => 'It may happen that your mail provider has marked an email as Spam. We suggest you change the settings or your spam filter and insert FreeCause - Online Petition into the list of trusted addresses. We do not send emails that are not required and this is a guarantee for you, because we only contact you to communicate information of particular relevance.'
            ],
            [
                'q' => 'Is it guaranteed the confidentiality of my personal data?',
                'a' => 'FreeCause - Online Petition will treat your personal data very seriously. After signing a petition, you will receive an email confirmation stating that your signature has been recorded. FreeCause - Online Petition will not use your information and your email for other petitions or for other purposes, unless you authorize us to do it. In any case, we pledge to not disclose to third parties your personal information. The only person who will be aware that you have signed the petition is the host or the person who made the petition that you have decided to sign. Read also our <a href="https://www.test.freecause.com/ethical-code">Ethical Code</a>.'
            ],
            [
                'q' => 'How do I know if the petition I\'ve signed has been successful?',
                'a' => 'There are many ways to follow the progress and know if the petition you\'ve signed has been successful. A good way is to visit the site where the petition is online regularly. There you will certainly find news and updates.'
            ],
            [
                'q' => 'How do I report when a petition violates the respect of the law for its content?',
                'a' => 'FreeCause - Online Petition accept any complaints very seriously, and if is found that a petition contains content that violates the law, please contact us immediately. Before proceeding, you must ensure that the content is really illegal and that simply does not respond to our ideas about the subject matter of the petition itself. Our business ethics requires us also accept that there are different ideas from those proposed by the petition, in that case, please express your opposition to creating a counter-petition or commenting on the special space in the same comments.'
            ],
            [
                'q' => 'How do I create a petition?',
                'a' => 'If you just signed a campaign to collect signatures and would like to create your own, we are happy to inform you that we offer the most advanced and flexible tools to create petitions on the web. Enter here<br /><br /><a href="{CREATE_PETITION_URL}">to create a petition</a>.'
            ],
            [
                'q' => 'What happened to my signature? I signed a petition, but I can\'t find it in the list of signatories.',
                'a' => 'We divide the list of signatories in more than one page, even if in the pages after the first you can\'t find the signature, contact the staff indicating the petition and email address associated with your account. We will be happy to help you.'
            ],
            [
                'q' => 'I signed a petition. My data and my personal information will be treated confidentially?',
                'a' => 'Yes. We have our safety and privacy code. Please read our privacy code.'
            ],
            [
                'q' => 'My name has appeared on your site or Google. How can I remove it?',
                'a' => 'Please contact the Staff of FreeCause - Online Petition and ask them to remove your name from the petition. If someone has entered your name in a list or forum without your permission, please contact the sponsor of the petition and ask him to immediately remove you. FreeCause - Online Petition can\'t absolutely exercise any control over the use of your name by third parties, however we will activate immediately in case of we recieve complaints. Please read the Terms of Use of FreeCause - Online Petition. When your name has been removed of FreeCause - Online Petition,  please visit the list with the signatures to verify that indeed has been done. Although has been removed, however, Google will continue to index it. In fact, the process of updating the new data will require a few days. Your name will appear on Google for a few days until the search engine will not have completed the upgrade.'
            ],
            [
                'q' => 'Facebook and Twitter statistics. Why Facebook users prefer to click "like" and not sign? What happened to the "like" of facebook?',
                'a' => 'Often a petition recieve more likes than signatures. This may happen because some people prefer to just click on "I like" and not to sign, for privacy or personal reasons. Some who share the objectives of a petition, don\'t want to give their information and so prefer not to sign but only click on "I like it." The fact that there are more "like" does not mean you\'ve lost signatures. It just means that you have a broader support. But remember  that if you change the URL of your petition you will also lose all those users who have expressed an interest in your idea. However, you can have others or redirect the old URL to the new.'
            ],
            [
                'q' => 'Can I hide my petition from public directories or Google? Can I make changes?',
                'a' => 'No, you can\'t hide the petition, it will be indexed by Google in the days following the creation. Also you can make changes only if no one, apart of the promoter, has not yet signed.'
            ],
            [
                'q' => 'Time zone. What time zone is used for dating the signatures?',
                'a' => 'FreeCause - Online Petition uses the Time Zone UTC-5'
            ],
            [
                'q' => 'How can I close my account FreeCause - Online Petition? Closing the account.',
                'a' => 'You can cancel your account at any time, once you have the access, go to your profile, there you will find a button to delete your account and all data accompanying it from our database.<br />If you have questions or concerns please contact us, maybe we can help you.<br />Our community is based on trust between users and those who run the service for users.'
            ],
        ],

        'For promoters of petitions' => [
            [
                'q' => 'How do I activate my membership once I decided to start a campaign?',
                'a' => 'When you subscribe to FreeCause - Online Petition, we send a message to your email address to confirm your account.<br />If you have not received the activation email, you can request it by clicking the link on the login screen, it is <a href="{RECOVER_URL}">questo</a>. If you have problems with the activation process, contact us.'
            ],
            [
                'q' => 'Is it free the creation of a petition?',
                'a' => 'Yes, all our petitions are absolutely free. We offer the most flexible and advanced tools on the web, and we support through advertising and voluntary donations. You can also create a petition requesting that a paid service becomes free.'
            ],
            [
                'q' => 'Do I need technical knowledge to create a petition?',
                'a' => 'You do not need any technical knowledge to create an online petition.<br />All is already provided by FreeCause - Online Petition, you will only express your ideas.'
            ],
            [
                'q' => 'Can I import a petition on your site that was opened on another site?',
                'a' => 'Yes. Simply integrate with our existing database, and we will help transfer the petition on our website. Contact us to do this, we will help you on it.'
            ],
            [
                'q' => 'Can I choose the graphical style of my petition?',
                'a' => 'We are implementing a service that will allow you to customize it. It will be possible shortly to customize the new petitions or those already created.'
            ],
            [
                'q' => 'Can I add multiple petitions?',
                'a' => 'Yes, of course, personal freedom has no limits.'
            ],
            [
                'q' => 'FreeCause - Online Petition will use the information and signatures I gathered, for other purposes?',
                'a' => 'FreeCause - Online Petition treats privacy very seriously. The list of signatures will not be used under any circumstances, except to send the confirmation email to the signatory of a petition or if  the petitioner authorize us to contact others for petitions or other related events. Only in this case we can contact the complainant. In no cases we shall sell, trade or share signatory information with third parties. To learn more about privacy read also our code of ethics.<br /><br />Enter to read our <a href="https://www.test.freecause.com/ethical-code">Ethical Code</a>.'
            ],
            [
                'q' => 'Can my petition be put in the "Featured Petition" or in evidence on the home page of FreeCause - Online Petition?',
                'a' => 'The petition highlighted in the window is more visible and this increases the number of signatures received. If you want your petition to be placed in the window, <a href="{CONTACTS_URL}">contattaci</a>.'
            ],
            [
                'q' => 'How can I delete a petition?',
                'a' => 'To delete a petition you can use the Delete link on the page of the petition. You must be logged in to perform this operation.'
            ],
            [
                'q' => 'I\'ve created a petition but I can\'t find it. Where is my petition?',
                'a' => 'Use the Search function or enter the page of your profile, inside the profile you will find your list of petitions that you\'ve opened.'
            ],
            [
                'q' => 'What is the best way to write a petition?',
                'a' => 'We recommend that you read our notes on how to write a petition. This page contains several suggestions for writing a petition.<br /><br />You will find the indications here: {HOW_TO_CREATE_LINK}.'
            ],
            [
                'q' => 'How can I convince people to sign my petition?',
                'a' => 'Spread the petition by telling your friends. Telling to others is the primary weapon.<br /><br />
    We also recommend you to increase the opportunities for discussion forums and group discussions.<br /><br />
    In addition, send email to a considerable number of people you know are interested in petitions, or to people you believe that at least are interested. (But not send spam!).<br /><br />
    We also invite you to use our bookmarking service that is on top of each petition.<br /><br />
    Networking sites like Facebook and Twitter are also a good window for your petition.<br /><br />
    Petitions in social networks run very quickly between people who, as you, are interested in a topic, such as in groups.<br /><br />
    To promote your petition is also a good practice to place links on other websites. Please note also that as many web sites will contain the link to your petition more web search engines will recognize it as an important page and more it will be visited. Therefore we recommend to put the link on as many sites as possible. A good way to spread it can also be to insert the link in databases as Wikipedia.org.<br /><br />
    You should be looking for sites that have similar aspects to the subject of your petition. You will agree once you\'ve identified them to send emails to their webmaster and probabily they will insert the link of your petition. You can also click on the Bookmark link found at the top of each petition and refer to other social networks, where you can promote your petition.'
            ],
            [
                'q' => 'How do I put a link that points to FreeCause - Online Petition?',
                'a' => 'As with any other links, copy the address bar of your browser and paste it wherever you want.<br /><br />Or grab the embedding code on every page you will find petitions and paste it into the html code of your site.'
            ],
            [
                'q' => 'Donations to FreeCause - Online Petition. How can I do a donation?',
                'a' => 'Go to the donations page link at the top menu, there you will find all necessary information in order to send a donation.<br /><br />Pages {DONATIONS_LINK}<br /><br />FreeCause - Online Petition it is an independent site that holds only thanks to donations from users.'
            ],
        ],
    ];

    // Replace legacy placeholders with your Laravel routes/URLs (safe for now).
    $replace = [
        '{CREATE_PETITION_URL}' => url('/' . app()->getLocale() . '/petition/create'),
        '{CONTACTS_URL}' => url('/' . app()->getLocale() . '/contacts'),
        '{RECOVER_URL}' => url('/' . app()->getLocale() . '/login'),
        '{HOW_TO_CREATE_LINK}' => url('/' . app()->getLocale() . '/help'),
        '{DONATIONS_LINK}' => url('/' . app()->getLocale() . '/donate'),
    ];

    foreach ($faq as $section => $items) {
        foreach ($items as $idx => $it) {
            $faq[$section][$idx]['a'] = str_replace(array_keys($replace), array_values($replace), $it['a'] ?? '');
        }
    }
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
                    <div
                        style="height:2px;width:100%;background:linear-gradient(to right, black, red);position:absolute;left:0;top:0;">
                    </div>
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
                                $hasAnswer = trim(strip_tags($item['a'] ?? '')) !== '';
                            @endphp

                            <div class="accordion-item" style="border:1px solid #e9ecef;">
                                <h2 class="accordion-header" id="{{ $headingId }}">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#{{ $collapseId }}" aria-expanded="false" aria-controls="{{ $collapseId }}">
                                        {{ $item['q'] }}
                                    </button>
                                </h2>

                                <div id="{{ $collapseId }}" class="accordion-collapse collapse"
                                    data-bs-parent="#{{ $accId }}-{{ \Illuminate\Support\Str::slug($sectionTitle) }}"
                                    aria-labelledby="{{ $headingId }}">
                                    <div class="accordion-body">
                                        @if($hasAnswer)
                                                {!! $item['a'] !!}
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
