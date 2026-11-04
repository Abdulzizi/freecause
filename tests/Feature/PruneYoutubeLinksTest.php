<?php

namespace Tests\Feature;

use App\Models\Petition;
use App\Models\User;
use Database\Seeders\LanguageSeeder;
use Database\Seeders\UserLevelSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PruneYoutubeLinksTest extends TestCase
{
    use RefreshDatabase;

    private const STATUSES = [
        'okokokokok1' => 200, 'gonegonego4' => 404, 'privprivp03' => 403, 'badreqbad40' => 400,
        'unauthun401' => 401, 'servererr50' => 500, 'limitlimi29' => 429, 'editedwhile' => 404,
    ];

    private string $backup;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([UserLevelSeeder::class, LanguageSeeder::class]);

        // A fixed clock gives the backup file a known name that no real run would use.
        $this->travelTo('2000-01-01 00:00:00');
        $this->backup = storage_path('app/prune-youtube-20000101-000000.tsv');

        Http::fake(function (Request $request) {
            parse_str((string) parse_url($request->url(), PHP_URL_QUERY), $query);
            $id = substr((string) ($query['url'] ?? ''), -11);

            if ($id === 'downdowndow') {
                return Http::failedConnection()($request);
            }
            if ($id === 'editedwhile') {
                DB::table('petitions')->where('youtube_url', 'editedwhile')->update(['youtube_url' => 'okokokokok1']);
            }

            return Http::response('', self::STATUSES[$id] ?? 200);
        });
    }

    protected function tearDown(): void
    {
        @unlink($this->backup);

        parent::tearDown();
    }

    private function petition(?string $youtube): int
    {
        return Petition::factory()->create(['youtube_url' => $youtube])->id;
    }

    private function prune(array $options = [])
    {
        return $this->artisan('petitions:prune-youtube', ['--sleep' => 0] + $options);
    }

    private function youtube(int $id): ?string
    {
        return Petition::query()->whereKey($id)->value('youtube_url');
    }

    private function oembedIds(): array
    {
        return Http::recorded()->map(fn ($pair) => substr(urldecode($pair[0]->url()), -11))->all();
    }

    public function test_it_clears_junk_and_unavailable_videos_and_keeps_the_rest(): void
    {
        $ids = [];
        foreach (['okokokokok1', 'https://youtu.be/gonegonego4', 'privprivp03', 'badreqbad40', 'unauthun401', 'servererr50', 'downdowndow', 'NvqLf7Bpnk', null] as $value) {
            $ids[$value ?? 'null'] = $this->petition($value);
        }
        DB::table('petitions')->where('id', $ids['privprivp03'])->update(['updated_at' => '1999-01-01 00:00:00']);
        $before = (array) DB::table('petitions')->where('id', $ids['privprivp03'])->first();

        $gone = $ids['https://youtu.be/gonegonego4'];
        $this->prune()
            ->expectsOutput("cleared\tunavailable\t{$gone}\t\"https://youtu.be/gonegonego4\"")
            ->expectsOutput("cleared\tjunk\t{$ids['NvqLf7Bpnk']}\t\"NvqLf7Bpnk\"")
            ->expectsOutput('kept 1, cleared junk 1, cleared unavailable 4, unchecked 2')
            ->expectsOutput('Backup of cleared values: '.$this->backup)
            ->assertSuccessful();

        foreach (['okokokokok1', 'servererr50', 'downdowndow'] as $kept) {
            $this->assertSame($kept, $this->youtube($ids[$kept]));
        }
        foreach (['https://youtu.be/gonegonego4', 'privprivp03', 'badreqbad40', 'unauthun401', 'NvqLf7Bpnk'] as $cleared) {
            $this->assertNull($this->youtube($ids[$cleared]));
        }
        $this->assertNotContains('NvqLf7Bpnk', $this->oembedIds());
        $this->assertCount(7, $this->oembedIds());
        $this->assertSame(array_merge($before, ['youtube_url' => null]), (array) DB::table('petitions')->where('id', $ids['privprivp03'])->first());

        $lines = file($this->backup, FILE_IGNORE_NEW_LINES);
        $this->assertCount(5, $lines);
        $this->assertContains("cleared\tunavailable\t{$gone}\t\"https://youtu.be/gonegonego4\"", $lines);
        $this->assertContains("cleared\tjunk\t{$ids['NvqLf7Bpnk']}\t\"NvqLf7Bpnk\"", $lines);
    }

    public function test_it_processes_rows_past_the_first_chunk(): void
    {
        $user = User::factory()->create();
        Petition::factory()->for($user)->count(205)
            ->sequence(fn ($sequence) => ['youtube_url' => $sequence->index % 2 ? 'okokokokok1' : 'junk'.$sequence->index])
            ->create();

        $this->prune()->expectsOutput('kept 102, cleared junk 103, cleared unavailable 0, unchecked 0')->assertSuccessful();

        $this->assertSame(0, Petition::where('youtube_url', 'like', 'junk%')->count());
        $this->assertSame(102, Petition::where('youtube_url', 'okokokokok1')->count());
    }

    public function test_it_stops_at_the_first_rate_limit_and_fails(): void
    {
        $junk = $this->petition('NvqLf7Bpnk');
        $ok = $this->petition('okokokokok1');
        $limited = $this->petition('limitlimi29');
        $after = $this->petition('gonegonego4');

        $this->prune()
            ->expectsOutput('Rate limited by YouTube (429): stopped early, rerun later. Rows already cleared stay cleared.')
            ->expectsOutput('kept 1, cleared junk 1, cleared unavailable 0, unchecked 0')
            ->assertFailed();

        $this->assertSame(['okokokokok1', 'limitlimi29'], $this->oembedIds());
        $this->assertNull($this->youtube($junk));
        $this->assertSame('okokokokok1', $this->youtube($ok));
        $this->assertSame('limitlimi29', $this->youtube($limited));
        $this->assertSame('gonegonego4', $this->youtube($after));
    }

    public function test_a_link_edited_during_the_run_is_not_wiped(): void
    {
        $id = $this->petition('editedwhile');

        $this->prune()->expectsOutput('kept 0, cleared junk 0, cleared unavailable 0, unchecked 1')->assertSuccessful();

        $this->assertSame('okokokokok1', $this->youtube($id));
        $this->assertFileDoesNotExist($this->backup);
    }

    public function test_an_unwritable_backup_location_fails_before_any_row_changes(): void
    {
        $junk = $this->petition('NvqLf7Bpnk');
        $this->app->useStoragePath('/nonexistent-freecause-storage');

        $this->prune()
            ->expectsOutput('Cannot open the backup file /nonexistent-freecause-storage/app/prune-youtube-20000101-000000.tsv; nothing was changed.')
            ->assertFailed();

        $this->assertSame('NvqLf7Bpnk', $this->youtube($junk));
        $this->assertSame([], $this->oembedIds());
    }

    public function test_dry_run_reports_but_changes_nothing(): void
    {
        $junk = $this->petition('NvqLf7Bpnk');
        $gone = $this->petition('gonegonego4');

        $this->prune(['--dry-run' => true])
            ->expectsOutput("would clear\tjunk\t{$junk}\t\"NvqLf7Bpnk\"")
            ->expectsOutput("would clear\tunavailable\t{$gone}\t\"gonegonego4\"")
            ->expectsOutput('dry run: kept 0, cleared junk 1, cleared unavailable 1, unchecked 0')
            ->assertSuccessful();

        $this->assertSame('NvqLf7Bpnk', $this->youtube($junk));
        $this->assertSame('gonegonego4', $this->youtube($gone));
        $this->assertFileDoesNotExist($this->backup);
    }
}
