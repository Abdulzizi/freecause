<?php

namespace Tests\Feature;

use Database\Seeders\LanguageSeeder;
use Database\Seeders\UserLevelSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ContentPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([UserLevelSeeder::class, LanguageSeeder::class]);
    }

    public static function migratedPages(): array
    {
        return [
            'donations' => ['donations', 'Donations'],
            'how-to' => ['how-to-write-a-petition', 'How to write a petition'],
            'pdp' => ['personal-data-processing', 'Personal Data Processing'],
        ];
    }

    #[DataProvider('migratedPages')]
    public function test_migrated_page_loads_in_english_via_named_route(string $slug, string $title): void
    {
        $this->get('/en/'.$slug)
            ->assertOk()
            ->assertSee($title);
    }

    public function test_donations_content_rendered_from_db(): void
    {
        $this->get('/en/donations')
            ->assertOk()
            ->assertSee('independent site', false);
    }

    public function test_non_english_locale_falls_back_to_english_with_notice_and_canonical(): void
    {
        config(['app.url' => 'https://test.invalid', 'app.canonical_url' => 'https://www.freecause.com']);

        $response = $this->get('/de/personal-data-processing');

        $response->assertOk()
            ->assertSee(__('pages.lang_fallback_notice'))
            ->assertSee('href="https://www.freecause.com/en/personal-data-processing"', false);

        $this->assertSame(1, substr_count($response->getContent(), 'rel="canonical"'));
    }

    public function test_english_page_has_no_fallback_notice(): void
    {
        $this->get('/en/donations')
            ->assertOk()
            ->assertDontSee(__('pages.lang_fallback_notice'));
    }

    public function test_token_shaped_page_slug_returns_404(): void
    {
        $this->get('/en/'.rawurlencode('{ETHICAL_CODE_LINK}'))->assertNotFound();
    }

    public function test_unknown_slug_returns_404(): void
    {
        $this->get('/en/this-slug-does-not-exist')->assertNotFound();
    }

    public function test_translated_editorial_page_has_no_fallback_notice(): void
    {
        $this->get('/es/donations')
            ->assertOk()
            ->assertDontSee(__('pages.lang_fallback_notice'));

        $this->get('/fr/how-to-write-a-petition')
            ->assertOk()
            ->assertDontSee(__('pages.lang_fallback_notice'));
    }

    public function test_status_query_on_fixed_page_redirects_to_clean_url(): void
    {
        $this->get('/en/donations?status=200&utm_source=x')
            ->assertStatus(301)
            ->assertRedirect('/en/donations?utm_source=x');

        $this->get('/en/donations')->assertOk();
    }

    public function test_status_redirect_is_not_host_header_poisoned(): void
    {
        $response = $this->withHeader('Host', 'evil.test')->get('/en/donations?status=200');

        $response->assertStatus(301);

        $location = (string) $response->headers->get('Location');

        $this->assertStringStartsWith('/en/donations', $location);
        $this->assertStringNotContainsString('evil.test', $location);
    }

    public function test_status_query_on_petitions_is_not_redirected_and_is_noindexed(): void
    {
        $response = $this->get('/en/petitions?status=200');

        $this->assertNotSame(301, $response->getStatusCode());
        $response->assertOk()
            ->assertSee('name="robots"', false)
            ->assertSee('noindex, follow');
    }

    public function test_untranslated_legal_page_still_shows_fallback_notice(): void
    {
        $this->get('/es/personal-data-processing')
            ->assertOk()
            ->assertSee(__('pages.lang_fallback_notice'));
    }
}
