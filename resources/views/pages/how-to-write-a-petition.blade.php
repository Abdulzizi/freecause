@extends('layouts.legacy')

@section('title', 'How to write a petition - FreeCause')
@section('body_class', 'how-to-write-a-petition')

@section('content')
    <section class="breadcrumb-section">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <h2 class="page-titles">How to write a petition</h2>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb m-0 p-0">
                            <li class="breadcrumb-item">
                                <a href="{{ lroute('home') }}">Home</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">How to write a petition</li>
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
                                <h1 class="headings">How to write a petition</h1>

                                <div class="privacy-inner">
                                    <h2>Introduction</h2>

                                    <p>Each of us, those who for one reason or another, feel compelled to protest against something and want to act. Some want their politicians to resign from their role, while others want to express their support for a politician, and some want that their favorite TV program would be again on air and still others want to see their favorite band's music in their hometown.</p>

                                    <p>Many of us also believe in something and have values. <em>Online Petition</em> takes care to ensure that these values are not violated and the rights doesn't be violated.<br />
                                    The nature, economics and politics and human rights are at the center of our attention.</p>

                                    <p>Write a petition is not as difficult as you might think.</p>

                                    <p>Many activists have won their battle with <em>Online Petition</em>, that is, using our platform, which is simple and powerful. It's hard to say exactly what makes that a petition is signed by many people and reach the result. In some cases it is just luck. In other it's time. However, it is possible to draw some general principles and guidelines for writing a good petition.</p>

                                    <h2>Guidelines for writing a good petition</h2>

                                    <p>Here are some of the best tips from the team of <em>Online Petition</em>:</p>

                                    <ul class="text-list">
                                        <li>
                                            <p>Write your petition clearly and concisely. Many of the potential of your petition's signatories often do not have much time at their disposal. It is important that in the introductory part of the petition will be shown in a concise manner the main objective. In the first paragraph you must specify the objectives that the petition aims to achieve.</p>
                                        </li>

                                        <li>
                                            <p>Spell Check<br />
                                            On many sites you are not worried at all to do spell check of the petition was posted online. If you want that people take seriously your petition, you have to spend a bit of your time to ensure that the form is correctly edited and especially proposed to the public in a serious and professional way.</p>
                                        </li>

                                        <li>
                                            <p>Use "Internet" to diffuse it.<br />
                                            Advertise and promote your petition using email and social networking sites like Facebook and Twitter. You will be surprised at the ability to disseminate and publicize of the social networks. Also ask to all your contacts to tell about your petition to their friends. This form of advertising called "viral marketing" is the most effective way to advertise a topic on the web.</p>
                                        </li>

                                        <li>
                                            <p>Try to be respectful of others!<br />
                                            It is important that you express clearly the objectives in the text of the petition. Try to do it but in a way that respects the ideas of others. Many potential signatories, while supporting the objectives are often annoyed by petitions that are offensive in their content.</p>
                                        </li>

                                        <li>
                                            <p>Specify your objectives<br />
                                            Indicate precisely what are the objectives of your petition and for what purpose you will use the petition. Indicate to whom is directed the petition.</p>
                                        </li>

                                        <li>
                                            <p>Offline Promotion of a petition<br />
                                            The fact that you have created an online petition does not mean you just promote it only on web sites. You can also promote your petition in your daily life. There are many ways to promote it at home, at work, and in many other places where you can collect more signatures. For example, through posters in the libraries of your city, or even doing promotion at work and making people aware that you have created a promotion on a certain topic.</p>
                                        </li>
                                    </ul>

                                    <p>
                                        <strong>
                                            <a class="btn btn-primary" href="{{ lroute('petition.create') }}">Create now your campaign to collect signatures</a>
                                        </strong>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection