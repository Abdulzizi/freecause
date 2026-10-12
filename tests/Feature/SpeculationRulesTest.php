<?php

namespace Tests\Feature;

use Database\Seeders\LanguageSeeder;
use Database\Seeders\UserLevelSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SpeculationRulesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([LanguageSeeder::class, UserLevelSeeder::class]);
    }

    public function test_public_html_response_advertises_the_rules_document(): void
    {
        $this->get('/en')
            ->assertOk()
            ->assertHeader('Speculation-Rules', '"/speculationrules.json"');
    }

    public function test_rules_document_is_served_as_speculation_rules_json(): void
    {
        $response = $this->get('/speculationrules.json');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/speculationrules+json');

        $rules = $response->json();

        $this->assertIsArray($rules['prerender']);
        $this->assertIsArray($rules['prefetch']);

        $matchAll = ['href_matches' => '/*', 'relative_to' => 'document'];
        $petitionExclusion = ['not' => ['href_matches' => '*/petition/*']];
        $adminExclusion = ['not' => ['href_matches' => '/admin/*']];
        $createExclusion = ['not' => ['href_matches' => '*/create-petition*']];
        $signExclusion = ['not' => ['href_matches' => '*/sign*']];

        $this->assertContains($matchAll, $rules['prerender'][0]['where']['and']);
        $this->assertContains($adminExclusion, $rules['prerender'][0]['where']['and']);
        $this->assertNotContains($petitionExclusion, $rules['prerender'][0]['where']['and']);
        $this->assertContains($createExclusion, $rules['prerender'][0]['where']['and']);
        $this->assertContains($signExclusion, $rules['prerender'][0]['where']['and']);
        $this->assertSame('moderate', $rules['prerender'][0]['eagerness']);

        $this->assertContains($matchAll, $rules['prefetch'][0]['where']['and']);
        $this->assertContains($adminExclusion, $rules['prefetch'][0]['where']['and']);
        $this->assertNotContains($petitionExclusion, $rules['prefetch'][0]['where']['and']);
        $this->assertSame('conservative', $rules['prefetch'][0]['eagerness']);
    }

    public function test_header_is_not_added_to_admin_responses(): void
    {
        $this->get('/admin/login')
            ->assertOk()
            ->assertHeaderMissing('Speculation-Rules');
    }

    public function test_header_is_not_added_to_non_get_requests(): void
    {
        $this->post('/en/login', [])
            ->assertStatus(302)
            ->assertHeaderMissing('Speculation-Rules');
    }
}
