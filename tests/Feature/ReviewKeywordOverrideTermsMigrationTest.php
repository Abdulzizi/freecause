<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ReviewKeywordOverrideTermsMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_test_terms_are_removed_from_review_keyword_overrides(): void
    {
        $setting = fn (string $key, string $value) => [
            'group' => 'moderation',
            'key' => $key,
            'value' => $value,
            'type' => 'json',
        ];

        $english = json_encode([
            ['term' => 'adulzi', 'concept' => 'test'],
            ['term' => 'keyword', 'concept' => 'test'],
            ['term' => 'casino', 'concept' => 'gambling'],
        ]);
        $disabled = json_encode([['term' => 'adulzi', 'concept' => 'test']]);
        $rescan = json_encode([['term' => 'keyword', 'concept' => 'test']]);
        $invalid = '{not json';

        DB::table('settings')->insert([
            $setting('review_keywords_en', $english),
            $setting('review_keywords_disabled', $disabled),
            $setting('review_keywords_rescan_en', $rescan),
            $setting('review_keywords_de', $invalid),
        ]);

        Cache::put('settings:all', 'stale');
        Cache::put('review_keywords:rows', 'stale');

        $migration = require database_path('migrations/2026_11_02_000001_remove_test_terms_from_review_keyword_overrides.php');
        $migration->up();

        $this->assertSame(
            [['term' => 'casino', 'concept' => 'gambling']],
            json_decode(DB::table('settings')->where('key', 'review_keywords_en')->value('value'), true)
        );
        $this->assertSame($disabled, DB::table('settings')->where('key', 'review_keywords_disabled')->value('value'));
        $this->assertSame($rescan, DB::table('settings')->where('key', 'review_keywords_rescan_en')->value('value'));
        $this->assertSame($invalid, DB::table('settings')->where('key', 'review_keywords_de')->value('value'));
        $this->assertNull(Cache::get('settings:all'));
        $this->assertNull(Cache::get('review_keywords:rows'));
    }
}
