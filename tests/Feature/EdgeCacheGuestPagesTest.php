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

        $this->assertStringNotContainsString('name="_token"', $response->getContent());
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

    public function test_no_js_guest_can_complete_the_sign_flow(): void
    {
        [$petition, $slug] = $this->publishedPetition();
        $detailUrl = "/en/petition/{$slug}/{$petition->id}";
        $signPageUrl = "{$detailUrl}/sign/confirm";
        $signUrl = "{$detailUrl}/sign";

        $detail = $this->get($detailUrl)->assertOk();
        $this->assertStringContainsString('data-sign-cta', $detail->getContent());
        $this->assertStringContainsString($signPageUrl, $detail->getContent());

        $form = $this->get($signPageUrl)->assertOk();
        $form->assertHeaderMissing('X-FreeCause-Edge-Cache');

        $confirm = $this->post($signPageUrl, [
            '_token' => $this->csrfToken($form->getContent()),
            'name' => 'No Js Signer',
            'email' => 'no-js@example.com',
            'comment' => 'Signing without JavaScript',
            'agree1' => '1',
            'agree2' => '1',
            'agree3' => '1',
        ]);

        $this->assertNotSame(419, $confirm->getStatusCode());
        $this->assertNotSame(405, $confirm->getStatusCode());
        $confirm->assertOk();
        $this->assertStringContainsString($signUrl, $confirm->getContent());

        $final = $this->post($signUrl, [
            '_token' => $this->csrfToken($confirm->getContent()),
            'agree1' => '1',
            'agree2' => '1',
            'agree3' => '1',
            'comment' => 'Signing without JavaScript',
        ]);

        $this->assertNotSame(419, $final->getStatusCode());
        $final->assertRedirect();
    }

    private function csrfToken(string $html): string
    {
        preg_match('/name="_token"\s+value="([^"]+)"/', $html, $matches);

        $this->assertNotEmpty($matches[1] ?? null, 'expected a real CSRF token in the rendered form');

        return $matches[1];
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
