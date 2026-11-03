<?php

namespace Tests\Unit;

use App\Models\Setting;
use App\Support\Settings;
use Illuminate\Contracts\Queue\Job;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SettingsTest extends TestCase
{
    use RefreshDatabase;

    private const CACHE_KEY = 'settings:all';

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
    }

    public function test_get_returns_default_for_missing_setting(): void
    {
        $this->assertNull(Settings::get('does_not_exist'));
        $this->assertSame('fallback', Settings::get('does_not_exist', 'fallback'));
    }

    public function test_set_then_get_round_trips(): void
    {
        Settings::set('site_name', 'FreeCause');

        $this->assertSame('FreeCause', Settings::get('site_name'));
    }

    public function test_set_and_get_are_group_scoped(): void
    {
        Settings::set('color', 'red', 'theme');

        $this->assertSame('red', Settings::get('color', null, 'theme'));
        $this->assertNull(Settings::get('color'));
    }

    public function test_get_populates_single_batch_key_with_grouped_layout(): void
    {
        Setting::create(['group' => 'global', 'key' => 'a', 'value' => '1', 'type' => 'string']);
        Setting::create(['group' => 'global', 'key' => 'b', 'value' => '2', 'type' => 'string']);
        Setting::create(['group' => 'theme', 'key' => 'color', 'value' => 'red', 'type' => 'string']);

        Settings::get('a');

        $cached = Cache::get(self::CACHE_KEY);

        $this->assertIsArray($cached);
        $this->assertSame('1', $cached['global']['a'] ?? null);
        $this->assertSame('2', $cached['global']['b'] ?? null);
        $this->assertSame('red', $cached['theme']['color'] ?? null);
        $this->assertArrayNotHasKey('missing', $cached['theme'] ?? []);
    }

    public function test_second_get_is_served_from_cache_without_a_db_query(): void
    {
        Setting::create(['group' => 'global', 'key' => 'a', 'value' => '1', 'type' => 'string']);

        Settings::get('a');

        DB::enableQueryLog();

        try {
            Settings::get('a');
            $this->assertCount(0, DB::getQueryLog());
        } finally {
            DB::disableQueryLog();
        }
    }

    public function test_set_invalidates_the_cached_batch(): void
    {
        Settings::set('flag', 'one');
        $this->assertSame('one', Settings::get('flag'));

        Settings::set('flag', 'two');
        $this->assertSame('two', Settings::get('flag'));
    }

    public function test_get_decodes_json_arrays(): void
    {
        Settings::set('list', ['x', 'y']);

        $this->assertSame(['x', 'y'], Settings::get('list'));
    }

    public function test_get_decodes_json_bool_and_int(): void
    {
        Settings::set('enabled', true);
        Settings::set('count', 5);

        $this->assertTrue(Settings::get('enabled'));
        $this->assertSame(5, Settings::get('count'));
    }

    public function test_get_leaves_plain_and_json_strings_untouched(): void
    {
        Setting::create(['group' => 'global', 'key' => 'plain', 'value' => 'hello', 'type' => 'string']);

        Setting::create(['group' => 'global', 'key' => 'quoted', 'value' => '"hello"', 'type' => 'string']);

        $this->assertSame('hello', Settings::get('plain'));
        $this->assertSame('"hello"', Settings::get('quoted'));
    }

    public function test_forget_clears_the_batch_and_reloads_from_database(): void
    {
        Settings::set('greeting', 'hi');
        $this->assertSame('hi', Settings::get('greeting'));

        Setting::where('key', 'greeting')->update(['value' => 'bye']);
        $this->assertSame('hi', Settings::get('greeting'));

        Settings::forget();

        $this->assertSame('bye', Settings::get('greeting'));
    }

    public function test_cache_failure_falls_back_to_default(): void
    {
        Cache::shouldReceive('remember')->once()->andThrow(new \RuntimeException('cache down'));

        $this->assertSame('fallback', Settings::get('anything', 'fallback'));
    }

    public function test_repeated_gets_hit_cache_once_per_request(): void
    {
        Settings::set('a', '1');
        Settings::set('b', '2');

        Cache::spy();
        Cache::shouldReceive('remember')->once()->andReturn(['global' => ['a' => '1', 'b' => '2']]);

        $this->assertSame(1, Settings::get('a'));
        $this->assertSame(2, Settings::get('b'));
    }

    public function test_job_processing_flushes_stale_memo(): void
    {
        Settings::set('site_name', 'old');
        $this->assertSame('old', Settings::get('site_name'));

        Setting::where('key', 'site_name')->update(['value' => 'new']);
        Cache::forget(self::CACHE_KEY);
        $this->assertSame('old', Settings::get('site_name'));

        event(new JobProcessing('sync', $this->createMock(Job::class)));

        $this->assertSame('new', Settings::get('site_name'));
    }

    public function test_get_falls_back_to_db_when_cache_throws(): void
    {
        Setting::create(['group' => 'global', 'key' => 'a', 'value' => 'db', 'type' => 'string']);
        Settings::flushMemo();
        Cache::shouldReceive('remember')->andThrow(new \RuntimeException('cache down'));

        DB::enableQueryLog();

        try {
            $this->assertSame('db', Settings::get('a'));
            $this->assertSame('db', Settings::get('a'));
            $this->assertCount(1, DB::getQueryLog());
        } finally {
            DB::disableQueryLog();
        }
    }
}
