<?php

namespace Tests\Feature;

use App\Models\Petition;
use App\Models\PetitionTranslation;
use Database\Seeders\LanguageSeeder;
use Database\Seeders\UserLevelSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MobileShellTest extends TestCase
{
    use RefreshDatabase;

    private const PAGES = ['/en', '/en/petitions', '/en/login', '/en/donations', '/en/faqs'];

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([LanguageSeeder::class, UserLevelSeeder::class]);
    }

    public function test_mobile_shell_renders_once_and_stays_csp_safe(): void
    {
        foreach (self::PAGES as $url) {
            $html = $this->get($url)->assertOk()->getContent();

            $this->assertSame(1, substr_count($html, 'data-bottom-nav'), "{$url} renders the bottom nav exactly once");
            $this->assertStringContainsString('viewport-fit=cover', $html, "{$url} opts into safe areas");
            $this->assertStringContainsString('data-sheet', $html, "{$url} has the sheet controller hook");

            $this->assertDoesNotMatchRegularExpression('/\son(click|submit|change|load)=/i', $html, "{$url} has no inline handlers");
            $this->assertSame(0, preg_match_all('/<script(?![^>]*\bsrc=)[^>]*>/i', $html), "{$url} has no inline scripts");
        }
    }

    public function test_cached_petition_detail_still_has_no_session_markup(): void
    {
        $petition = Petition::factory()->create(['status' => 'published', 'is_active' => true]);

        PetitionTranslation::create([
            'petition_id' => $petition->id,
            'locale' => 'en',
            'title' => 'Mobile Shell',
            'slug' => 'mobile-shell',
            'description' => 'Description',
        ]);

        $html = $this->get("/en/petition/mobile-shell/{$petition->id}")->assertOk()->getContent();

        $this->assertStringNotContainsString('name="_token"', $html);
        $this->assertStringContainsString('data-bottom-nav', $html);
        $this->assertStringContainsString('data-sign-cta', $html);
    }
}
