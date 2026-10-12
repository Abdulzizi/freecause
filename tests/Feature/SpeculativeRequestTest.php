<?php

namespace Tests\Feature;

use App\Http\Middleware\ConditionalLogging;
use App\Models\Petition;
use App\Models\PetitionTranslation;
use Database\Seeders\LanguageSeeder;
use Database\Seeders\UserLevelSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class SpeculativeRequestTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([UserLevelSeeder::class, LanguageSeeder::class]);
    }

    public function test_normal_petition_view_is_counted(): void
    {
        $petition = $this->createPetition('normal-view');

        $this->get("/en/petition/normal-view/{$petition->id}")->assertOk();

        $this->assertDatabaseHas('petitions', ['id' => $petition->id, 'views' => 1]);
        $this->assertDatabaseHas('petition_views', ['petition_id' => $petition->id, 'views' => 1]);
    }

    public function test_prefetch_request_is_not_counted(): void
    {
        $petition = $this->createPetition('prefetch-view');

        $this->get("/en/petition/prefetch-view/{$petition->id}", ['Sec-Purpose' => 'prefetch'])->assertOk();

        $this->assertDatabaseHas('petitions', ['id' => $petition->id, 'views' => 0]);
        $this->assertDatabaseMissing('petition_views', ['petition_id' => $petition->id]);
    }

    public function test_prerender_request_is_counted(): void
    {
        $petition = $this->createPetition('prerender-view');

        $this->get("/en/petition/prerender-view/{$petition->id}", ['Sec-Purpose' => 'prefetch;prerender'])->assertOk();

        $this->assertDatabaseHas('petitions', ['id' => $petition->id, 'views' => 1]);
        $this->assertDatabaseHas('petition_views', ['petition_id' => $petition->id, 'views' => 1]);
    }

    public function test_speculative_detection_reads_sec_purpose_header(): void
    {
        $this->assertFalse(ConditionalLogging::isSpeculative(Request::create('/')));
        $this->assertFalse(ConditionalLogging::isSpeculative($this->requestWithPurpose('navigation')));

        $this->assertTrue(ConditionalLogging::isSpeculative($this->requestWithPurpose('prefetch')));
        $this->assertTrue(ConditionalLogging::isSpeculative($this->requestWithPurpose('Prefetch')));
        $this->assertTrue(ConditionalLogging::isSpeculative($this->requestWithPurpose('prefetch;prerender')));

        $this->assertTrue(ConditionalLogging::isPrefetchOnly($this->requestWithPurpose('prefetch')));
        $this->assertFalse(ConditionalLogging::isPrefetchOnly($this->requestWithPurpose('prefetch;prerender')));
        $this->assertFalse(ConditionalLogging::isPrefetchOnly(Request::create('/')));
    }

    private function createPetition(string $slug): Petition
    {
        $petition = Petition::factory()->create(['status' => 'published', 'is_active' => true]);
        PetitionTranslation::create([
            'petition_id' => $petition->id,
            'locale' => 'en',
            'title' => 'Speculative Petition',
            'slug' => $slug,
            'description' => 'Description',
        ]);

        return $petition;
    }

    private function requestWithPurpose(string $purpose): Request
    {
        return Request::create('/', 'GET', [], [], [], ['HTTP_SEC_PURPOSE' => $purpose]);
    }
}
