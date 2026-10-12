<?php

namespace Tests\Feature;

use App\Models\Petition;
use Database\Seeders\LanguageSeeder;
use Database\Seeders\UserLevelSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_health_endpoint_returns_a_successful_response(): void
    {
        $response = $this->get('/up');

        $response->assertStatus(200);
    }

    public function test_homepage_empty_featured_state_has_no_link(): void
    {
        $this->seed(LanguageSeeder::class);

        $response = $this->get('/en');

        $response->assertOk()->assertSee('no featured petition yet');

        $html = $response->getContent();
        $start = strpos($html, 'id="featured-petition-tab-pane"');
        $end = strpos($html, 'id="recent-activities-tab-pane"');
        $featuredPane = substr($html, $start, $end - $start);

        $this->assertStringNotContainsString('<a ', $featuredPane);
    }

    public function test_homepage_featured_petition_links_to_its_detail_page(): void
    {
        $this->seed([LanguageSeeder::class, UserLevelSeeder::class]);

        $petition = Petition::factory()->create([
            'status' => 'published',
            'is_active' => true,
            'is_featured' => true,
        ]);
        $translation = $petition->translations()->create([
            'locale' => 'en',
            'title' => 'Featured Test Petition',
            'slug' => 'featured-test-petition',
            'description' => 'A featured petition used for homepage regression coverage.',
            'is_featured' => true,
        ]);

        $response = $this->get('/en');
        $expectedUrl = lroute('petition.show', [
            'slug' => $translation->slug,
            'id' => $petition->id,
        ]);

        $response
            ->assertOk()
            ->assertSee('Featured Test Petition')
            ->assertSee('href="'.$expectedUrl.'"', false);
    }
}
