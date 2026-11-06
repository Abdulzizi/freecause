<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\CategoryTranslation;
use App\Models\Language;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LegacyRedirectTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $isDefault = true;
        foreach (['da', 'de', 'el', 'en', 'es', 'fr', 'it', 'nl', 'pl', 'pt', 'ro', 'ru', 'sv', 'tr'] as $code) {
            Language::create([
                'code' => $code,
                'name' => $code,
                'is_active' => 1,
                'is_default' => $code === 'en',
            ]);
            $isDefault = false;
        }

        cache()->forget('active_languages');
        cache()->forget('default_language');
    }

    public function test_localized_list_word_with_legacy_category_id_redirects_to_canonical_category(): void
    {
        for ($i = 0; $i < 13; $i++) {
            Category::create(['is_active' => 1, 'sort_order' => $i + 1]);
        }
        CategoryTranslation::create([
            'category_id' => 13,
            'locale' => 'it',
            'name' => 'Salute',
            'slug' => 'salute',
        ]);

        $this->get('/it/petizioni/categoria-salute-79')
            ->assertStatus(301)
            ->assertHeader('Location', 'http://localhost/it/petitions/category-salute-13');
    }

    public function test_localized_list_word_with_legacy_pagination_redirects_to_query(): void
    {
        $this->get('/de/petitionen/page-3')
            ->assertStatus(301)
            ->assertHeader('Location', 'http://localhost/de/petitions?page=3');
    }

    public function test_legacy_download_signatures_url_redirects_to_canonical_export(): void
    {
        $this->get('/en/download-signatures/save-the-reef/4242')
            ->assertStatus(301)
            ->assertHeader('Location', 'http://localhost/en/petition/save-the-reef/4242/download/txt');
    }

    public function test_legacy_download_signatures_without_id_is_404(): void
    {
        $this->get('/en/download-signatures/save-the-reef')
            ->assertNotFound();
    }

    public function test_bare_localized_list_word_redirects_to_canonical_list(): void
    {
        $this->get('/pt/peticoes')
            ->assertStatus(301)
            ->assertHeader('Location', 'http://localhost/pt/petitions');
    }

    public function test_canonical_list_with_legacy_pagination_redirects_to_query(): void
    {
        $this->get('/de/petitions/page-3')
            ->assertStatus(301)
            ->assertHeader('Location', 'http://localhost/de/petitions?page=3');
    }

    public function test_author_filtered_legacy_list_redirects_to_plain_list(): void
    {
        $this->get('/es/peticiones/user-42')
            ->assertStatus(301)
            ->assertHeader('Location', 'http://localhost/es/petitions');
    }

    public function test_localized_petition_word_redirects_to_canonical_detail_with_suffix(): void
    {
        $this->get('/tr/dilekce/qualcosa/123')
            ->assertStatus(301)
            ->assertHeader('Location', 'http://localhost/tr/petition/qualcosa/123');
    }

    public function test_canonical_petition_passes_through_untouched(): void
    {
        $this->get('/en/petition/qualcosa/123')
            ->assertHeaderMissing('Location');
    }

    public function test_localized_user_word_redirects_to_canonical_user(): void
    {
        $this->get('/de/benutzer/killer1/2471185')
            ->assertStatus(301)
            ->assertHeader('Location', 'http://localhost/de/user/killer1/2471185');
    }

    public function test_polish_user_word_with_encoded_path_redirects_to_canonical_user(): void
    {
        $this->get('/pl/u%C5%BCytkownik/jan/5')
            ->assertStatus(301)
            ->assertHeader('Location', 'http://localhost/pl/user/jan/5');
    }

    public function test_canonical_user_passes_through_untouched(): void
    {
        $this->get('/en/user/killer1/2471185')
            ->assertHeaderMissing('Location');
    }

    public function test_localized_search_word_redirects_to_list_with_query(): void
    {
        $this->get('/pl/szukaj/climate')
            ->assertStatus(301)
            ->assertHeader('Location', 'http://localhost/pl/petitions?q=climate');
    }

    public function test_legacy_search_page_is_preserved(): void
    {
        $this->get('/pl/szukaj/climate/page-2')
            ->assertStatus(301)
            ->assertHeader('Location', 'http://localhost/pl/petitions?q=climate&page=2');
    }

    public function test_canonical_search_word_redirects_to_list_with_query(): void
    {
        $this->get('/en/search/climate/5')
            ->assertStatus(301)
            ->assertHeader('Location', 'http://localhost/en/petitions?q=climate&page=5');
    }

    public function test_localized_faq_word_redirects_to_faqs(): void
    {
        $this->get('/de/haufig-gestellte-fragen')
            ->assertStatus(301)
            ->assertHeader('Location', 'http://localhost/de/faqs');
    }

    public function test_en_faq_word_redirects_to_faqs(): void
    {
        $this->get('/en/faq')
            ->assertStatus(301)
            ->assertHeader('Location', 'http://localhost/en/faqs');
    }

    public function test_canonical_faqs_passes_through_untouched(): void
    {
        $this->get('/en/faqs')
            ->assertHeaderMissing('Location');
    }

    public function test_news_and_localized_blog_word_redirect_to_magazine_index(): void
    {
        $this->get('/en/news')
            ->assertStatus(301)
            ->assertHeader('Location', 'http://localhost/magazine');

        $this->get('/it/notizie/perche-questa-petizione')
            ->assertStatus(301)
            ->assertHeader('Location', 'http://localhost/magazine');
    }

    public function test_topics_and_results_words_redirect_to_petitions(): void
    {
        $this->get('/en/topics')
            ->assertStatus(301)
            ->assertHeader('Location', 'http://localhost/en/petitions');

        $this->get('/it/results')
            ->assertStatus(301)
            ->assertHeader('Location', 'http://localhost/it/petitions');
    }

    public function test_recover_password_word_redirects_to_forgot_password(): void
    {
        $this->get('/en/recover-password')
            ->assertStatus(301)
            ->assertHeader('Location', 'http://localhost/en/forgot-password');
    }

    public function test_legacy_locale_literal_is_normalised_before_word_redirects(): void
    {
        $this->get('/it_IT/petizione/qualcosa/123')
            ->assertStatus(301)
            ->assertHeader('Location', 'http://localhost/it/petizione/qualcosa/123');
    }

    public function test_query_string_is_preserved_on_word_redirects(): void
    {
        $this->get('/it/petizione/qualcosa/123?utm_source=legacy&utm_id=1')
            ->assertStatus(301)
            ->assertHeader('Location', 'http://localhost/it/petition/qualcosa/123?utm_id=1&utm_source=legacy');
    }

    public function test_unknown_words_pass_through_without_redirect(): void
    {
        $this->get('/it/qualcosa-random-che-non-esiste')
            ->assertHeaderMissing('Location');
    }

    public function test_signatures_list_word_stays_intentionally_404(): void
    {
        $this->get('/it/firme/qualcosa/123')->assertStatus(404);
        $this->get('/en/signatures/qualcosa/123')->assertStatus(404);
    }

    public function test_download_signatures_redirects_to_canonical_export(): void
    {
        // No longer deferred: the legacy /download-signatures/<slug>/<id> URL
        // now maps to the canonical authenticated export route.
        $this->get('/en/download-signatures/my-cause/123')
            ->assertStatus(301)
            ->assertHeader('Location', 'http://localhost/en/petition/my-cause/123/download/txt');

        // Malformed (no slug/id pair) still 404s.
        $this->get('/en/download-signatures/123')->assertStatus(404);
    }

    public function test_feeds_referers_and_invite_friends_stay_intentionally_404(): void
    {
        $this->get('/en/feed')->assertStatus(404);
        $this->get('/en/referers/qualcosa/1')->assertStatus(404);
        $this->get('/en/invite-friends/1')->assertStatus(404);
    }

    public function test_hashed_canonical_petition_url_redirects_to_base(): void
    {
        $this->get('/en/petition/support-nkf/122285/340a6d1488c454a893e14e13ccc2abb9')
            ->assertStatus(301)
            ->assertHeader('Location', 'http://localhost/en/petition/support-nkf/122285');
    }

    public function test_hashed_localized_petition_word_redirects_to_canonical_petition(): void
    {
        $this->get('/it/petizione/salute/122285/340a6d1488c454a893e14e13ccc2abb9')
            ->assertStatus(301)
            ->assertHeader('Location', 'http://localhost/it/petition/salute/122285');
    }

    public function test_canonical_petition_without_hash_segment_passes_through(): void
    {
        $this->get('/en/petition/support-nkf/122285')
            ->assertHeaderMissing('Location');
    }
}
