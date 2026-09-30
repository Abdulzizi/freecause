<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Petition;
use App\Models\PetitionTranslation;
use App\Support\CacheVersion;
use Database\Seeders\LanguageSeeder;
use Database\Seeders\UserLevelSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CacheInvalidationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([UserLevelSeeder::class, LanguageSeeder::class]);
    }

    public function test_bump_changes_version(): void
    {
        $before = CacheVersion::get('test-group');

        $this->assertSame($before, CacheVersion::get('test-group'));

        CacheVersion::bump('test-group');

        $after = CacheVersion::get('test-group');

        $this->assertNotSame($before, $after);
    }

    public function test_petition_save_bumps_home_list_and_sitemap_versions(): void
    {
        $homeBefore = CacheVersion::get('home');
        $listBefore = CacheVersion::get('list');
        $sitemapBefore = CacheVersion::get('sitemap');

        Petition::factory()->create();

        $this->assertNotSame($homeBefore, CacheVersion::get('home'));
        $this->assertNotSame($listBefore, CacheVersion::get('list'));
        $this->assertNotSame($sitemapBefore, CacheVersion::get('sitemap'));
    }

    public function test_category_save_bumps_cache_versions(): void
    {
        $homeBefore = CacheVersion::get('home');
        $listBefore = CacheVersion::get('list');
        $sitemapBefore = CacheVersion::get('sitemap');

        Category::factory()->create();

        $this->assertNotSame($homeBefore, CacheVersion::get('home'));
        $this->assertNotSame($listBefore, CacheVersion::get('list'));
        $this->assertNotSame($sitemapBefore, CacheVersion::get('sitemap'));
    }

    public function test_petition_translation_save_bumps_home_and_list(): void
    {
        // create the parent petition first so its own bump does not mask the translation bump
        $petition = Petition::factory()->create();

        $homeBefore = CacheVersion::get('home');
        $listBefore = CacheVersion::get('list');

        PetitionTranslation::factory()->create(['petition_id' => $petition->id]);

        $this->assertNotSame($homeBefore, CacheVersion::get('home'));
        $this->assertNotSame($listBefore, CacheVersion::get('list'));
    }

    public function test_homepage_renders_new_petition_title_immediately_after_save(): void
    {
        $petition = Petition::factory()->create([
            'status' => 'published',
            'is_active' => true,
            'is_featured' => true,
        ]);

        $translation = PetitionTranslation::factory()->create([
            'petition_id' => $petition->id,
            'locale' => 'en',
            'title' => 'Original Homepage Petition Title',
            'slug' => 'original-homepage-petition-title',
            'is_featured' => true,
        ]);

        $this->get('/en')
            ->assertOk()
            ->assertSee('Original Homepage Petition Title');

        // title lives on the translation, so renaming it must invalidate the homepage cache
        $translation->update(['title' => 'Renamed Homepage Petition Title']);

        $this->get('/en')
            ->assertOk()
            ->assertSee('Renamed Homepage Petition Title')
            ->assertDontSee('Original Homepage Petition Title');
    }
}
