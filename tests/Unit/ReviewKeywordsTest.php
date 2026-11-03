<?php

namespace Tests\Unit;

use App\Support\ReviewKeywords;
use App\Support\Settings;
use Illuminate\Cache\ArrayStore;
use Illuminate\Cache\Repository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\TestCase;

class ReviewKeywordsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();

        ReviewKeywords::flushCache();
    }

    protected function tearDown(): void
    {
        $this->restoreCacheStore();

        Cache::flush();

        Settings::forget();

        ReviewKeywords::flushCache();

        parent::tearDown();
    }

    private function writeRow(string $key, string $value): void
    {
        DB::table('settings')->insert([
            'group' => 'moderation',
            'key' => $key,
            'value' => $value,
            'type' => 'string',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Cache::flush();

        Settings::forget();

        ReviewKeywords::flushCache();
    }

    private function breakCacheForget(): void
    {
        Cache::extend('array', function ($app, $config) {
            return new class(new ArrayStore, ['store' => 'array']) extends Repository
            {
                public function forget($key)
                {
                    throw new RuntimeException('the cache backend is down');
                }
            };
        });

        Cache::forgetDriver();
    }

    private function restoreCacheStore(): void
    {
        Cache::extend('array', function ($app, $config) {
            return new Repository(new ArrayStore);
        });

        Cache::forgetDriver();
    }

    private function settingValue(string $key, string $group = 'moderation'): ?string
    {
        $value = DB::table('settings')
            ->where('group', $group)
            ->where('key', $key)
            ->value('value');

        return $value === null ? null : (string) $value;
    }

    public static function malformedDisabledLists(): array
    {
        return [
            'not a list' => ['not-a-list'],
            'a json object' => ['{"a":1}'],
            'a json map' => ['{"a":"b"}'],
        ];
    }

    public function test_parse_terms_reads_plain_and_concept_lines(): void
    {
        $entries = ReviewKeywords::parseTerms("cuvant de test\nalt termen :: cu alt concept\nthird term");

        $this->assertSame([
            ['term' => 'cuvant de test', 'concept' => 'cuvant de test'],
            ['term' => 'alt termen', 'concept' => 'cu alt concept'],
            ['term' => 'third term', 'concept' => 'third term'],
        ], $entries);
    }

    public function test_parse_terms_skips_empty_lines(): void
    {
        $entries = ReviewKeywords::parseTerms("\n   \n\t\n  \n");

        $this->assertSame([], $entries);
    }

    public function test_parse_terms_keeps_the_first_spelling_of_a_repeated_term(): void
    {
        $entries = ReviewKeywords::parseTerms("Cuvant\ncuvant\nCUVANT");

        $this->assertCount(1, $entries);
        $this->assertSame('Cuvant', $entries[0]['term']);
        $this->assertSame('cuvant', $entries[0]['concept']);
    }

    public function test_parse_terms_caps_the_term_count(): void
    {
        $text = implode("\n", array_map(
            fn (int $i): string => "term{$i}",
            range(1, ReviewKeywords::MAX_TERMS + 10)
        ));

        $entries = ReviewKeywords::parseTerms($text);

        $this->assertCount(ReviewKeywords::MAX_TERMS, $entries);
        $this->assertSame('term1', $entries[0]['term']);
        $this->assertSame('term'.ReviewKeywords::MAX_TERMS, $entries[ReviewKeywords::MAX_TERMS - 1]['term']);
    }

    public function test_the_term_and_concept_length_caps_are_enforced(): void
    {
        $entries = ReviewKeywords::parseTerms(str_repeat('a', 300).' :: '.str_repeat('b', 300));

        $this->assertCount(1, $entries);
        $this->assertSame(str_repeat('a', ReviewKeywords::MAX_TERM_LENGTH), $entries[0]['term']);
        $this->assertSame(str_repeat('b', ReviewKeywords::MAX_CONCEPT_LENGTH), $entries[0]['concept']);
        $this->assertSame(ReviewKeywords::MAX_TERM_LENGTH, mb_strlen($entries[0]['term']));
        $this->assertSame(ReviewKeywords::MAX_CONCEPT_LENGTH, mb_strlen($entries[0]['concept']));

        $expected = [[
            'term' => str_repeat('a', ReviewKeywords::MAX_TERM_LENGTH),
            'concept' => str_repeat('b', ReviewKeywords::MAX_CONCEPT_LENGTH),
        ]];

        ReviewKeywords::saveLocale('en', $entries);

        $this->assertSame(
            $expected,
            ReviewKeywords::entriesFor('en'),
            'The saved locale must hold the truncated entry.'
        );

        $this->assertSame(
            $expected,
            json_decode((string) $this->settingValue('review_keywords_en'), true),
            'The settings row must hold the truncated entry.'
        );
    }

    public function test_format_terms_is_the_inverse_of_parse_terms(): void
    {
        $entries = [
            ['term' => 'cuvant de test', 'concept' => 'cuvant de test'],
            ['term' => 'alt termen', 'concept' => 'grup comun'],
        ];

        $formatted = ReviewKeywords::formatTerms($entries);

        $this->assertSame("cuvant de test\nalt termen :: grup comun", $formatted);
        $this->assertSame($entries, ReviewKeywords::parseTerms($formatted));
    }

    public function test_format_terms_drops_the_concept_when_it_matches_the_term(): void
    {
        $formatted = ReviewKeywords::formatTerms([
            ['term' => 'Cuvant de test', 'concept' => 'cuvant de test'],
        ]);

        $this->assertSame('Cuvant de test', $formatted);
        $this->assertSame(
            [['term' => 'Cuvant de test', 'concept' => 'cuvant de test']],
            ReviewKeywords::parseTerms($formatted)
        );
    }

    public function test_a_stored_entry_with_an_explicit_concept_is_grouped(): void
    {
        ReviewKeywords::saveLocale('en', [
            ['term' => 'special thing', 'concept' => 'shared concept'],
        ]);

        ReviewKeywords::saveLocale('ro', [
            ['term' => 'lucru special', 'concept' => 'shared concept'],
        ]);

        $concepts = ReviewKeywords::concepts();

        $this->assertArrayHasKey('shared concept', $concepts);
        $this->assertSame([
            'en' => ['special thing'],
            'ro' => ['lucru special'],
        ], $concepts['shared concept']);

        $this->assertSame([
            ['term' => 'special thing', 'concept' => 'shared concept'],
        ], ReviewKeywords::entriesFor('en'));
    }

    public function test_a_disabled_concept_leaves_the_catalogue_and_the_matcher(): void
    {
        ReviewKeywords::saveLocale('en', [
            ['term' => 'alpha term', 'concept' => 'shared concept'],
            ['term' => 'beta term', 'concept' => 'shared concept'],
        ]);

        $this->assertContains('alpha term', ReviewKeywords::catalogue()['en']);
        $this->assertNotEmpty(ReviewKeywords::matches('alpha term'));

        ReviewKeywords::setConceptEnabled('shared concept', false);

        $this->assertContains('shared concept', ReviewKeywords::disabledConcepts());
        $this->assertNotContains('alpha term', ReviewKeywords::catalogue()['en']);
        $this->assertNotContains('beta term', ReviewKeywords::catalogue()['en']);
        $this->assertEmpty(ReviewKeywords::matches('alpha term'));
        $this->assertEmpty(ReviewKeywords::matches('beta term'));

        $entries = ReviewKeywords::entriesFor('en');

        $this->assertCount(2, $entries);
        $this->assertSame('alpha term', $entries[0]['term']);
        $this->assertSame('shared concept', $entries[0]['concept']);

        ReviewKeywords::setConceptEnabled('shared concept', true);

        $this->assertNotContains('shared concept', ReviewKeywords::disabledConcepts());
        $this->assertContains('alpha term', ReviewKeywords::catalogue()['en']);
        $this->assertNotEmpty(ReviewKeywords::matches('alpha term'));
    }

    #[DataProvider('malformedDisabledLists')]
    public function test_a_malformed_disabled_list_is_never_overwritten(string $stored): void
    {
        $this->writeRow('review_keywords_disabled', $stored);

        $result = ReviewKeywords::setConceptEnabled('some concept', true);

        $this->assertFalse(
            $result,
            'A malformed disabled list must refuse the concept write.'
        );

        $this->assertSame(
            $stored,
            $this->settingValue('review_keywords_disabled'),
            'The malformed value must survive the refused write.'
        );

        $this->assertNotContains(
            'some concept',
            ReviewKeywords::disabledConcepts(),
            'A refused write must not enable the concept.'
        );
    }

    public function test_a_valid_disabled_list_is_updated_by_a_concept_write(): void
    {
        $this->writeRow('review_keywords_disabled', json_encode(['other concept']));

        $result = ReviewKeywords::setConceptEnabled('some concept', false);

        $this->assertTrue(
            $result,
            'A valid list must accept the concept write.'
        );

        $this->assertSame(
            ['other concept', 'some concept'],
            json_decode((string) $this->settingValue('review_keywords_disabled'), true),
            'The stored list must hold the disabled concept.'
        );

        $this->assertContains('some concept', ReviewKeywords::disabledConcepts());

        $enabled = ReviewKeywords::setConceptEnabled('some concept', true);

        $this->assertTrue(
            $enabled,
            'A valid list must accept the concept write.'
        );

        $this->assertSame(
            ['other concept'],
            json_decode((string) $this->settingValue('review_keywords_disabled'), true),
            'The stored list must drop the re-enabled concept.'
        );

        $this->assertNotContains('some concept', ReviewKeywords::disabledConcepts());
    }

    public function test_a_legacy_stored_row_is_read_as_entries(): void
    {
        $this->writeRow('review_keywords_ro', json_encode(['cuvant de test', 'alt termen']));

        $overrides = ReviewKeywords::overrides();

        $this->assertArrayHasKey('ro', $overrides);
        $this->assertSame([
            ['term' => 'cuvant de test', 'concept' => 'cuvant de test'],
            ['term' => 'alt termen', 'concept' => 'alt termen'],
        ], $overrides['ro']);
        $this->assertSame(['cuvant de test', 'alt termen'], ReviewKeywords::catalogue()['ro']);
        $this->assertNotEmpty(ReviewKeywords::matches('cuvant de test'));
    }

    public function test_a_failing_cache_does_not_break_the_save_and_still_serves_the_fresh_list(): void
    {
        $stale = ['review_keywords_en' => json_encode([['term' => 'stale term', 'concept' => 'stale term']])];

        $this->writeRow('review_keywords_en', $stale['review_keywords_en']);

        $this->assertContains('stale term', ReviewKeywords::catalogue()['en']);

        $this->breakCacheForget();

        $failure = null;

        try {
            ReviewKeywords::saveLocale('en', [['term' => 'fresh term', 'concept' => 'fresh term']]);
        } catch (\Throwable $e) {
            $failure = $e;
        }

        $this->assertNull($failure, 'A failing cache must not break the keyword save.');

        $this->assertContains('fresh term', ReviewKeywords::catalogue()['en']);
        $this->assertNotContains('stale term', ReviewKeywords::catalogue()['en']);
    }

    public function test_settings_forget_alone_refreshes_the_catalogue(): void
    {
        $this->writeRow('review_keywords_en', json_encode([['term' => 'old term', 'concept' => 'old term']]));
        $this->assertContains('old term', ReviewKeywords::catalogue()['en']);

        DB::table('settings')->where('key', 'review_keywords_en')
            ->update(['value' => json_encode([['term' => 'new term', 'concept' => 'new term']])]);
        Settings::forget();

        $this->assertContains('new term', ReviewKeywords::catalogue()['en']);
        $this->assertNotContains('old term', ReviewKeywords::catalogue()['en']);
    }
}
