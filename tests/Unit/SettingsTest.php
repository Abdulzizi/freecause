<?php

namespace Tests\Unit;

use App\Models\Setting;
use App\Support\Settings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SettingsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Must match Settings::CACHE_KEY (private constant).
     */
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

        $this->assertSame(
            [
                'global' => ['a' => '1', 'b' => '2'],
                'theme' => ['color' => 'red'],
            ],
            Cache::get(self::CACHE_KEY)
        );
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
        // Plain non-JSON string is returned verbatim.
        Setting::create(['group' => 'global', 'key' => 'plain', 'value' => 'hello', 'type' => 'string']);

        // A JSON-encoded string is returned raw (byte-compatible with the legacy get()).
        Setting::create(['group' => 'global', 'key' => 'quoted', 'value' => '"hello"', 'type' => 'string']);

        $this->assertSame('hello', Settings::get('plain'));
        $this->assertSame('"hello"', Settings::get('quoted'));
    }

    public function test_forget_clears_the_batch_and_reloads_from_database(): void
    {
        Settings::set('greeting', 'hi');
        $this->assertSame('hi', Settings::get('greeting'));

        // Mutate the DB behind the cached snapshot: reads stay stale.
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
}
