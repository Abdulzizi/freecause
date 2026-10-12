<?php

namespace Tests\Feature;

use App\Models\Petition;
use App\Models\PetitionTranslation;
use App\Models\User;
use Database\Seeders\LanguageSeeder;
use Database\Seeders\UserLevelSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EdgeCacheGuestPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([UserLevelSeeder::class, LanguageSeeder::class]);
    }

    public function test_guest_static_page_is_edge_eligible_and_cookie_free(): void
    {
        $response = $this->get('/en/donations');

        $response->assertOk()
            ->assertHeader('X-FreeCause-Edge-Cache', 'eligible')
            ->assertHeaderMissing('Set-Cookie');

        $this->assertStringContainsString('s-maxage=600', (string) $response->headers->get('Cache-Control'));
    }

    public function test_guest_home_is_deliberately_not_edge_cached(): void
    {
        $this->get('/en')
            ->assertOk()
            ->assertHeaderMissing('X-FreeCause-Edge-Cache');
    }

    public function test_request_with_any_cookie_bypasses(): void
    {
        $this->withUnencryptedCookie(config('session.cookie'), 'opaque')
            ->get('/en/donations')
            ->assertOk()
            ->assertHeader('X-FreeCause-Edge-Cache', 'bypass');
    }

    public function test_guest_petition_detail_is_edge_eligible_without_a_form_token(): void
    {
        [$petition, $slug] = $this->publishedPetition();

        $response = $this->get("/en/petition/{$slug}/{$petition->id}");

        $response->assertOk()
            ->assertHeader('X-FreeCause-Edge-Cache', 'eligible')
            ->assertHeaderMissing('Set-Cookie');

        $this->assertStringNotContainsString('name="_token"', $response->getContent());
        $this->assertStringContainsString('/sign/confirm', $response->getContent());
    }

    public function test_authenticated_petition_detail_bypasses(): void
    {
        [$petition, $slug] = $this->publishedPetition();

        $this->actingAs($this->user())
            ->get("/en/petition/{$slug}/{$petition->id}")
            ->assertOk()
            ->assertHeader('X-FreeCause-Edge-Cache', 'bypass');
    }

    private function user()
    {
        return User::factory()->create(['verified' => true]);
    }

    private function publishedPetition(): array
    {
        $petition = Petition::factory()->create(['status' => 'published', 'is_active' => true]);
        $slug = 'edge-cache-detail-'.$petition->id;

        PetitionTranslation::create([
            'petition_id' => $petition->id,
            'locale' => 'en',
            'title' => 'Edge Cache Detail',
            'slug' => $slug,
            'description' => 'Description',
        ]);

        return [$petition, $slug];
    }
}
