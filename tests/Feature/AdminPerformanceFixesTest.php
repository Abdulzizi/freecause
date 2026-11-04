<?php

namespace Tests\Feature;

use App\Jobs\PurgeEdgeListings;
use App\Models\Log;
use App\Models\Permission;
use App\Models\Petition;
use App\Models\PetitionTranslation;
use App\Models\User;
use App\Models\UserLevel;
use Database\Seeders\LanguageSeeder;
use Database\Seeders\UserLevelSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AdminPerformanceFixesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([UserLevelSeeder::class, LanguageSeeder::class]);
    }

    private function systemAdmin(): User
    {
        return User::factory()->create([
            'level_id' => UserLevel::where('name', 'admin')->value('id'),
            'verified' => true,
        ]);
    }

    private function editorAdmin(string $module, string $action = 'view'): User
    {
        $level = UserLevel::create([
            'name' => 'perf_editor_'.$module.'_'.$action,
            'visible_name' => 'Perf Editor',
            'is_system' => false,
        ]);

        Permission::create(['level_id' => $level->id, 'module' => $module, 'action' => $action]);

        return User::factory()->create(['level_id' => $level->id, 'verified' => true]);
    }

    public function test_stats_page_caches_totals_and_renders_type_tables(): void
    {
        Petition::factory()->count(3)->create(['status' => 'published']);
        $petitionIds = Petition::pluck('id');
        foreach ($petitionIds as $pid) {
            PetitionTranslation::create([
                'petition_id' => $pid, 'locale' => 'en',
                'title' => 'Published petition '.$pid, 'slug' => 'published-petition-'.$pid,
                'description' => 'd',
            ]);
        }

        $viewer = $this->editorAdmin('stats');

        $first = $this->actingAs($viewer, 'admin')->get(route('admin.stats'));
        $first->assertOk();
        $this->assertTrue(Cache::has('admin:dashboard:stats:v4'));
        $this->assertSame(3, Cache::get('admin:dashboard:stats:v4')['petitions_total']);

        $this->actingAs($viewer, 'admin')->get(route('admin.stats', ['type' => 'top_petitioners']));

        $typePage = $this->actingAs($viewer, 'admin')->get(route('admin.stats', ['type' => 'petitions_all']));
        $typePage->assertOk();
        $typePage->assertSee('published');
    }

    public function test_user_levels_page_shows_cached_member_counts(): void
    {
        User::factory()->count(2)->create(['level_id' => UserLevel::where('name', 'user')->value('id')]);

        $admin = $this->systemAdmin();

        $res = $this->actingAs($admin, 'admin')->get(route('admin.system.user_levels'));
        $res->assertOk();
        $this->assertTrue(Cache::has('level:user_counts'));

        $counts = Cache::get('level:user_counts');
        $this->assertSame(2, (int) ($counts[UserLevel::where('name', 'user')->value('id')] ?? 0));
    }

    public function test_logs_view_uses_simple_pagination_and_prefix_search(): void
    {
        Log::create(['level' => 'info', 'context' => 'system.users', 'title' => 'prefixmatch-title', 'content' => 'body']);
        Log::create(['level' => 'info', 'context' => 'system.logs', 'title' => 'other', 'content' => 'contains prefixmatch-title inside']);

        $viewer = $this->editorAdmin('logs');

        $res = $this->actingAs($viewer, 'admin')->get(route('admin.logs', ['q' => 'prefixmatch']));
        $res->assertOk();
        $res->assertSee('prefixmatch-title');
        $res->assertDontSee('other');

        $res2 = $this->actingAs($viewer, 'admin')->get(route('admin.logs', ['context' => 'system.users']));
        $res2->assertOk();
        $res2->assertSee('prefixmatch-title');
    }

    public function test_clear_cache_is_system_gated_and_targeted(): void
    {
        Cache::forever('settings:all', ['x' => 1]);
        Cache::forever('ver:home', 5);
        Cache::forever('ver:list', 5);
        Cache::rememberForever('unrelated:key', fn () => 'keepme');

        $editor = $this->editorAdmin('options', 'edit');
        $this->actingAs($editor, 'admin')->post(route('admin.options.clearCache'))->assertForbidden();
        $this->assertSame(['x' => 1], Cache::get('settings:all'), 'non-system admin must not flush');

        $admin = $this->systemAdmin();
        $this->actingAs($admin, 'admin')->post(route('admin.options.clearCache'))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertFalse(Cache::has('settings:all'));
        $this->assertSame(6, Cache::get('ver:home'));
        $this->assertSame(6, Cache::get('ver:list'));
        $this->assertSame('keepme', Cache::get('unrelated:key'), 'targeted rebuild must not wipe unrelated keys');
    }

    public function test_petitions_index_two_stage_query_filters_and_renders(): void
    {
        $user = User::factory()->create();

        $a = Petition::factory()->create(['user_id' => $user->id, 'status' => 'published', 'is_featured' => 1]);
        PetitionTranslation::create([
            'petition_id' => $a->id, 'locale' => 'en',
            'title' => 'Save the whales specifically', 'slug' => 'save-the-whales-specifically',
            'description' => 'd', 'text' => '<p>t</p>', 'is_featured' => true,
        ]);

        $b = Petition::factory()->create(['user_id' => $user->id, 'status' => 'draft', 'is_featured' => 0]);
        PetitionTranslation::create([
            'petition_id' => $b->id, 'locale' => 'en',
            'title' => 'Completely other topic', 'slug' => 'completely-other-topic',
            'description' => 'd',
        ]);

        $viewer = $this->editorAdmin('petitions');

        $res = $this->actingAs($viewer, 'admin')->get(route('admin.petitions', ['title' => 'save the whales']));
        $res->assertOk();
        $res->assertSee('Save the whales specifically');
        $res->assertDontSee('Completely other topic');

        $featured = $this->actingAs($viewer, 'admin')->get(route('admin.petitions', ['featured' => '1']));
        $featured->assertOk();
        $featured->assertSee('Save the whales specifically');
        $featured->assertDontSee('Completely other topic');

        $missing = $this->actingAs($viewer, 'admin')->get(route('admin.petitions', ['missing_locale' => 'de']));
        $missing->assertOk();
        $missing->assertSee('Save the whales specifically');
        $missing->assertSee('Completely other topic');

        $missingIt = $this->actingAs($viewer, 'admin')->get(route('admin.petitions', ['missing_locale' => 'it']));
        $missingIt->assertOk();
        $missingIt->assertSee('Save the whales specifically');
    }

    public function test_edge_purge_runs_through_the_queued_job(): void
    {
        config()->set('edge-cache.purge_enabled', true);
        config()->set('edge-cache.api_token', 'test-placeholder');
        config()->set('edge-cache.zone_id', 'test-zone');
        config()->set('edge-cache.base_url', 'https://www.freycastle.test');
        Http::fake(['api.cloudflare.com/*' => Http::response(['success' => true])]);

        $petition = Petition::factory()->create(['status' => 'published', 'is_active' => true]);
        PetitionTranslation::create([
            'petition_id' => $petition->id, 'locale' => 'en',
            'title' => 'Queue purge wiring', 'slug' => 'queue-purge-wiring', 'description' => 'd',
        ]);

        dispatch(new PurgeEdgeListings);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/purge_cache')
                && in_array('https://www.freycastle.test/en/petitions', $request['files'], true);
        });
    }
}
