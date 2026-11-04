<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\PageTranslation;
use App\Models\Petition;
use App\Models\PetitionTranslation;
use App\Support\CacheVersion;
use Database\Seeders\LanguageSeeder;
use Database\Seeders\UserLevelSeeder;
use Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SitemapTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([LanguageSeeder::class, UserLevelSeeder::class]);
    }

    public function test_sitemap_is_publicly_cacheable_without_session_cookie(): void
    {
        $response = $this->get('/sitemap.xml');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/xml; charset=UTF-8');
        $response->assertHeader('Cache-Control', 'max-age=300, public, s-maxage=86400, stale-while-revalidate=3600');
        $this->assertCount(0, $response->headers->getCookies());
        $response->assertSee('<sitemapindex', false);
    }

    public function test_sitemap_and_robots_bypass_maintenance_mode(): void
    {
        $except = app(PreventRequestsDuringMaintenance::class)->getExcludedPaths();

        $this->assertContains('sitemap.xml', $except);
        $this->assertContains('sitemap-*.xml', $except);
        $this->assertContains('robots.txt', $except);
    }

    private function petitionFixture(): Petition
    {
        $petition = Petition::factory()->create(['status' => 'published', 'is_active' => true]);

        PetitionTranslation::create([
            'petition_id' => $petition->id,
            'locale' => 'en',
            'title' => 'Petition EN',
            'slug' => 'p-en',
            'description' => '<p>Body</p>',
        ]);

        PetitionTranslation::create([
            'petition_id' => $petition->id,
            'locale' => 'es',
            'title' => 'Petition ES',
            'slug' => 'p-es',
            'description' => '<p>Cuerpo</p>',
        ]);

        $draft = Petition::factory()->create(['status' => 'draft', 'is_active' => true]);
        PetitionTranslation::create([
            'petition_id' => $draft->id,
            'locale' => 'en',
            'title' => 'Draft',
            'slug' => 'p-draft',
            'description' => '<p>Body</p>',
        ]);

        $inactive = Petition::factory()->create(['status' => 'published', 'is_active' => false]);
        PetitionTranslation::create([
            'petition_id' => $inactive->id,
            'locale' => 'en',
            'title' => 'Inactive',
            'slug' => 'p-inactive',
            'description' => '<p>Body</p>',
        ]);

        return $petition;
    }

    public function test_petition_sitemap_has_one_url_per_petition_with_locale_alternates(): void
    {
        $petition = $this->petitionFixture();

        $content = $this->get('/sitemap-petitions-1.xml')->assertOk()->getContent();

        $this->assertSame(1, substr_count($content, '<url>'));
        $this->assertSame(1, substr_count($content, '<loc>'));
        $this->assertStringContainsString('<loc>'.canonical_url('en/petition/p-en/'.$petition->id).'</loc>', $content);
        $this->assertStringContainsString('hreflang="en"', $content);
        $this->assertStringContainsString('hreflang="es"', $content);
        $this->assertStringContainsString('hreflang="x-default"', $content);
        $this->assertStringNotContainsString('p-draft', $content);
        $this->assertStringNotContainsString('p-inactive', $content);
    }

    public function test_petition_sitemap_falls_back_to_first_active_locale_without_default_translation(): void
    {
        $petition = Petition::factory()->create(['status' => 'published', 'is_active' => true]);

        PetitionTranslation::create([
            'petition_id' => $petition->id,
            'locale' => 'es',
            'title' => 'Solo ES',
            'slug' => 'p-es-only',
            'description' => '<p>Cuerpo</p>',
        ]);

        $content = $this->get('/sitemap-petitions-1.xml')->assertOk()->getContent();

        $this->assertStringContainsString('<loc>'.canonical_url('es/petition/p-es-only/'.$petition->id).'</loc>', $content);
        $this->assertStringNotContainsString('hreflang="x-default"', $content);
    }

    public function test_sitemap_index_lists_one_shard_per_petition_page(): void
    {
        config(['sitemap.petitions_per_file' => 1]);

        $this->petitionFixture();

        $second = Petition::factory()->create(['status' => 'published', 'is_active' => true]);
        PetitionTranslation::create([
            'petition_id' => $second->id,
            'locale' => 'en',
            'title' => 'Second',
            'slug' => 'p-second',
            'description' => '<p>Body</p>',
        ]);

        $content = $this->get('/sitemap.xml')->assertOk()->getContent();

        $this->assertSame(2, substr_count($content, 'sitemap-petitions-'));
    }

    public function test_petition_sitemap_lastmod_uses_latest_petition_or_translation_date(): void
    {
        $petition = $this->petitionFixture();

        DB::table('petitions')->where('id', $petition->id)->update(['updated_at' => '2026-01-01 00:00:00']);
        DB::table('petition_translations')->where('petition_id', $petition->id)->where('locale', 'en')->update(['updated_at' => '2026-02-01 00:00:00']);
        DB::table('petition_translations')->where('petition_id', $petition->id)->where('locale', 'es')->update(['updated_at' => '2026-03-05 00:00:00']);

        $content = $this->get('/sitemap-petitions-1.xml')->assertOk()->getContent();

        $this->assertSame(1, substr_count($content, '<url>'));
        preg_match('#<lastmod>([^<]+)</lastmod>#', $content, $matches);
        $this->assertSame('2026-03-05', $matches[1] ?? null);
    }

    public function test_petition_sitemap_page_two_uses_the_next_offset(): void
    {
        config(['sitemap.petitions_per_file' => 1]);

        $this->petitionFixture();

        $second = Petition::factory()->create(['status' => 'published', 'is_active' => true]);
        PetitionTranslation::create([
            'petition_id' => $second->id,
            'locale' => 'en',
            'title' => 'Second',
            'slug' => 'p-second',
            'description' => '<p>Body</p>',
        ]);

        $pageOne = $this->get('/sitemap-petitions-1.xml')->assertOk()->getContent();
        $pageTwo = $this->get('/sitemap-petitions-2.xml')->assertOk()->getContent();

        $this->assertStringContainsString('p-en', $pageOne);
        $this->assertStringNotContainsString('p-second', $pageOne);
        $this->assertStringContainsString('p-second', $pageTwo);
        $this->assertStringNotContainsString('p-en', $pageTwo);
        $this->get('/sitemap-petitions-3.xml')->assertNotFound();
    }

    public function test_petition_sitemap_page_past_the_last_returns_404_without_caching(): void
    {
        config(['sitemap.petitions_per_file' => 1]);

        $this->petitionFixture();

        $this->get('/sitemap-petitions-1.xml')->assertOk();
        $response = $this->get('/sitemap-petitions-2.xml')->assertNotFound();

        $this->assertCount(0, $response->headers->getCookies());
        $response->assertHeader('Cache-Control', 'max-age=300, public, s-maxage=300');
        $this->assertFalse(Cache::has('sitemap:'.CacheVersion::get('sitemap').':petitions.v2.2'));
    }

    public function test_petition_sitemap_page_number_too_large_for_int_is_not_a_server_error(): void
    {
        // Falls through to the locale redirect like any other unknown path, instead of an int overflow 500.
        $this->get('/sitemap-petitions-99999999999999999999.xml')->assertRedirect('/en');
    }

    public function test_petition_sitemap_page_one_is_ok_without_petitions(): void
    {
        $this->get('/sitemap-petitions-1.xml')->assertOk();
    }

    public function test_petition_sitemap_page_zero_returns_empty_sitemap(): void
    {
        $this->petitionFixture();

        $content = $this->get('/sitemap-petitions-0.xml')->assertOk()->getContent();

        $this->assertStringContainsString('<urlset', $content);
        $this->assertStringNotContainsString('p-en', $content);
    }

    public function test_pages_sitemap_lists_static_pages_once_even_with_cms_rows(): void
    {
        // Migrations already seed published faqs/contacts CMS rows.
        $this->assertTrue(PageTranslation::where('locale', 'en')->where('slug', 'faqs')->where('published', true)->exists());
        $about = Page::create();
        PageTranslation::create(['page_id' => $about->id, 'locale' => 'en', 'slug' => 'about-us', 'title' => 'About', 'content' => '<p>x</p>', 'published' => true]);

        $content = $this->get('/sitemap-pages.xml')->assertOk()->getContent();

        $this->assertSame(1, substr_count($content, '<loc>'.canonical_url('en/faqs').'</loc>'));
        $this->assertSame(1, substr_count($content, '<loc>'.canonical_url('en/about-us').'</loc>'));
    }
}
