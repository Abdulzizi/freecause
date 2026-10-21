<?php

namespace Tests\Feature;

use App\Jobs\RescanReviewKeywords;
use App\Models\Category;
use App\Models\CategoryTranslation;
use App\Models\Permission;
use App\Models\Petition;
use App\Models\Setting;
use App\Models\User;
use App\Models\UserLevel;
use App\Services\ReviewKeywordRescan;
use App\Support\ReviewKeywords;
use App\Support\Settings;
use Database\Seeders\LanguageSeeder;
use Database\Seeders\UserLevelSeeder;
use Illuminate\Bus\UniqueLock;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Route;
use RuntimeException;
use Tests\TestCase;

class AdminReviewKeywordRescanTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();

        ReviewKeywords::flushCache();

        $this->seed([UserLevelSeeder::class, LanguageSeeder::class]);

        $this->user = User::factory()->create(['verified' => true]);

        $this->category = Category::factory()->create(['is_active' => true]);

        CategoryTranslation::create([
            'category_id' => $this->category->id,
            'locale' => 'en',
            'name' => 'Test Category',
            'slug' => 'test-category',
        ]);
    }

    protected function tearDown(): void
    {
        ReviewKeywordRescan::$chunkSize = ReviewKeywordRescan::CHUNK_SIZE;

        parent::tearDown();
    }

    private function createPetition(string $locale, string $title): Petition
    {
        $this->actingAs($this->user)->post("/{$locale}/create-petition", [
            'title' => $title,
            'description' => '<p>This is a test petition with enough content to pass validation and be accepted.</p>',
            'goal_signatures' => 100,
            'category_id' => $this->category->id,
        ])->assertRedirect();

        return Petition::latest('id')->firstOrFail();
    }

    private function renamePetition(Petition $petition, string $title): void
    {
        DB::table('petition_translations')
            ->where('petition_id', $petition->id)
            ->update(['title' => $title]);
    }

    private function reviewLogsFor(int $petitionId): array
    {
        return DB::table('spam_logs')
            ->where('type', 'petition.review')
            ->where('payload', 'like', '%"petition_id":'.$petitionId.',%')
            ->get(['payload'])
            ->map(fn ($row): string => (string) $row->payload)
            ->all();
    }

    private function rescanReturning(array $result): ReviewKeywordRescan
    {
        return new class($result) extends ReviewKeywordRescan
        {
            public array $calls = [];

            public function __construct(private array $result) {}

            public function run(?int $limit = null, int $maxSeconds = self::DEFAULT_MAX_SECONDS, ?int $afterId = null, ?callable $onProgress = null): array
            {
                $this->calls[] = [
                    'limit' => $limit,
                    'maxSeconds' => $maxSeconds,
                    'afterId' => $afterId,
                    'progress' => is_callable($onProgress),
                ];

                return $this->result;
            }
        };
    }

    private function userWithPermissions(string $name, array $permissions): User
    {
        $level = UserLevel::create([
            'name' => $name,
            'visible_name' => $name,
            'is_system' => false,
        ]);

        foreach ($permissions as $module => $actions) {
            foreach ($actions as $action) {
                Permission::create(['level_id' => $level->id, 'module' => $module, 'action' => $action]);
            }
        }

        return User::factory()->create(['level_id' => $level->id, 'verified' => true]);
    }

    private function adminUser(): User
    {
        return $this->userWithPermissions('keywords_editor', [
            'spam' => ['view', 'edit'],
            'petitions' => ['view', 'edit'],
        ]);
    }

    private function spamViewer(): User
    {
        return $this->userWithPermissions('keywords_viewer_only', [
            'spam' => ['view'],
        ]);
    }

    private function spamEditor(): User
    {
        return $this->userWithPermissions('keywords_spam_editor', [
            'spam' => ['view', 'edit'],
        ]);
    }

    private function rescanRouteMiddleware(): array
    {
        $route = Route::getRoutes()->getByName('admin.keywords.rescan');

        return $route ? $route->gatherMiddleware() : [];
    }

    private function writeSetting(string $key, mixed $value, string $group = 'moderation'): void
    {
        Setting::updateOrCreate(
            ['group' => $group, 'key' => $key],
            ['value' => (string) $value, 'type' => 'string']
        );

        Cache::flush();

        Settings::forget();

        ReviewKeywords::flushCache();
    }

    private function settingValue(string $key, string $group = 'moderation'): ?string
    {
        $value = DB::table('settings')
            ->where('group', $group)
            ->where('key', $key)
            ->value('value');

        return $value === null ? null : (string) $value;
    }

    private function withQueueDefault(string $connection, callable $callback): mixed
    {
        $original = config('queue.default');

        config(['queue.default' => $connection]);

        try {
            return $callback();
        } finally {
            config(['queue.default' => $original]);
        }
    }

    public function test_the_rescan_holds_a_published_petition_that_matches_a_keyword(): void
    {
        $matching = $this->createPetition('en', 'Keep the local library open');
        $this->renamePetition($matching, 'Stop betting advertising');

        $clean = $this->createPetition('en', 'Support the local schools');

        $this->assertSame('published', $matching->fresh()->status);
        $this->assertSame('published', $clean->fresh()->status);

        $result = (new ReviewKeywordRescan)->run();

        $this->assertSame(2, $result['scanned']);
        $this->assertSame(1, $result['held']);

        $this->assertSame('draft', $matching->fresh()->status);
        $this->assertSame('published', $clean->fresh()->status);

        $logs = $this->reviewLogsFor($matching->id);

        $this->assertCount(1, $logs);
        $this->assertStringContainsString('"petition_id":'.$matching->id, $logs[0]);
        $this->assertStringContainsString('betting', $logs[0]);

        $this->assertSame([], $this->reviewLogsFor($clean->id));
    }

    public function test_the_rescan_payload_is_truncated_and_keeps_the_petition_id(): void
    {
        $terms = [];

        foreach (range(0, 349) as $index) {
            $term = 'bulk'.chr(97 + intdiv($index, 26)).chr(97 + ($index % 26)).str_repeat('z', 247);

            $terms[] = ['term' => $term, 'concept' => $term];
        }

        ReviewKeywords::saveLocale('en', $terms);

        $petition = $this->createPetition('en', 'Keep the local library open');

        DB::table('petition_translations')
            ->where('petition_id', $petition->id)
            ->update(['description' => '<p>'.implode(' ', array_column($terms, 'term')).'</p>']);

        $result = (new ReviewKeywordRescan)->run();

        $this->assertSame(1, $result['held']);

        $logs = $this->reviewLogsFor($petition->id);

        $this->assertCount(1, $logs);

        $payload = $logs[0];

        $this->assertLessThanOrEqual(
            ReviewKeywordRescan::PAYLOAD_LIMIT,
            strlen($payload),
            'The stored payload must never grow past the payload limit.'
        );

        $this->assertSame(
            ReviewKeywordRescan::PAYLOAD_LIMIT,
            strlen($payload),
            'The payload must be cut at the payload limit.'
        );

        $this->assertStringContainsString(
            '"petition_id":'.$petition->id,
            $payload,
            'The truncated payload must keep the petition id the admin sheet matches on.'
        );

        $this->assertStringNotContainsString(
            $terms[349]['term'],
            $payload,
            'The payload must be cut before the last keyword, so the raw payload is longer than the limit.'
        );
    }

    public function test_the_rescan_leaves_a_draft_petition_untouched(): void
    {
        $draft = $this->createPetition('en', 'Keep the local library open');
        $this->renamePetition($draft, 'Stop betting advertising');

        DB::table('petitions')->where('id', $draft->id)->update(['status' => 'draft']);

        $published = $this->createPetition('en', 'Support the local schools');

        $result = (new ReviewKeywordRescan)->run();

        $this->assertSame(1, $result['scanned']);
        $this->assertSame(0, $result['held']);

        $this->assertSame('draft', $draft->fresh()->status);
        $this->assertSame('published', $published->fresh()->status);
        $this->assertSame([], $this->reviewLogsFor($draft->id));
    }

    public function test_the_rescan_holds_a_published_petition_that_is_not_active(): void
    {
        $matching = $this->createPetition('en', 'Keep the local library open');
        $this->renamePetition($matching, 'Stop betting advertising');

        DB::table('petitions')->where('id', $matching->id)->update(['is_active' => 0]);

        $this->assertSame(0, (int) $matching->fresh()->is_active);

        $result = (new ReviewKeywordRescan)->run();

        $this->assertSame(1, $result['scanned']);
        $this->assertSame(1, $result['held']);

        $this->assertSame('draft', $matching->fresh()->status);
        $this->assertCount(1, $this->reviewLogsFor($matching->id));
    }

    public function test_running_the_rescan_twice_does_not_duplicate_the_log_rows(): void
    {
        $matching = $this->createPetition('en', 'Keep the local library open');
        $this->renamePetition($matching, 'Stop betting advertising');

        $first = (new ReviewKeywordRescan)->run();

        $this->assertSame(1, $first['held']);
        $this->assertCount(1, $this->reviewLogsFor($matching->id));

        $second = (new ReviewKeywordRescan)->run();

        $this->assertSame(0, $second['scanned']);
        $this->assertSame(0, $second['held']);
        $this->assertCount(1, $this->reviewLogsFor($matching->id));
        $this->assertSame('draft', $matching->fresh()->status);
    }

    public function test_the_rescan_skips_a_petition_that_matches_a_disabled_concept(): void
    {
        ReviewKeywords::saveLocale('en', [
            ['term' => 'alpha term', 'concept' => 'shared concept'],
        ]);

        Cache::flush();

        Settings::forget();

        ReviewKeywords::flushCache();

        $this->assertContains(
            'alpha term',
            ReviewKeywords::catalogue()['en'],
            'The term must be live before the concept is switched off.'
        );

        ReviewKeywords::setConceptEnabled('shared concept', false);

        $this->assertNotContains('alpha term', ReviewKeywords::catalogue()['en']);

        $petition = $this->createPetition('en', 'Keep the local library open');
        $this->renamePetition($petition, 'Stop alpha term advertising');

        $result = (new ReviewKeywordRescan)->run();

        $this->assertSame(1, $result['scanned']);
        $this->assertSame(0, $result['held']);
        $this->assertSame('published', $petition->fresh()->status);
        $this->assertSame([], $this->reviewLogsFor($petition->id));
    }

    public function test_the_rescan_does_not_match_a_term_across_the_title_and_description(): void
    {
        ReviewKeywords::saveLocale('en', [
            ['term' => 'alpha term', 'concept' => 'alpha concept'],
        ]);

        Cache::flush();

        Settings::forget();

        ReviewKeywords::flushCache();

        $petition = $this->createPetition('en', 'Keep the local library open');

        DB::table('petition_translations')
            ->where('petition_id', $petition->id)
            ->update([
                'title' => 'Stop alpha',
                'description' => '<p>term advertising everywhere</p>',
            ]);

        $result = (new ReviewKeywordRescan)->run();

        $this->assertSame(1, $result['scanned']);
        $this->assertSame(
            0,
            $result['held'],
            'A term must not match across the boundary between the title and the description.'
        );

        $this->assertSame('published', $petition->fresh()->status);
        $this->assertSame([], $this->reviewLogsFor($petition->id));
    }

    public function test_the_rescan_bookkeeping_rows_are_never_read_as_keyword_lists(): void
    {
        $bogusTerm = 'zz-bogus-rescan-term';

        DB::table('settings')->updateOrInsert(
            ['group' => 'moderation', 'key' => 'review_keywords_rescan_terms'],
            [
                'value' => json_encode([['term' => $bogusTerm, 'concept' => $bogusTerm]]),
                'type' => 'json',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        Cache::flush();

        Settings::forget();

        ReviewKeywords::flushCache();

        $this->assertArrayNotHasKey(
            'rescan_terms',
            ReviewKeywords::overrides(),
            'The rescan bookkeeping rows must never be parsed as keyword overrides.'
        );

        $this->assertArrayNotHasKey(
            'rescan_terms',
            ReviewKeywords::catalogue(),
            'The rescan bookkeeping rows must never add a catalogue locale.'
        );

        $this->assertNotContains(
            $bogusTerm,
            ReviewKeywords::all(),
            'The rescan bookkeeping rows must never add a keyword term.'
        );

        $this->assertSame(
            [],
            ReviewKeywords::matches($bogusTerm),
            'The rescan bookkeeping rows must never make a term match.'
        );
    }

    public function test_the_artisan_command_runs_the_rescan(): void
    {
        $matching = $this->createPetition('en', 'Keep the local library open');
        $this->renamePetition($matching, 'Stop betting advertising');

        $this->artisan('review-keywords:rescan')
            ->expectsOutputToContain('held 1')
            ->assertExitCode(0);

        $this->assertSame('draft', $matching->fresh()->status);
        $this->assertCount(1, $this->reviewLogsFor($matching->id));
    }

    public function test_the_artisan_command_resumes_from_the_stored_cursor(): void
    {
        $lower = $this->createPetition('en', 'Keep the local library open');
        $this->renamePetition($lower, 'Stop betting advertising');

        $this->assertSame('published', $lower->fresh()->status);

        $this->writeSetting('review_keywords_rescan_cursor', $lower->id);

        $higher = $this->createPetition('en', 'Support the local schools');
        $this->renamePetition($higher, 'Stop betting advertising today');

        $this->assertGreaterThan($lower->id, $higher->id);
        $this->assertSame('published', $higher->fresh()->status);

        $this->artisan('review-keywords:rescan')
            ->expectsOutputToContain('held 1')
            ->assertExitCode(0);

        $this->assertSame(
            'published',
            $lower->fresh()->status,
            'The scan must start after the stored cursor, the petition behind it is not rescanned.'
        );

        $this->assertSame('draft', $higher->fresh()->status);

        $this->assertSame([], $this->reviewLogsFor($lower->id));
        $this->assertCount(1, $this->reviewLogsFor($higher->id));
    }

    public function test_the_artisan_command_refuses_to_run_while_a_rescan_lease_is_held(): void
    {
        $matching = $this->createPetition('en', 'Keep the local library open');
        $this->renamePetition($matching, 'Stop betting advertising');

        $this->writeSetting('review_keywords_rescan_running', 1);
        $this->writeSetting('review_keywords_rescan_started_at', time());

        $exitCode = Artisan::call('review-keywords:rescan');

        $output = Artisan::output();

        $this->assertNotSame(
            0,
            $exitCode,
            'A refused rescan must not report success.'
        );

        $this->assertMatchesRegularExpression(
            '/already|running|in progress|lease|refus/i',
            $output,
            "The command must say why it refused to run, got: '{$output}'."
        );

        $status = (string) $this->settingValue('review_keywords_rescan_status');

        $this->assertMatchesRegularExpression(
            '/fail|refus|already/i',
            $status,
            "The command must record a failure status, got: '{$status}'."
        );

        $this->assertSame('published', $matching->fresh()->status);
        $this->assertSame([], $this->reviewLogsFor($matching->id));
    }

    public function test_the_admin_can_trigger_the_rescan(): void
    {
        if (! Route::has('admin.keywords.rescan')) {
            $this->markTestSkipped('The admin rescan route is not registered.');
        }

        $matching = $this->createPetition('en', 'Keep the local library open');
        $this->renamePetition($matching, 'Stop betting advertising');

        Queue::fake();

        $admin = $this->adminUser();

        $response = $this->withQueueDefault('database', fn () => $this->actingAs($admin, 'admin')
            ->post(route('admin.keywords.rescan'), ['confirm' => 'rescan', 'locale' => 'en']));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $flash = (string) session('success');

        $this->assertMatchesRegularExpression(
            '/queued|started|background/i',
            $flash,
            "The rescan must be queued and reported back to the admin, got: '{$flash}'."
        );

        Queue::assertPushed(RescanReviewKeywords::class);

        $this->assertSame(
            'published',
            $matching->fresh()->status,
            'The endpoint must not hold a petition inline, the queued job does that.'
        );
        $this->assertSame([], $this->reviewLogsFor($matching->id));
    }

    public function test_the_rescan_stops_early_and_reports_the_cursor_when_the_limit_is_reached(): void
    {
        $first = $this->createPetition('en', 'Keep the local library open');
        $second = $this->createPetition('en', 'Support the local schools');

        $result = (new ReviewKeywordRescan)->run(1);

        $this->assertSame(1, $result['scanned']);
        $this->assertSame(0, $result['held']);
        $this->assertSame($first->id, $result['lastId']);
        $this->assertFalse($result['complete']);

        $this->assertSame('published', $first->fresh()->status);
        $this->assertSame('published', $second->fresh()->status);
    }

    public function test_the_rescan_reports_a_complete_run_at_the_end_of_the_table(): void
    {
        $first = $this->createPetition('en', 'Keep the local library open');
        $second = $this->createPetition('en', 'Support the local schools');

        $result = (new ReviewKeywordRescan)->run();

        $this->assertSame(2, $result['scanned']);
        $this->assertSame(0, $result['held']);
        $this->assertSame($second->id, $result['lastId']);
        $this->assertTrue($result['complete']);
    }

    public function test_the_job_clears_the_cursor_when_the_run_reaches_the_end_of_the_table(): void
    {
        $this->createPetition('en', 'Keep the local library open');

        (new RescanReviewKeywords)->handle();

        $this->assertSame(0, Settings::get('review_keywords_rescan_cursor', 0, 'moderation'));
        $this->assertDatabaseHas('settings', [
            'group' => 'moderation',
            'key' => 'review_keywords_rescan_cursor',
            'value' => '0',
        ]);
    }

    public function test_the_job_keeps_the_cursor_and_queues_a_continuation_when_the_run_is_incomplete(): void
    {
        Queue::fake();

        $this->createPetition('en', 'Keep the local library open');

        $leaseStart = time() - 5;

        $this->writeSetting('review_keywords_rescan_running', 1);
        $this->writeSetting('review_keywords_rescan_started_at', $leaseStart);

        $job = new RescanReviewKeywords($this->rescanReturning([
            'scanned' => 10,
            'held' => 0,
            'lastId' => 42,
            'complete' => false,
        ]), 0, $leaseStart);

        $before = (int) Settings::get('review_keywords_rescan_started_at', 0, 'moderation');

        $job->handle();

        $after = (int) Settings::get('review_keywords_rescan_started_at', 0, 'moderation');

        $this->assertSame($leaseStart, $before);

        $this->assertGreaterThan(
            $before,
            $after,
            'The continuation must renew the lease stamp, otherwise every segment after the first aborts.'
        );

        $this->assertSame(42, Settings::get('review_keywords_rescan_cursor', 0, 'moderation'));
        $this->assertDatabaseHas('settings', [
            'group' => 'moderation',
            'key' => 'review_keywords_rescan_cursor',
            'value' => '42',
        ]);

        $this->assertDatabaseHas('settings', [
            'group' => 'moderation',
            'key' => 'review_keywords_rescan_started_at',
            'value' => (string) $after,
        ]);

        Queue::assertPushed(
            RescanReviewKeywords::class,
            fn (RescanReviewKeywords $queued): bool => $queued->segment === 1
                && $queued->leaseStart === $after
        );
    }

    public function test_the_chain_stops_when_a_segment_makes_no_progress(): void
    {
        Queue::fake();

        $this->writeSetting('review_keywords_rescan_cursor', 42);

        $service = $this->rescanReturning([
            'scanned' => 0,
            'held' => 0,
            'lastId' => 42,
            'complete' => false,
        ]);

        (new RescanReviewKeywords($service, 1))->handle();

        Queue::assertNotPushed(RescanReviewKeywords::class);

        $this->assertSame(
            42,
            (int) $this->settingValue('review_keywords_rescan_cursor'),
            'A segment without progress must leave the cursor where it was.'
        );
    }

    public function test_the_chain_stops_at_the_segment_cap(): void
    {
        Queue::fake();

        $this->writeSetting('review_keywords_rescan_cursor', 42);

        $service = $this->rescanReturning([
            'scanned' => 5,
            'held' => 0,
            'lastId' => 99,
            'complete' => false,
        ]);

        (new RescanReviewKeywords($service, RescanReviewKeywords::MAX_SEGMENTS))->handle();

        Queue::assertNotPushed(RescanReviewKeywords::class);

        $this->assertSame(
            99,
            (int) $this->settingValue('review_keywords_rescan_cursor'),
            'The capped segment still records its progress.'
        );
    }

    public function test_the_chain_stops_when_the_next_segment_is_already_queued(): void
    {
        Queue::fake();

        $this->writeSetting('review_keywords_rescan_cursor', 42);

        $lock = app(CacheRepository::class)->lock(UniqueLock::getKey(new RescanReviewKeywords(null, 1)), 300);

        $this->assertTrue($lock->get(), 'The next segment lock must be held for this test to mean anything.');

        $service = $this->rescanReturning([
            'scanned' => 5,
            'held' => 0,
            'lastId' => 99,
            'complete' => false,
        ]);

        (new RescanReviewKeywords($service, 0))->handle();

        Queue::assertNotPushed(RescanReviewKeywords::class);

        $status = (string) $this->settingValue('review_keywords_rescan_status');

        $this->assertMatchesRegularExpression(
            '/stopped/i',
            $status,
            "The chain must record that it stopped, got: '{$status}'."
        );

        $this->assertSame(
            99,
            (int) $this->settingValue('review_keywords_rescan_cursor'),
            'The stopped chain keeps the progress it made.'
        );
    }

    public function test_a_continuation_segment_resumes_from_the_stored_cursor(): void
    {
        $this->writeSetting('review_keywords_rescan_cursor', 7);

        $service = $this->rescanReturning([
            'scanned' => 0,
            'held' => 0,
            'lastId' => 7,
            'complete' => true,
        ]);

        (new RescanReviewKeywords($service, 1))->handle();

        $this->assertSame([
            [
                'limit' => null,
                'maxSeconds' => RescanReviewKeywords::MAX_SECONDS,
                'afterId' => 7,
                'progress' => true,
            ],
        ], $service->calls);

        $this->assertSame(0, Settings::get('review_keywords_rescan_cursor', 0, 'moderation'));
    }

    public function test_the_first_segment_ignores_the_stored_cursor(): void
    {
        $this->writeSetting('review_keywords_rescan_cursor', 7);

        $service = $this->rescanReturning([
            'scanned' => 0,
            'held' => 0,
            'lastId' => 0,
            'complete' => true,
        ]);

        (new RescanReviewKeywords($service))->handle();

        $this->assertSame([
            [
                'limit' => null,
                'maxSeconds' => RescanReviewKeywords::MAX_SECONDS,
                'afterId' => 0,
                'progress' => true,
            ],
        ], $service->calls);
    }

    public function test_a_stale_cursor_does_not_skip_the_head_of_the_table(): void
    {
        Queue::fake();

        $matching = $this->createPetition('en', 'Keep the local library open');
        $this->renamePetition($matching, 'Stop betting advertising');

        $this->writeSetting('review_keywords_rescan_cursor', 999999);

        $this->assertLessThan(999999, $matching->id);

        (new RescanReviewKeywords)->handle();

        $this->assertSame('draft', $matching->fresh()->status);
        $this->assertCount(1, $this->reviewLogsFor($matching->id));
    }

    public function test_the_admin_endpoint_refuses_to_start_a_rescan_while_one_is_running(): void
    {
        if (! Route::has('admin.keywords.rescan')) {
            $this->markTestSkipped('The admin rescan route is not registered.');
        }

        $matching = $this->createPetition('en', 'Keep the local library open');
        $this->renamePetition($matching, 'Stop betting advertising');

        $this->writeSetting('review_keywords_rescan_running', 1);
        $this->writeSetting('review_keywords_rescan_started_at', time());

        Queue::fake();

        $admin = $this->adminUser();

        $response = $this->withQueueDefault('database', fn () => $this->actingAs($admin, 'admin')
            ->post(route('admin.keywords.rescan'), ['confirm' => 'rescan', 'locale' => 'en']));

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertMatchesRegularExpression('/already (running|queued)/i', (string) session('error'));

        Queue::assertNothingPushed();

        $this->assertSame('published', $matching->fresh()->status);
        $this->assertSame([], $this->reviewLogsFor($matching->id));
    }

    public function test_the_admin_endpoint_queues_the_rescan_when_no_rescan_is_running(): void
    {
        if (! Route::has('admin.keywords.rescan')) {
            $this->markTestSkipped('The admin rescan route is not registered.');
        }

        $this->writeSetting('review_keywords_rescan_running', 0);

        Queue::fake();

        $admin = $this->adminUser();

        $response = $this->withQueueDefault('database', fn () => $this->actingAs($admin, 'admin')
            ->post(route('admin.keywords.rescan'), ['confirm' => 'rescan', 'locale' => 'en']));

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertMatchesRegularExpression('/queue/i', (string) session('success'));

        Queue::assertPushed(RescanReviewKeywords::class);
    }

    public function test_the_admin_endpoint_queues_the_rescan_when_the_running_flag_is_stale(): void
    {
        if (! Route::has('admin.keywords.rescan')) {
            $this->markTestSkipped('The admin rescan route is not registered.');
        }

        $staleStamp = time() - (11 * 60);

        $this->writeSetting('review_keywords_rescan_running', 1);
        $this->writeSetting('review_keywords_rescan_started_at', $staleStamp);

        Queue::fake();

        $admin = $this->adminUser();

        $response = $this->withQueueDefault('database', fn () => $this->actingAs($admin, 'admin')
            ->post(route('admin.keywords.rescan'), ['confirm' => 'rescan', 'locale' => 'en']));

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertMatchesRegularExpression('/queue/i', (string) session('success'));

        Queue::assertPushed(RescanReviewKeywords::class);

        $running = (int) $this->settingValue('review_keywords_rescan_running');
        $startedAt = (int) $this->settingValue('review_keywords_rescan_started_at');

        $this->assertSame(
            1,
            $running,
            'The lease stays claimed until the queued job runs, the request only replaces it.'
        );

        $this->assertGreaterThan(
            $staleStamp,
            $startedAt,
            'The stale lease must be replaced by a fresh one.'
        );
    }

    public function test_the_admin_endpoint_refuses_to_queue_the_rescan_on_a_sync_queue(): void
    {
        if (! Route::has('admin.keywords.rescan')) {
            $this->markTestSkipped('The admin rescan route is not registered.');
        }

        $matching = $this->createPetition('en', 'Keep the local library open');
        $this->renamePetition($matching, 'Stop betting advertising');

        $this->writeSetting('review_keywords_rescan_running', 0);

        Queue::fake();

        $admin = $this->adminUser();

        $response = $this->withQueueDefault('sync', fn () => $this->actingAs($admin, 'admin')
            ->post(route('admin.keywords.rescan'), ['confirm' => 'rescan', 'locale' => 'en']));

        $response->assertRedirect();
        $response->assertSessionHas('error');

        $flash = (string) session('error');

        $this->assertMatchesRegularExpression(
            '/review-keywords:rescan/',
            $flash,
            "The admin must be pointed at the artisan command, got: '{$flash}'."
        );

        Queue::assertNothingPushed();

        $this->assertSame(
            0,
            (int) $this->settingValue('review_keywords_rescan_running'),
            'A refused rescan must not claim the lease.'
        );

        $this->assertSame('published', $matching->fresh()->status);
        $this->assertSame([], $this->reviewLogsFor($matching->id));
    }

    public function test_the_admin_endpoint_refuses_to_queue_a_second_rescan_while_the_unique_lock_is_held(): void
    {
        if (! Route::has('admin.keywords.rescan')) {
            $this->markTestSkipped('The admin rescan route is not registered.');
        }

        $admin = $this->adminUser();

        Queue::fake();

        $lock = app(CacheRepository::class)->lock(UniqueLock::getKey(new RescanReviewKeywords), 300);

        $this->assertTrue($lock->get(), 'The unique lock must be held for this test to mean anything.');

        $response = $this->withQueueDefault('database', fn () => $this->actingAs($admin, 'admin')
            ->post(route('admin.keywords.rescan'), ['confirm' => 'rescan', 'locale' => 'en']));

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertMatchesRegularExpression('/already (queued|running)/i', (string) session('error'));

        Queue::assertNothingPushed();
    }

    public function test_the_failure_handler_clears_the_running_flag(): void
    {
        $stamp = time();

        $this->writeSetting('review_keywords_rescan_running', 1);
        $this->writeSetting('review_keywords_rescan_started_at', $stamp);

        (new RescanReviewKeywords(null, 0, $stamp))->failed(new RuntimeException('boom'));

        Cache::flush();

        Settings::forget();

        ReviewKeywords::flushCache();

        $this->assertSame(
            0,
            (int) Settings::get('review_keywords_rescan_running', 0, 'moderation'),
            'A failed run must release the rescan lease.'
        );

        $this->assertSame(
            0,
            (int) Settings::get('review_keywords_rescan_started_at', 0, 'moderation'),
            'A failed run must clear the started-at stamp of the lease.'
        );

        $this->assertDatabaseHas('settings', [
            'group' => 'moderation',
            'key' => 'review_keywords_rescan_running',
            'value' => '0',
        ]);

        $this->assertDatabaseHas('settings', [
            'group' => 'moderation',
            'key' => 'review_keywords_rescan_started_at',
            'value' => '0',
        ]);
    }

    public function test_the_failure_handler_leaves_a_lease_it_does_not_own(): void
    {
        $stamp = time();

        $this->writeSetting('review_keywords_rescan_running', 1);
        $this->writeSetting('review_keywords_rescan_started_at', $stamp);

        (new RescanReviewKeywords(null, 0, $stamp - 60))->failed(new RuntimeException('boom'));

        $this->assertSame(
            1,
            (int) $this->settingValue('review_keywords_rescan_running'),
            'A failed run must not release a lease that belongs to another run.'
        );

        $this->assertSame(
            $stamp,
            (int) $this->settingValue('review_keywords_rescan_started_at'),
            'A failed run must not clear the lease stamp of another run.'
        );
    }

    public function test_the_lease_is_not_released_by_another_run(): void
    {
        $this->writeSetting('review_keywords_rescan_running', 1);
        $this->writeSetting('review_keywords_rescan_started_at', 1700000000);

        RescanReviewKeywords::releaseLease(1600000000);

        $this->assertSame(
            1,
            (int) $this->settingValue('review_keywords_rescan_running'),
            'A run must not clear the lease of another run.'
        );

        $this->assertSame(
            1700000000,
            (int) $this->settingValue('review_keywords_rescan_started_at'),
            'A run must not clear the lease stamp of another run.'
        );
    }

    public function test_the_admin_endpoint_queues_a_new_rescan_after_a_failed_run(): void
    {
        if (! Route::has('admin.keywords.rescan')) {
            $this->markTestSkipped('The admin rescan route is not registered.');
        }

        $this->writeSetting('review_keywords_rescan_running', 1);
        $this->writeSetting('review_keywords_rescan_started_at', time());

        (new RescanReviewKeywords(null, 0, (int) Settings::get('review_keywords_rescan_started_at', 0, 'moderation')))->failed(new RuntimeException('boom'));

        Queue::fake();

        $admin = $this->adminUser();

        $response = $this->withQueueDefault('database', fn () => $this->actingAs($admin, 'admin')
            ->post(route('admin.keywords.rescan'), ['confirm' => 'rescan', 'locale' => 'en']));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        Queue::assertPushed(RescanReviewKeywords::class);
    }

    public function test_the_job_unique_id_is_unique_per_segment(): void
    {
        $first = (new RescanReviewKeywords(null, 0))->uniqueId();
        $second = (new RescanReviewKeywords(null, 1))->uniqueId();

        $this->assertIsString($first);
        $this->assertIsString($second);
        $this->assertNotSame('', $first);
        $this->assertNotSame('', $second);

        $this->assertNotSame(
            $first,
            $second,
            'Each segment needs its own unique lock, otherwise a continuation is dropped.'
        );

        $this->assertStringContainsString('0', $first);
        $this->assertStringContainsString('1', $second);

        $this->assertSame($first, (new RescanReviewKeywords(null, 0))->uniqueId());
        $this->assertSame($second, (new RescanReviewKeywords(null, 1))->uniqueId());
    }

    public function test_the_keywords_page_shows_the_last_rescan_status(): void
    {
        $this->writeSetting('review_keywords_rescan_status', 'stopped early — scanned 5, held 1');

        $admin = $this->adminUser();

        $response = $this->actingAs($admin, 'admin')->get(route('admin.keywords'));

        $response->assertOk();

        $html = (string) $response->getContent();

        $this->assertMatchesRegularExpression(
            '/stopped early.*scanned 5.*held 1/s',
            $html,
            'The keywords page must show the last rescan status next to the rescan button.'
        );
    }

    public function test_the_keywords_page_shows_no_rescan_status_when_there_is_none(): void
    {
        $this->writeSetting('review_keywords_rescan_status', '');

        $admin = $this->adminUser();

        $response = $this->actingAs($admin, 'admin')->get(route('admin.keywords'));

        $response->assertOk();

        $html = (string) $response->getContent();

        $this->assertStringNotContainsString('stopped early', $html);
        $this->assertStringNotContainsString('scanned 5', $html);
        $this->assertStringNotContainsString('held 1', $html);
    }

    public function test_the_rescan_stops_at_a_chunk_boundary_and_resumes_after_the_cursor(): void
    {
        $chunkSize = ReviewKeywordRescan::$chunkSize;

        ReviewKeywordRescan::$chunkSize = 2;

        try {
            $first = $this->createPetition('en', 'Keep the local library open');
            $this->renamePetition($first, 'Stop betting advertising');

            $second = $this->createPetition('en', 'Support the local schools');
            $third = $this->createPetition('en', 'Protect the neighbourhood park');
            $fourth = $this->createPetition('en', 'Improve the bus timetable');

            $progress = [];

            $result = (new ReviewKeywordRescan)->run(
                3,
                ReviewKeywordRescan::DEFAULT_MAX_SECONDS,
                0,
                function (int $lastId) use (&$progress): void {
                    $progress[] = $lastId;
                }
            );

            $this->assertSame(3, $result['scanned']);
            $this->assertSame(1, $result['held']);
            $this->assertSame($third->id, $result['lastId']);
            $this->assertFalse($result['complete']);
            $this->assertSame([$second->id, $third->id], $progress);

            $this->assertSame('draft', $first->fresh()->status);
            $this->assertCount(1, $this->reviewLogsFor($first->id));

            $resumed = (new ReviewKeywordRescan)->run(
                null,
                ReviewKeywordRescan::DEFAULT_MAX_SECONDS,
                $result['lastId']
            );

            $this->assertSame(1, $resumed['scanned']);
            $this->assertSame(0, $resumed['held']);
            $this->assertSame($fourth->id, $resumed['lastId']);
            $this->assertTrue($resumed['complete']);

            $this->assertCount(1, $this->reviewLogsFor($first->id));
            $this->assertSame('published', $fourth->fresh()->status);
        } finally {
            ReviewKeywordRescan::$chunkSize = $chunkSize;
        }
    }

    public function test_the_job_failure_handler_records_the_error(): void
    {
        Log::spy();

        (new RescanReviewKeywords)->failed(new RuntimeException('the rescan exploded'));

        Log::shouldHaveReceived('error')->once();
    }

    public function test_a_viewer_without_the_edit_permission_cannot_trigger_the_rescan(): void
    {
        if (! Route::has('admin.keywords.rescan')) {
            $this->markTestSkipped('The admin rescan route is not registered.');
        }

        $matching = $this->createPetition('en', 'Keep the local library open');
        $this->renamePetition($matching, 'Stop betting advertising');

        $viewer = $this->spamViewer();

        $response = $this->actingAs($viewer, 'admin')
            ->post(route('admin.keywords.rescan'), ['confirm' => 'rescan', 'locale' => 'en']);

        $response->assertForbidden();

        $this->assertSame('published', $matching->fresh()->status);
        $this->assertSame([], $this->reviewLogsFor($matching->id));
    }

    public function test_the_rescan_is_refused_without_the_petitions_edit_permission(): void
    {
        if (! Route::has('admin.keywords.rescan')) {
            $this->markTestSkipped('The admin rescan route is not registered.');
        }

        $this->assertContains(
            'permission:petitions,edit',
            $this->rescanRouteMiddleware(),
            'The admin.keywords.rescan route must declare the permission:petitions,edit middleware.'
        );

        $matching = $this->createPetition('en', 'Keep the local library open');
        $this->renamePetition($matching, 'Stop betting advertising');

        $editor = $this->spamEditor();

        $response = $this->actingAs($editor, 'admin')
            ->post(route('admin.keywords.rescan'), ['confirm' => 'rescan', 'locale' => 'en']);

        $response->assertForbidden();

        $this->assertSame('published', $matching->fresh()->status);
        $this->assertSame([], $this->reviewLogsFor($matching->id));
    }
}
