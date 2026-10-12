<?php

namespace Tests\Feature;

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
}
