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
        $response = $this->get('/de/personal-data-processing');

        $response->assertOk()
            ->assertSee(__('pages.lang_fallback_notice'))
            ->assertSee('rel="canonical"', false)
            ->assertSee(url('en/personal-data-processing'), false);
    }

    public function test_english_page_has_no_fallback_notice(): void
    {
        $this->get('/en/donations')
            ->assertOk()
            ->assertDontSee(__('pages.lang_fallback_notice'));
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

    public function test_untranslated_legal_page_still_shows_fallback_notice(): void
    {
        $this->get('/es/personal-data-processing')
            ->assertOk()
            ->assertSee(__('pages.lang_fallback_notice'));
    }
}
