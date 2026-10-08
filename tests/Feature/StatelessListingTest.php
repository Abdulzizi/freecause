<?php

namespace Tests\Feature;

use Database\Seeders\LanguageSeeder;
use Database\Seeders\UserLevelSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StatelessListingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([LanguageSeeder::class, UserLevelSeeder::class]);
    }

    public function test_anonymous_stateless_listing_sets_no_cookie(): void
    {
        $response = $this->get('/en/petitions')->assertOk();
        $response->assertHeader('X-FreeCause-Edge-Cache', 'eligible');
        $response->assertHeaderMissing('Set-Cookie');
    }

    public function test_cookie_bearing_listing_request_bypasses(): void
    {
        $response = $this->withUnencryptedCookie('probe', '1')
            ->get('/en/petitions')
            ->assertOk();
        $response->assertHeader('X-FreeCause-Edge-Cache', 'bypass');
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
    }
}
