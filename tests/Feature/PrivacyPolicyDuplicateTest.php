<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\PageTranslation;
use Database\Seeders\LanguageSeeder;
use Database\Seeders\UserLevelSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PrivacyPolicyDuplicateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([LanguageSeeder::class, UserLevelSeeder::class]);
    }

    private function seedDuplicate(): void
    {
        $page = Page::create();
        PageTranslation::create(['page_id' => $page->id, 'locale' => 'en', 'slug' => 'privacy-policy-12', 'title' => 'Privacy', 'content' => '<p>x</p>', 'published' => true]);
    }

    private function runMigration(): void
    {
        (require database_path('migrations/2026_11_07_120000_unpublish_duplicate_privacy_policy_12.php'))->up();
    }

    public function test_migration_unpublishes_the_duplicate(): void
    {
        $this->seedDuplicate();

        $this->runMigration();

        $this->assertFalse(PageTranslation::where('slug', 'privacy-policy-12')->where('published', true)->exists());
        $this->assertTrue(PageTranslation::where('slug', 'privacy-policy-12')->exists());
    }

    public function test_duplicate_url_redirects_to_privacy_policy(): void
    {
        $this->seedDuplicate();

        $this->get('/en/privacy-policy-12')->assertStatus(301)->assertRedirect('/en/privacy-policy');
    }

    public function test_pages_sitemap_omits_the_duplicate_after_migration(): void
    {
        $this->seedDuplicate();
        $this->get('/sitemap-pages.xml')->assertSee('privacy-policy-12');

        $this->runMigration();

        $this->get('/sitemap-pages.xml')->assertOk()->assertDontSee('privacy-policy-12');
    }
}
