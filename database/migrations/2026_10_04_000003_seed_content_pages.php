<?php

use App\Models\Page;
use App\Models\PageTranslation;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const LOCALE = 'en';

    private function pages(): array
    {
        return [
            'donations' => [
                'title' => 'Donations',
                'content' => <<<'HTML'
<p><strong>ONLINE DONATIONS</strong></p>
<h2>FreeCause - Online Petition is an independent site. Please help support it with a donation through one of the methods described on this page.</h2>
<p><strong>FreeCause - Online Petition</strong> does not accept direct sponsorship of any campaign, nor does it sell the names or emails of those who sign the petitions. It is and will always remain free.</p>
<p>We don't want to subject ourselves to the dictates of the free market: ideals are priceless and cannot be bought or sold.<br>For this reason we ask for your help.</p>
<p>Many campaigns started here at <strong>FreeCause - Online Petition</strong> have become victorious and have changed the lives of small communities or even entire countries. There is nothing like the grassroots awareness that the Internet can achieve, touching consciences and moving people to action.</p>
<p>Send us your contribution today and join us in continuing to offer people around the world the possibility, the tools and the information necessary to create change within their communities.</p>
<p>It is only through voluntary support from contributors like you that we can continue to function and expand the scope of our projects.</p>
<p>All donations we receive are used exclusively for the maintenance of the <strong>FreeCause - Online Petition</strong> website across the world and for the promotion of individual petitions.</p>
<p>By following our code of ethics we have become an important partner to non-profit organizations, non-governmental organizations, and regular citizens in many countries.<br>We are the place where non-violent resistance begins.</p>
<h2>Even a small donation helps. Please support our cause</h2>
<h2>You can make a donation to FreeCause - Online Petition through:</h2>
<p><strong>ONLINE DONATIONS</strong></p>
<p>The amount can be changed as you desire and feel appropriate.</p>
<p><strong>OFFLINE DONATIONS</strong></p>
<p>You can also send us a check or make a payment via bank transfer.<br>Write to us at <a href="mailto:hello@freecause.com">hello@freecause.com</a> and we will provide the necessary details.<br>We can provide, on request, documentation of your donation for tax purposes.</p>
HTML,
            ],
            'how-to-write-a-petition' => [
                'title' => 'How to write a petition',
                'content' => <<<'HTML'
<h2>Introduction</h2>
<p>Each of us, those who for one reason or another, feel compelled to protest against something and want to act. Some want their politicians to resign from their role, while others want to express their support for a politician, and some want that their favorite TV program would be again on air and still others want to see their favorite band's music in their hometown.</p>
<p>Many of us also believe in something and have values. <em>Online Petition</em> takes care to ensure that these values are not violated and the rights doesn't be violated.<br>The nature, economics and politics and human rights are at the center of our attention.</p>
<p>Write a petition is not as difficult as you might think.</p>
<p>Many activists have won their battle with <em>Online Petition</em>, that is, using our platform, which is simple and powerful. It's hard to say exactly what makes that a petition is signed by many people and reach the result. In some cases it is just luck. In other it's time. However, it is possible to draw some general principles and guidelines for writing a good petition.</p>
<h2>Guidelines for writing a good petition</h2>
<p>Here are some of the best tips from the team of <em>Online Petition</em>:</p>
<ul>
<li><p>Write your petition clearly and concisely. Many of the potential of your petition's signatories often do not have much time at their disposal. It is important that in the introductory part of the petition will be shown in a concise manner the main objective. In the first paragraph you must specify the objectives that the petition aims to achieve.</p></li>
<li><p>Spell Check<br>On many sites you are not worried at all to do spell check of the petition was posted online. If you want that people take seriously your petition, you have to spend a bit of your time to ensure that the form is correctly edited and especially proposed to the public in a serious and professional way.</p></li>
<li><p>Use "Internet" to diffuse it.<br>Advertise and promote your petition using email and social networking sites like Facebook and Twitter. You will be surprised at the ability to disseminate and publicize of the social networks. Also ask to all your contacts to tell about your petition to their friends. This form of advertising called "viral marketing" is the most effective way to advertise a topic on the web.</p></li>
<li><p>Try to be respectful of others!<br>It is important that you express clearly the objectives in the text of the petition. Try to do it but in a way that respects the ideas of others. Many potential signatories, while supporting the objectives are often annoyed by petitions that are offensive in their content.</p></li>
<li><p>Specify your objectives<br>Indicate precisely what are the objectives of your petition and for what purpose you will use the petition. Indicate to whom is directed the petition.</p></li>
<li><p>Offline Promotion of a petition<br>The fact that you have created an online petition does not mean you just promote it only on web sites. You can also promote your petition in your daily life. There are many ways to promote it at home, at work, and in many other places where you can collect more signatures. For example, through posters in the libraries of your city, or even doing promotion at work and making people aware that you have created a promotion on a certain topic.</p></li>
</ul>
HTML,
            ],
            'personal-data-processing' => [
                'title' => 'Personal Data Processing',
                'content' => <<<'HTML'
<h2>Consent to the processing of personal data</h2>
<p>Authorization for communication and/or transfer of data to partners and third parties operating in the fields of services, editorial, energy, telephony, tourism, communication, entertainment, financial, insurance, automotive, large-scale consumption, humanitarian and charitable organizations Advertising material and other promotional and commercial purposes by means of mail or telephone and/or automated call or call communication systems without the intervention of an operator and/or electronic communications such as e-mail.</p>
HTML,
            ],
        ];
    }

    public function up(): void
    {
        foreach ($this->pages() as $slug => $data) {
            $exists = PageTranslation::query()
                ->where('locale', self::LOCALE)
                ->where('slug', $slug)
                ->exists();

            if ($exists) {
                continue;
            }

            DB::transaction(function () use ($slug, $data) {
                $page = Page::create();

                PageTranslation::create([
                    'page_id' => $page->id,
                    'locale' => self::LOCALE,
                    'title' => $data['title'],
                    'slug' => $slug,
                    'content' => sanitizeAdminHtml($data['content']),
                    'published' => true,
                ]);
            });
        }
    }

    public function down(): void
    {
        foreach (array_keys($this->pages()) as $slug) {
            $translation = PageTranslation::query()
                ->where('locale', self::LOCALE)
                ->where('slug', $slug)
                ->first();

            if (! $translation) {
                continue;
            }

            $pageId = $translation->page_id;

            PageTranslation::query()->where('page_id', $pageId)->delete();
            Page::query()->whereKey($pageId)->delete();
        }
    }
};
