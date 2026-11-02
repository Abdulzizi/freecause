<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\CategoryTranslation;
use App\Models\Language;
use App\Models\Permission;
use App\Models\Petition;
use App\Models\User;
use App\Models\UserLevel;
use App\Support\Locale;
use App\Support\ReviewKeywords;
use App\Support\Settings;
use Database\Seeders\LanguageSeeder;
use Database\Seeders\UserLevelSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class AdminReviewKeywordsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();

        ReviewKeywords::flushCache();

        $this->seed([UserLevelSeeder::class, LanguageSeeder::class]);
    }

    private function levelWithPermissions(string $name, array $permissions): UserLevel
    {
        $level = UserLevel::create([
            'name' => $name,
            'visible_name' => $name,
            'is_system' => false,
        ]);

        foreach ($permissions as $module => $actions) {
            foreach ($actions as $action) {
                Permission::create([
                    'level_id' => $level->id,
                    'module' => $module,
                    'action' => $action,
                ]);
            }
        }

        return $level;
    }

    private function userWithLevel(UserLevel $level): User
    {
        return User::factory()->create([
            'level_id' => $level->id,
            'verified' => true,
        ]);
    }

    private function duplicateTerms(array $terms): array
    {
        $counts = array_count_values($terms);

        return array_keys(array_filter($counts, fn ($count) => $count > 1));
    }

    private function entry(string $term, ?string $concept = null): array
    {
        return ['term' => $term, 'concept' => $concept ?? $term];
    }

    private function createPetition(string $locale, string $title): Petition
    {
        $user = User::factory()->create(['verified' => true]);

        $category = Category::factory()->create(['is_active' => true]);

        CategoryTranslation::create([
            'category_id' => $category->id,
            'locale' => 'en',
            'name' => 'Test Category',
            'slug' => 'test-category',
        ]);

        $this->actingAs($user)->post("/{$locale}/create-petition", [
            'title' => $title,
            'description' => '<p>This is a test petition with enough content to pass validation and be accepted.</p>',
            'goal_signatures' => 100,
            'category_id' => $category->id,
        ])->assertRedirect();

        return Petition::latest('id')->firstOrFail();
    }

    private function warningText(string $html): string
    {
        preg_match_all(
            '/<(div|p|section|aside|span)\b(?=[^>]*(?:data-slot="alert"|class="[^"]*\bwarning\b))[^>]*>(.*?)<\/\1>/is',
            $html,
            $matches
        );

        return trim(strip_tags(implode(' ', $matches[2])));
    }

    private function conceptsTableHtml(string $html): string
    {
        preg_match_all('/<table\b.*?<\/table>/s', $html, $matches);

        foreach ($matches[0] as $table) {
            if (str_contains($table, '>concept</th>')) {
                return $table;
            }
        }

        return '';
    }

    private function settingValue(string $key, string $group = 'moderation'): ?string
    {
        $value = DB::table('settings')
            ->where('group', $group)
            ->where('key', $key)
            ->value('value');

        return $value === null ? null : (string) $value;
    }

    public function test_bundled_defaults_are_unchanged(): void
    {
        $this->assertNotEmpty(ReviewKeywords::matches('Stop casino betting promotions'));
        $this->assertContains('casino', ReviewKeywords::catalogue()['en']);
        $this->assertNotContains('casino', ReviewKeywords::catalogue()['ro']);
        $this->assertContains('pariuri', ReviewKeywords::translations()['ro']);
        $this->assertNotEmpty(ReviewKeywords::matches('pariuri'));
        $this->assertContains('pariuri', ReviewKeywords::catalogue()['ro']);
        $this->assertContains('casino', ReviewKeywords::all());
        $this->assertContains('pariuri', ReviewKeywords::all());

        $this->assertSame(55, count(ReviewKeywords::TERMS));

        $expected = [
            'da' => 46,
            'de' => 47,
            'el' => 54,
            'es' => 52,
            'fr' => 50,
            'it' => 50,
            'nl' => 49,
            'pl' => 55,
            'pt' => 53,
            'ro' => 52,
            'ru' => 55,
            'sv' => 50,
            'tr' => 51,
        ];

        $actual = array_map('count', ReviewKeywords::catalogue());

        unset($actual['en']);

        ksort($actual);

        $this->assertSame($expected, $actual);
    }

    public function test_bundled_lists_have_no_case_insensitive_duplicates(): void
    {
        $english = array_map(fn ($term) => mb_strtolower($term), ReviewKeywords::TERMS);

        $this->assertSame(
            [],
            $this->duplicateTerms($english),
            'The english term list must not contain case-insensitive duplicates.'
        );

        foreach (ReviewKeywords::catalogue() as $locale => $terms) {
            $lowered = array_map(fn ($term) => mb_strtolower($term), $terms);

            $this->assertSame(
                [],
                $this->duplicateTerms($lowered),
                "The {$locale} term list must not contain case-insensitive duplicates."
            );
        }
    }

    public function test_overriding_a_locale_replaces_its_bundled_terms(): void
    {
        ReviewKeywords::saveLocale('ro', ['cuvant de test']);

        $this->assertNotEmpty(ReviewKeywords::matches('cuvant de test'));
        $this->assertEmpty(ReviewKeywords::matches('pariuri'));
        $this->assertNotEmpty(ReviewKeywords::matches('casino'));
        $this->assertContains('cuvant de test', ReviewKeywords::catalogue()['ro']);
        $this->assertNotContains('pariuri', ReviewKeywords::catalogue()['ro']);
        $this->assertNotContains('pariuri', ReviewKeywords::all());
        $this->assertContains('cuvant de test', ReviewKeywords::all());
    }

    public function test_resetting_a_locale_restores_the_bundled_terms(): void
    {
        ReviewKeywords::saveLocale('ro', ['cuvant de test']);

        $this->assertEmpty(ReviewKeywords::matches('pariuri'));

        ReviewKeywords::resetLocale('ro');

        $this->assertNotEmpty(ReviewKeywords::matches('pariuri'));
        $this->assertContains('pariuri', ReviewKeywords::catalogue()['ro']);
        $this->assertEmpty(ReviewKeywords::overrides());
    }

    public function test_saved_overrides_are_persisted_in_settings(): void
    {
        ReviewKeywords::saveLocale('ro', ['a', 'b']);

        $expected = [$this->entry('a'), $this->entry('b')];

        $this->assertDatabaseHas('settings', [
            'group' => 'moderation',
            'key' => 'review_keywords_ro',
        ]);
        $this->assertSame($expected, Settings::get('review_keywords_ro', [], 'moderation'));
        $this->assertSame($expected, ReviewKeywords::overrides()['ro']);

        ReviewKeywords::resetLocale('ro');

        $this->assertDatabaseMissing('settings', [
            'group' => 'moderation',
            'key' => 'review_keywords_ro',
        ]);
        $this->assertSame([], Settings::get('review_keywords_ro', [], 'moderation'));
        $this->assertArrayNotHasKey('ro', ReviewKeywords::overrides());
    }

    public function test_a_malformed_stored_row_is_ignored(): void
    {
        DB::table('settings')->insert([
            'group' => 'moderation',
            'key' => 'review_keywords_ro',
            'value' => 'garbage',
            'type' => 'string',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Cache::flush();

        Settings::forget();

        ReviewKeywords::flushCache();

        $this->assertArrayNotHasKey('ro', ReviewKeywords::overrides());
        $this->assertNotEmpty(ReviewKeywords::matches('pariuri'));
    }

    public function test_a_stored_json_object_is_ignored(): void
    {
        DB::table('settings')->insert([
            'group' => 'moderation',
            'key' => 'review_keywords_ro',
            'value' => '{"a":"b"}',
            'type' => 'string',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Cache::flush();

        Settings::forget();

        ReviewKeywords::flushCache();

        $this->assertArrayNotHasKey('ro', ReviewKeywords::overrides());
        $this->assertNotEmpty(ReviewKeywords::matches('pariuri'));
    }

    public function test_a_stored_list_with_non_string_members_keeps_only_the_strings(): void
    {
        DB::table('settings')->insert([
            'group' => 'moderation',
            'key' => 'review_keywords_ro',
            'value' => '[1,true,"ok"]',
            'type' => 'string',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Cache::flush();

        Settings::forget();

        ReviewKeywords::flushCache();

        $overrides = ReviewKeywords::overrides();

        $this->assertArrayHasKey('ro', $overrides);
        $this->assertSame([$this->entry('ok')], $overrides['ro']);
    }

    public function test_saving_an_empty_list_keeps_an_explicit_empty_override(): void
    {
        ReviewKeywords::saveLocale('ro', ['cuvant de test']);

        $this->assertArrayHasKey('ro', ReviewKeywords::overrides());

        ReviewKeywords::saveLocale('ro', []);

        $overrides = ReviewKeywords::overrides();

        $this->assertArrayHasKey('ro', $overrides);
        $this->assertSame([], $overrides['ro']);
        $this->assertDatabaseHas('settings', [
            'group' => 'moderation',
            'key' => 'review_keywords_ro',
        ]);
        $this->assertSame([], Settings::get('review_keywords_ro', [], 'moderation'));
        $this->assertSame([], ReviewKeywords::catalogue()['ro']);
        $this->assertEmpty(ReviewKeywords::matches('pariuri'));
        $this->assertNotEmpty(ReviewKeywords::matches('casino'));

        ReviewKeywords::resetLocale('ro');

        $this->assertArrayNotHasKey('ro', ReviewKeywords::overrides());
        $this->assertDatabaseMissing('settings', [
            'group' => 'moderation',
            'key' => 'review_keywords_ro',
        ]);
        $this->assertNotEmpty(ReviewKeywords::matches('pariuri'));
    }

    public function test_a_locale_without_a_bundled_list_can_be_added(): void
    {
        $this->assertArrayNotHasKey('zh', ReviewKeywords::catalogue());

        ReviewKeywords::saveLocale('zh', ['zhterm']);

        $this->assertNotEmpty(ReviewKeywords::matches('zhterm'));
        $this->assertArrayHasKey('zh', ReviewKeywords::catalogue());
        $this->assertSame(['zhterm'], ReviewKeywords::catalogue()['zh']);
        $this->assertContains('zhterm', ReviewKeywords::all());

        ReviewKeywords::resetLocale('zh');

        $this->assertArrayNotHasKey('zh', ReviewKeywords::catalogue());
    }

    public function test_removing_an_english_term_unbans_it(): void
    {
        ReviewKeywords::saveLocale('en', array_values(array_diff(ReviewKeywords::TERMS, ['casino'])));

        $matches = ReviewKeywords::matches('Stop casino betting promotions');

        $this->assertNotContains('casino', $matches);
        $this->assertContains('betting', $matches);
        $this->assertNotEmpty(ReviewKeywords::matches('betting'));
        $this->assertEmpty(ReviewKeywords::matches('Stop casino promotions'));

        ReviewKeywords::resetLocale('en');

        $this->assertContains('casino', ReviewKeywords::matches('Stop casino betting promotions'));
    }

    public function test_the_union_of_all_locales_still_applies(): void
    {
        $this->assertSame([], ReviewKeywords::overrides());
        $this->assertNotEmpty(ReviewKeywords::matches('pariuri'));
        $this->assertNotEmpty(ReviewKeywords::matches('casino'));
        $this->assertContains('pariuri', ReviewKeywords::catalogue()['ro']);
        $this->assertContains('casino', ReviewKeywords::catalogue()['en']);
        $this->assertContains('pariuri', ReviewKeywords::all());
        $this->assertContains('casino', ReviewKeywords::all());
    }

    public function test_admin_keywords_page_renders_the_terms_textarea(): void
    {
        $level = $this->levelWithPermissions('keywords_editor_viewer', [
            'spam' => ['view', 'edit'],
            'petitions' => ['view', 'edit'],
        ]);
        $user = $this->userWithLevel($level);

        $response = $this->actingAs($user, 'admin')->get(route('admin.keywords', ['locale' => 'ro']));

        $response->assertOk();

        $html = (string) $response->getContent();

        $this->assertMatchesRegularExpression('/<textarea[^>]*name="terms"/i', $html);
        $this->assertMatchesRegularExpression(
            '/<input(?=[^>]*\btype="hidden")(?=[^>]*\bname="locale")(?=[^>]*\bvalue="ro")[^>]*>/i',
            $html,
            'The form must carry the edited locale in a hidden input.'
        );
        $this->assertMatchesRegularExpression(
            '/<option(?=[^>]*\bvalue="ro")(?=[^>]*\bselected)[^>]*>/i',
            $html,
            'The ro option must be the selected one.'
        );
        $this->assertDoesNotMatchRegularExpression(
            '/<option(?=[^>]*\bvalue="en")(?=[^>]*\bselected)[^>]*>/i',
            $html,
            'Only the ro option may be selected.'
        );
        $this->assertStringContainsString('pariuri', $html);
        $this->assertStringContainsString(
            route('admin.keywords.concept'),
            $html,
            'An editor must get the concept switch form.'
        );
        $this->assertStringContainsString(
            route('admin.keywords.rescan'),
            $html,
            'An editor must get the rescan form.'
        );
    }

    public function test_viewer_without_edit_permission_sees_no_edit_controls(): void
    {
        $level = $this->levelWithPermissions('keywords_viewer_only', ['spam' => ['view']]);
        $user = $this->userWithLevel($level);

        $response = $this->actingAs($user, 'admin')->get(route('admin.keywords', ['locale' => 'ro']));

        $response->assertOk();

        $html = (string) $response->getContent();

        $this->assertStringNotContainsString(route('admin.keywords.save'), $html);
        $this->assertStringNotContainsString(route('admin.keywords.reset'), $html);
        $this->assertStringNotContainsString(route('admin.keywords.concept'), $html);
        $this->assertStringNotContainsString(route('admin.keywords.rescan'), $html);
        $this->assertDoesNotMatchRegularExpression('/<textarea[^>]*name="terms"/i', $html);
        $this->assertStringContainsString('you have read-only access to the keyword lists', $html);
        $this->assertStringContainsString('pariuri', $html);
    }

    public function test_admin_keywords_page_shows_the_custom_marker_the_reset_control_and_the_counts_table(): void
    {
        $level = $this->levelWithPermissions('keywords_editor', ['spam' => ['view', 'edit']]);
        $user = $this->userWithLevel($level);

        $this->actingAs($user, 'admin')->post(route('admin.keywords.save'), [
            'locale' => 'ro',
            'terms' => "cuvant de test\nalt termen",
        ])->assertRedirect();

        $response = $this->actingAs($user, 'admin')->get(route('admin.keywords', ['locale' => 'ro']));

        $response->assertOk();

        $html = (string) $response->getContent();

        $this->assertMatchesRegularExpression(
            '/<option(?=[^>]*\bvalue="ro")[^>]*>\s*ro\s*-\s*Romanian\s*\(custom\)/i',
            $html,
            'A locale with an admin override must be marked as custom in the locale list.'
        );

        $this->assertDoesNotMatchRegularExpression(
            '/<option(?=[^>]*\bvalue="en")[^>]*>\s*en\s*-\s*English\s*\(custom\)/i',
            $html,
            'A locale without an override must not be marked as custom.'
        );

        $this->assertStringContainsString(
            'action="'.route('admin.keywords.reset').'"',
            $html,
            'The page must offer the reset control.'
        );

        $this->assertMatchesRegularExpression(
            '/<td[^>]*>\s*ro\s*-\s*Romanian\s*<\/td>\s*<td[^>]*>\s*2\s*<\/td>/i',
            $html,
            'The counts table must list the ro locale with the saved term count.'
        );
    }

    public function test_admin_can_save_a_locale_keyword_list(): void
    {
        $level = $this->levelWithPermissions('keywords_editor', ['spam' => ['view', 'edit']]);
        $user = $this->userWithLevel($level);

        $response = $this->actingAs($user, 'admin')->post(route('admin.keywords.save'), [
            'locale' => 'ro',
            'terms' => "cuvant de test\nalt termen\n",
        ]);

        $status = $response->getStatusCode();

        $this->assertTrue($status >= 200 && $status < 400, "Expected a successful response, got {$status}.");

        $stored = Settings::get('review_keywords_ro', [], 'moderation');

        $this->assertIsArray($stored);
        $this->assertSame([
            $this->entry('cuvant de test'),
            $this->entry('alt termen'),
        ], $stored);
        $this->assertNotEmpty(ReviewKeywords::matches('cuvant de test'));
        $this->assertEmpty(ReviewKeywords::matches('pariuri'));
    }

    public function test_admin_can_reset_a_locale_to_the_bundled_terms(): void
    {
        ReviewKeywords::saveLocale('ro', ['cuvant de test']);

        $level = $this->levelWithPermissions('keywords_editor', ['spam' => ['view', 'edit']]);
        $user = $this->userWithLevel($level);

        $response = $this->actingAs($user, 'admin')->post(route('admin.keywords.reset'), [
            'locale' => 'ro',
        ]);

        $status = $response->getStatusCode();

        $this->assertTrue($status >= 200 && $status < 400, "Expected a successful response, got {$status}.");

        $this->assertDatabaseMissing('settings', [
            'group' => 'moderation',
            'key' => 'review_keywords_ro',
        ]);
        $this->assertNotEmpty(ReviewKeywords::matches('pariuri'));
    }

    public function test_resetting_an_invalid_locale_stores_nothing(): void
    {
        $level = $this->levelWithPermissions('keywords_editor', ['spam' => ['view', 'edit']]);
        $user = $this->userWithLevel($level);

        ReviewKeywords::saveLocale('ro', ['cuvant de test']);

        $before = ReviewKeywords::overrides();

        $response = $this->actingAs($user, 'admin')->post(route('admin.keywords.reset'), [
            'locale' => 'zz',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $response->assertSessionMissing('success');

        $location = (string) $response->headers->get('Location');

        $this->assertStringNotContainsString(
            'zz',
            $location,
            'The invalid locale must not be handed back as a redirect target.'
        );

        $this->assertSame($before, ReviewKeywords::overrides());
        $this->assertArrayNotHasKey('zz', ReviewKeywords::overrides());
        $this->assertSame([$this->entry('cuvant de test')], ReviewKeywords::overrides()['ro']);
        $this->assertSame([], Settings::get('review_keywords_zz', [], 'moderation'));
        $this->assertDatabaseMissing('settings', [
            'group' => 'moderation',
            'key' => 'review_keywords_zz',
        ]);
    }

    public function test_user_without_the_permission_is_refused(): void
    {
        $level = $this->levelWithPermissions('logs_only', ['logs' => ['view']]);
        $user = $this->userWithLevel($level);

        $response = $this->actingAs($user, 'admin')->get(route('admin.keywords'));

        $response->assertForbidden();
    }

    public function test_user_without_the_edit_permission_cannot_save(): void
    {
        $level = $this->levelWithPermissions('keywords_viewer_only', ['spam' => ['view']]);
        $user = $this->userWithLevel($level);

        $response = $this->actingAs($user, 'admin')->post(route('admin.keywords.save'), [
            'locale' => 'ro',
            'terms' => 'cuvant de test',
        ]);

        $response->assertForbidden();

        $this->assertDatabaseMissing('settings', [
            'group' => 'moderation',
            'key' => 'review_keywords_ro',
        ]);
        $this->assertArrayNotHasKey('ro', ReviewKeywords::overrides());
    }

    public function test_user_without_the_edit_permission_cannot_reset(): void
    {
        ReviewKeywords::saveLocale('ro', ['cuvant de test']);

        $level = $this->levelWithPermissions('keywords_viewer_only', ['spam' => ['view']]);
        $user = $this->userWithLevel($level);

        $response = $this->actingAs($user, 'admin')->post(route('admin.keywords.reset'), [
            'locale' => 'ro',
        ]);

        $response->assertForbidden();

        $this->assertDatabaseHas('settings', [
            'group' => 'moderation',
            'key' => 'review_keywords_ro',
        ]);
        $this->assertSame([$this->entry('cuvant de test')], ReviewKeywords::overrides()['ro']);
        $this->assertEmpty(ReviewKeywords::matches('pariuri'));
    }

    public function test_saving_redirects_back_to_the_same_locale(): void
    {
        $level = $this->levelWithPermissions('keywords_editor', ['spam' => ['view', 'edit']]);
        $user = $this->userWithLevel($level);

        $response = $this->actingAs($user, 'admin')->post(route('admin.keywords.save'), [
            'locale' => 'ro',
            'terms' => 'cuvant de test',
        ]);

        $response->assertRedirect();

        $this->assertStringContainsString('locale=ro', (string) $response->headers->get('Location'));
    }

    public function test_saving_an_invalid_locale_stores_nothing(): void
    {
        $level = $this->levelWithPermissions('keywords_editor', ['spam' => ['view', 'edit']]);
        $user = $this->userWithLevel($level);

        ReviewKeywords::saveLocale('ro', ['cuvant de test']);

        $before = ReviewKeywords::overrides();

        $response = $this->actingAs($user, 'admin')->post(route('admin.keywords.save'), [
            'locale' => 'zz',
            'terms' => 'termen interzis',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $response->assertSessionMissing('success');

        $location = (string) $response->headers->get('Location');

        $this->assertStringNotContainsString(
            'zz',
            $location,
            'The invalid locale must not be handed back as a redirect target.'
        );

        $this->assertSame($before, ReviewKeywords::overrides());
        $this->assertArrayNotHasKey('zz', ReviewKeywords::overrides());
        $this->assertSame([$this->entry('cuvant de test')], ReviewKeywords::overrides()['ro']);
        $this->assertSame([], Settings::get('review_keywords_zz', [], 'moderation'));
        $this->assertDatabaseMissing('settings', [
            'group' => 'moderation',
            'key' => 'review_keywords_zz',
        ]);
    }

    public function test_saving_an_array_of_terms_is_rejected_and_keeps_the_stored_list(): void
    {
        $level = $this->levelWithPermissions('keywords_editor', ['spam' => ['view', 'edit']]);
        $user = $this->userWithLevel($level);

        ReviewKeywords::saveLocale('ro', ['cuvant de test']);

        $response = $this->actingAs($user, 'admin')->post(route('admin.keywords.save'), [
            'locale' => 'ro',
            'terms' => ['a', 'b'],
        ]);

        $this->assertContains(
            $response->status(),
            [302, 422],
            'Expected a validation error for an array of terms, got '.$response->status().'.'
        );

        if ($response->status() === 302) {
            $response->assertSessionHasErrors('terms');
        }

        $this->assertSame([$this->entry('cuvant de test')], Settings::get('review_keywords_ro', [], 'moderation'));
        $this->assertSame([$this->entry('cuvant de test')], ReviewKeywords::overrides()['ro']);

        $this->actingAs($user, 'admin')
            ->followingRedirects()
            ->post(route('admin.keywords.save'), [
                'locale' => 'ro',
                'terms' => ['a', 'b'],
            ])
            ->assertOk();
    }

    public function test_saving_invalid_utf8_is_rejected_and_keeps_the_stored_list(): void
    {
        $level = $this->levelWithPermissions('keywords_editor', ['spam' => ['view', 'edit']]);
        $user = $this->userWithLevel($level);

        ReviewKeywords::saveLocale('ro', ['cuvant de test']);

        $response = $this->actingAs($user, 'admin')->post(route('admin.keywords.save'), [
            'locale' => 'ro',
            'terms' => "cuvant \xB1\x31",
        ]);

        $this->assertNotSame(500, $response->status(), 'Invalid bytes must not crash the admin.');
        $this->assertContains(
            $response->status(),
            [302, 422],
            'Expected an error for invalid UTF-8, got '.$response->status().'.'
        );
        $this->assertTrue(
            session()->has('error') || session()->has('errors'),
            'The admin must be told that the save failed.'
        );

        $this->assertSame([$this->entry('cuvant de test')], Settings::get('review_keywords_ro', [], 'moderation'));
        $this->assertSame([$this->entry('cuvant de test')], ReviewKeywords::overrides()['ro']);
    }

    public function test_saving_an_oversized_term_list_is_rejected_and_keeps_the_stored_list(): void
    {
        $level = $this->levelWithPermissions('keywords_editor', ['spam' => ['view', 'edit']]);
        $user = $this->userWithLevel($level);

        ReviewKeywords::saveLocale('ro', ['cuvant de test']);

        $response = $this->actingAs($user, 'admin')->post(route('admin.keywords.save'), [
            'locale' => 'ro',
            'terms' => str_repeat('a', 20001),
        ]);

        $this->assertContains(
            $response->status(),
            [302, 422],
            'Expected a validation error for an oversized term list, got '.$response->status().'.'
        );

        if ($response->status() === 302) {
            $response->assertSessionHasErrors('terms');
        }

        $this->assertSame([$this->entry('cuvant de test')], Settings::get('review_keywords_ro', [], 'moderation'));
        $this->assertSame([$this->entry('cuvant de test')], ReviewKeywords::overrides()['ro']);
    }

    public function test_saving_more_terms_than_the_ceiling_is_rejected_and_keeps_the_stored_list(): void
    {
        $level = $this->levelWithPermissions('keywords_editor', ['spam' => ['view', 'edit']]);
        $user = $this->userWithLevel($level);

        ReviewKeywords::saveLocale('ro', ['cuvant de test']);

        $terms = implode("\n", array_map(
            fn (int $i): string => "term{$i}",
            range(1, ReviewKeywords::MAX_TERMS + 1)
        ));

        $response = $this->actingAs($user, 'admin')->post(route('admin.keywords.save'), [
            'locale' => 'ro',
            'terms' => $terms,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $response->assertSessionDoesntHaveErrors('terms');

        $this->assertStringContainsString(
            'Too many terms',
            (string) session('error'),
            'The admin must be told that the term list exceeds the ceiling.'
        );

        $this->assertSame([$this->entry('cuvant de test')], Settings::get('review_keywords_ro', [], 'moderation'));
        $this->assertSame([$this->entry('cuvant de test')], ReviewKeywords::overrides()['ro']);
    }

    public function test_saving_more_terms_than_the_ceiling_stores_only_the_first_terms(): void
    {
        $terms = array_map(
            fn (int $i): string => "term{$i}",
            range(1, ReviewKeywords::MAX_TERMS + 1)
        );

        ReviewKeywords::saveLocale('ro', $terms);

        $stored = Settings::get('review_keywords_ro', [], 'moderation');

        $this->assertIsArray($stored);
        $this->assertCount(ReviewKeywords::MAX_TERMS, $stored);
        $this->assertSame($this->entry('term1'), $stored[0]);
        $this->assertSame($this->entry('term'.ReviewKeywords::MAX_TERMS), $stored[ReviewKeywords::MAX_TERMS - 1]);
    }

    public function test_saving_an_oversized_term_and_concept_stores_the_truncated_values(): void
    {
        $level = $this->levelWithPermissions('keywords_editor', ['spam' => ['view', 'edit']]);
        $user = $this->userWithLevel($level);

        $response = $this->actingAs($user, 'admin')->post(route('admin.keywords.save'), [
            'locale' => 'ro',
            'terms' => str_repeat('a', 300).' :: '.str_repeat('b', 300),
        ]);

        $response->assertRedirect();

        $expected = [[
            'term' => str_repeat('a', ReviewKeywords::MAX_TERM_LENGTH),
            'concept' => str_repeat('b', ReviewKeywords::MAX_CONCEPT_LENGTH),
        ]];

        $this->assertSame(
            $expected,
            Settings::get('review_keywords_ro', [], 'moderation'),
            'The settings row must hold the truncated term and concept.'
        );

        $this->assertSame(
            $expected,
            ReviewKeywords::entriesFor('ro'),
            'The saved locale must hold the truncated term and concept.'
        );
    }

    public function test_saving_without_the_terms_field_keeps_the_existing_list(): void
    {
        $level = $this->levelWithPermissions('keywords_editor', ['spam' => ['view', 'edit']]);
        $user = $this->userWithLevel($level);

        $this->actingAs($user, 'admin')->post(route('admin.keywords.save'), [
            'locale' => 'ro',
            'terms' => "cuvant de test\nalt termen",
        ])->assertRedirect();

        $this->assertContains($this->entry('cuvant de test'), ReviewKeywords::overrides()['ro']);

        $response = $this->actingAs($user, 'admin')->post(route('admin.keywords.save'), [
            'locale' => 'ro',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $response->assertSessionMissing('success');

        $overrides = ReviewKeywords::overrides();

        $this->assertArrayHasKey('ro', $overrides);
        $this->assertContains($this->entry('cuvant de test'), $overrides['ro']);
        $this->assertContains($this->entry('alt termen'), $overrides['ro']);
    }

    public function test_saving_an_empty_term_list_stores_an_explicit_empty_override(): void
    {
        $level = $this->levelWithPermissions('keywords_editor', ['spam' => ['view', 'edit']]);
        $user = $this->userWithLevel($level);

        ReviewKeywords::saveLocale('ro', ['cuvant de test']);

        $this->actingAs($user, 'admin')->post(route('admin.keywords.save'), [
            'locale' => 'ro',
            'terms' => '',
        ])->assertRedirect();

        $overrides = ReviewKeywords::overrides();

        $this->assertArrayHasKey('ro', $overrides);
        $this->assertSame(
            [],
            $overrides['ro'],
            'An empty textarea must clear the locale override, so the terms rule has to accept an empty value.'
        );
        $this->assertSame([], Settings::get('review_keywords_ro', [], 'moderation'));
        $this->assertDatabaseHas('settings', [
            'group' => 'moderation',
            'key' => 'review_keywords_ro',
        ]);
    }

    public function test_a_deactivated_language_can_still_be_saved_and_reset(): void
    {
        $level = $this->levelWithPermissions('keywords_editor', ['spam' => ['view', 'edit']]);
        $user = $this->userWithLevel($level);

        Language::where('code', 'ro')->update(['is_active' => false]);

        Cache::flush();

        $this->assertNotContains('ro', Locale::supported());

        ReviewKeywords::saveLocale('ro', ['cuvant de test']);

        $response = $this->actingAs($user, 'admin')->post(route('admin.keywords.save'), [
            'locale' => 'ro',
            'terms' => 'cuvant nou de test',
        ]);

        $this->assertTrue(
            $response->isRedirect(),
            'Expected a redirect after saving a deactivated locale, got '.$response->status().'.'
        );

        $overrides = ReviewKeywords::overrides();

        $this->assertArrayHasKey('ro', $overrides);
        $this->assertContains($this->entry('cuvant nou de test'), $overrides['ro']);
        $this->assertNotContains($this->entry('cuvant de test'), $overrides['ro']);

        $response = $this->actingAs($user, 'admin')->post(route('admin.keywords.reset'), [
            'locale' => 'ro',
        ]);

        $this->assertTrue(
            $response->isRedirect(),
            'Expected a redirect after resetting a deactivated locale, got '.$response->status().'.'
        );

        $this->assertArrayNotHasKey('ro', ReviewKeywords::overrides());
        $this->assertNotEmpty(ReviewKeywords::matches('pariuri'));
    }

    public function test_the_keywords_screen_still_lists_a_deactivated_language(): void
    {
        $level = $this->levelWithPermissions('keywords_editor', ['spam' => ['view', 'edit']]);
        $user = $this->userWithLevel($level);

        Language::where('code', 'ro')->update(['is_active' => false]);

        Cache::flush();

        $response = $this->actingAs($user, 'admin')->get(route('admin.keywords', ['locale' => 'ro']));

        $response->assertOk();

        $html = (string) $response->getContent();

        $this->assertMatchesRegularExpression('/<option(?=[^>]*\bvalue="ro")[^>]*>/i', $html);
        $this->assertStringContainsString('pariuri', $html);
    }

    public function test_dropping_an_english_term_publishes_the_petition(): void
    {
        ReviewKeywords::saveLocale('en', array_values(array_diff(ReviewKeywords::TERMS, ['bookmaker'])));

        $petition = $this->createPetition('en', 'Stop bookmaker advertising');

        $this->assertSame('published', $petition->status);
        $this->assertDatabaseHas('petitions', ['id' => $petition->id, 'status' => 'published']);
        $this->assertTrue(Petition::visible()->where('id', $petition->id)->exists());
        $this->assertDatabaseMissing('spam_logs', ['type' => 'petition.review']);
    }

    public function test_a_custom_ro_term_holds_the_petition(): void
    {
        ReviewKeywords::saveLocale('ro', ['cuvant de test']);

        $petition = $this->createPetition('ro', 'Cuvant de test in Romania');

        $this->assertSame('draft', $petition->status);
        $this->assertDatabaseHas('petitions', ['id' => $petition->id, 'status' => 'draft']);
        $this->assertFalse(Petition::visible()->where('id', $petition->id)->exists());
        $this->assertDatabaseHas('spam_logs', ['type' => 'petition.review']);

        $slug = $petition->translations()->where('locale', 'ro')->value('slug');

        $other = User::factory()->create(['verified' => true]);

        $this->actingAs($other)
            ->get("/ro/petition/{$slug}/{$petition->id}")
            ->assertNotFound();
    }

    public function test_a_review_owned_spam_word_is_held_for_review_instead_of_rejected(): void
    {
        $user = User::factory()->create(['verified' => true]);

        $category = Category::factory()->create(['is_active' => true]);

        CategoryTranslation::create([
            'category_id' => $category->id,
            'locale' => 'en',
            'name' => 'Test Category',
            'slug' => 'test-category',
        ]);

        $response = $this->actingAs($user)->post('/en/create-petition', [
            'title' => 'Stop casino advertising',
            'description' => '<p>This is a test petition with enough content to pass validation and be accepted.</p>',
            'goal_signatures' => 100,
            'category_id' => $category->id,
        ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        $petition = Petition::latest('id')->firstOrFail();

        $this->assertSame('draft', $petition->status);
        $this->assertDatabaseHas('petitions', ['id' => $petition->id, 'status' => 'draft']);
        $this->assertDatabaseHas('spam_logs', ['type' => 'petition.review']);
        $this->assertDatabaseMissing('spam_logs', ['type' => 'petition.create']);
    }

    public function test_removing_a_review_owned_spam_word_from_the_catalogue_publishes_the_petition(): void
    {
        ReviewKeywords::saveLocale('en', array_values(array_diff(ReviewKeywords::TERMS, ['casino'])));

        $petition = $this->createPetition('en', 'Stop casino advertising');

        $this->assertSame('published', $petition->status);
        $this->assertDatabaseHas('petitions', ['id' => $petition->id, 'status' => 'published']);
        $this->assertTrue(Petition::visible()->where('id', $petition->id)->exists());
        $this->assertDatabaseMissing('spam_logs', ['type' => 'petition.review']);
        $this->assertDatabaseMissing('spam_logs', ['type' => 'petition.create']);
    }

    public function test_the_keywords_page_warns_about_a_review_owned_word_that_left_the_catalogue(): void
    {
        $level = $this->levelWithPermissions('keywords_editor', ['spam' => ['view', 'edit']]);
        $user = $this->userWithLevel($level);

        $untouched = (string) $this->actingAs($user, 'admin')
            ->get(route('admin.keywords', ['locale' => 'en']))
            ->assertOk()
            ->getContent();

        $this->assertStringNotContainsString(
            'porn',
            $this->warningText($untouched),
            'The untouched catalogue must not warn about a word that is still held for review.'
        );

        ReviewKeywords::saveLocale('en', array_values(array_diff(ReviewKeywords::TERMS, ['porn'])));

        $this->assertSame(['porn'], ReviewKeywords::unblockedLegacyTerms());

        $changed = (string) $this->actingAs($user, 'admin')
            ->get(route('admin.keywords', ['locale' => 'en']))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString(
            'porn',
            $this->warningText($changed),
            'The warning must name the word that is no longer held for review.'
        );
    }

    public function test_the_keywords_page_does_not_warn_while_the_catalogue_is_untouched(): void
    {
        $level = $this->levelWithPermissions('keywords_editor', ['spam' => ['view', 'edit']]);
        $user = $this->userWithLevel($level);

        $this->assertSame([], ReviewKeywords::unblockedLegacyTerms());

        $html = (string) $this->actingAs($user, 'admin')
            ->get(route('admin.keywords', ['locale' => 'en']))
            ->assertOk()
            ->getContent();

        $this->assertSame(
            '',
            $this->warningText($html),
            'An untouched catalogue must not show a warning.'
        );
    }

    public function test_admin_can_disable_and_reenable_a_concept(): void
    {
        if (! Route::has('admin.keywords.concept')) {
            $this->markTestSkipped('The admin concept route is not registered.');
        }

        $level = $this->levelWithPermissions('keywords_editor', ['spam' => ['view', 'edit']]);
        $user = $this->userWithLevel($level);

        ReviewKeywords::saveLocale('en', [
            ['term' => 'alpha term', 'concept' => 'shared concept'],
        ]);

        $this->actingAs($user, 'admin')
            ->post(route('admin.keywords.concept'), ['concept' => 'shared concept', 'enabled' => '0'])
            ->assertRedirect();

        $this->assertContains('shared concept', ReviewKeywords::disabledConcepts());
        $this->assertNotContains('alpha term', ReviewKeywords::catalogue()['en']);
        $this->assertEmpty(ReviewKeywords::matches('alpha term'));

        $this->actingAs($user, 'admin')
            ->post(route('admin.keywords.concept'), ['concept' => 'shared concept', 'enabled' => '1'])
            ->assertRedirect();

        $this->assertNotContains('shared concept', ReviewKeywords::disabledConcepts());
        $this->assertContains('alpha term', ReviewKeywords::catalogue()['en']);
        $this->assertNotEmpty(ReviewKeywords::matches('alpha term'));
    }

    public function test_a_disabled_concept_without_any_wording_stays_listed_and_can_be_re_enabled(): void
    {
        if (! Route::has('admin.keywords.concept')) {
            $this->markTestSkipped('The admin concept route is not registered.');
        }

        $level = $this->levelWithPermissions('keywords_editor', ['spam' => ['view', 'edit']]);
        $user = $this->userWithLevel($level);

        ReviewKeywords::saveLocale('en', [
            ['term' => 'alpha term', 'concept' => 'shared concept'],
        ]);

        ReviewKeywords::setConceptEnabled('shared concept', false);

        $this->assertContains('shared concept', ReviewKeywords::disabledConcepts());
        $this->assertNotContains('alpha term', ReviewKeywords::catalogue()['en']);

        ReviewKeywords::saveLocale('en', []);

        $this->assertNotContains('alpha term', ReviewKeywords::catalogue()['en']);
        $this->assertNotContains('alpha term', ReviewKeywords::all());

        $html = (string) $this->actingAs($user, 'admin')
            ->get(route('admin.keywords', ['locale' => 'en']))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString(
            'shared concept',
            $this->conceptsTableHtml($html),
            'A disabled concept must stay listed even when it has no wording left.'
        );

        $response = $this->actingAs($user, 'admin')
            ->post(route('admin.keywords.concept'), ['concept' => 'shared concept', 'enabled' => '1']);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $response->assertSessionMissing('error');

        $this->assertNotContains(
            'shared concept',
            ReviewKeywords::disabledConcepts(),
            'The concept must be re-enabled.'
        );
    }

    public function test_the_concept_endpoint_reports_a_refused_write(): void
    {
        if (! Route::has('admin.keywords.concept')) {
            $this->markTestSkipped('The admin concept route is not registered.');
        }

        $level = $this->levelWithPermissions('keywords_editor', ['spam' => ['view', 'edit']]);
        $user = $this->userWithLevel($level);

        DB::table('settings')->insert([
            'group' => 'moderation',
            'key' => 'review_keywords_disabled',
            'value' => 'not-a-list',
            'type' => 'string',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Cache::flush();

        Settings::forget();

        ReviewKeywords::flushCache();

        $response = $this->actingAs($user, 'admin')
            ->post(route('admin.keywords.concept'), [
                'concept' => 'betting',
                'enabled' => '0',
                'locale' => 'en',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $response->assertSessionMissing('success');

        $this->assertSame(
            'not-a-list',
            $this->settingValue('review_keywords_disabled'),
            'A refused write must leave the malformed value untouched.'
        );

        $this->assertNotContains(
            'betting',
            ReviewKeywords::disabledConcepts(),
            'A refused write must not disable the concept.'
        );
    }

    public function test_a_viewer_without_the_edit_permission_cannot_disable_a_concept(): void
    {
        if (! Route::has('admin.keywords.concept')) {
            $this->markTestSkipped('The admin concept route is not registered.');
        }

        ReviewKeywords::saveLocale('en', [
            ['term' => 'alpha term', 'concept' => 'shared concept'],
        ]);

        $level = $this->levelWithPermissions('keywords_concept_viewer', ['spam' => ['view']]);
        $user = $this->userWithLevel($level);

        $response = $this->actingAs($user, 'admin')
            ->post(route('admin.keywords.concept'), [
                'concept' => 'shared concept',
                'enabled' => '0',
                'locale' => 'en',
            ]);

        $response->assertForbidden();

        $this->assertNotContains('shared concept', ReviewKeywords::disabledConcepts());
        $this->assertContains('alpha term', ReviewKeywords::catalogue()['en']);
        $this->assertNotEmpty(ReviewKeywords::matches('alpha term'));
    }

    public function test_a_viewer_without_the_edit_permission_cannot_reenable_a_disabled_concept(): void
    {
        if (! Route::has('admin.keywords.concept')) {
            $this->markTestSkipped('The admin concept route is not registered.');
        }

        ReviewKeywords::saveLocale('en', [
            ['term' => 'alpha term', 'concept' => 'shared concept'],
        ]);

        ReviewKeywords::setConceptEnabled('shared concept', false);

        $level = $this->levelWithPermissions('keywords_concept_viewer', ['spam' => ['view']]);
        $user = $this->userWithLevel($level);

        $response = $this->actingAs($user, 'admin')
            ->post(route('admin.keywords.concept'), [
                'concept' => 'shared concept',
                'enabled' => '1',
                'locale' => 'en',
            ]);

        $response->assertForbidden();

        $this->assertContains('shared concept', ReviewKeywords::disabledConcepts());
        $this->assertNotContains('alpha term', ReviewKeywords::catalogue()['en']);
        $this->assertEmpty(ReviewKeywords::matches('alpha term'));
    }
}
