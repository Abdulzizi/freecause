<?php

namespace Tests\Feature;

use App\Models\Petition;
use App\Models\PetitionTranslation;
use App\Support\EdgeCachePurge;
use Database\Seeders\LanguageSeeder;
use Database\Seeders\UserLevelSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class EdgeCachePurgeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([LanguageSeeder::class, UserLevelSeeder::class]);
        config()->set('edge-cache.purge_enabled', true);
        config()->set('edge-cache.api_token', 'test-placeholder');
        config()->set('edge-cache.zone_id', 'test-zone');
        config()->set('edge-cache.base_url', 'https://www.freecause.com');
        Http::fake(['api.cloudflare.com/*' => Http::response(['success' => true])]);
    }

    public function test_purges_wait_for_commit_and_do_not_fire_on_rollback(): void
    {
        DB::beginTransaction();
        EdgeCachePurge::listings();
        Http::assertNothingSent();
        DB::rollBack();
        Http::assertNothingSent();

        DB::transaction(fn () => EdgeCachePurge::listings());
        Http::assertSent(fn ($request) => in_array('https://www.freecause.com/en/petitions', $request['files'], true));
    }

    public function test_disabled_purge_never_calls_cloudflare(): void
    {
        config()->set('edge-cache.purge_enabled', false);
        EdgeCachePurge::listings();
        Http::assertNothingSent();
    }

    public function test_purge_batches_urls_and_rejects_other_origins(): void
    {
        $urls = array_map(fn ($i) => 'https://www.freecause.com/en/petitions?page='.$i, range(1, 31));
        $urls[] = 'https://other.example/en/petitions';
        EdgeCachePurge::urls($urls);
        Http::assertSentCount(2);
        Http::assertSent(fn ($request) => count($request['files']) === 30);
    }

    public function test_remote_failure_does_not_fail_content_write(): void
    {
        Http::fake(['api.cloudflare.com/*' => Http::response(['success' => false], 500)]);
        $petition = DB::transaction(fn () => Petition::factory()->create(['status' => 'published', 'is_active' => true]));
        $this->assertDatabaseHas('petitions', ['id' => $petition->id]);
        Http::assertSent(fn ($request) => $request->method() === 'POST');
    }

    public function test_guest_sign_and_petition_update_purge_listings(): void
    {
        config()->set('edge-cache.purge_enabled', false);
        $petition = Petition::factory()->create(['status' => 'published', 'is_active' => true, 'signature_count' => 0]);
        PetitionTranslation::create(['petition_id' => $petition->id, 'locale' => 'en', 'title' => 'Purge petition', 'slug' => 'purge-petition', 'description' => 'Description']);
        config()->set('edge-cache.purge_enabled', true);

        $this->post('/en/petition/purge-petition/'.$petition->id.'/sign', [
            'name' => 'Test', 'email' => 'purge@example.com', 'agree1' => '1', 'agree2' => '1', 'agree3' => '1',
        ])->assertRedirect();
        Http::assertSent(fn ($request) => in_array('https://www.freecause.com/en/petitions', $request['files'], true));

        Http::fake(['api.cloudflare.com/*' => Http::response(['success' => true])]);
        $petition->target = 'Updated target';
        DB::transaction(fn () => $petition->save());
        Http::assertSent(fn ($request) => in_array('https://www.freecause.com/en/petitions', $request['files'], true));
    }
}
