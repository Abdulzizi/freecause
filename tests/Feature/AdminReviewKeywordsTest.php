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
use Tests\TestCase;

class AdminReviewKeywordsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();

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

    public function test_bundled_defaults_are_unchanged(): void
    {
        $this->assertNotEmpty(ReviewKeywords::matches('Stop casino betting promotions'));
        $this->assertSame('en', ReviewKeywords::localeMap()['casino']);
        $this->assertContains('pariuri', ReviewKeywords::translations()['ro']);
        $this->assertNotEmpty(ReviewKeywords::matches('pariuri'));
        $this->assertSame('ro', ReviewKeywords::localeMap()['pariuri']);

        $this->assertSame(57, count(ReviewKeywords::TERMS));

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
        $this->assertSame('ro', ReviewKeywords::localeMap()['cuvant de test']);
        $this->assertArrayNotHasKey('pariuri', ReviewKeywords::localeMap());
    }

    public function test_resetting_a_locale_restores_the_bundled_terms(): void
    {
        ReviewKeywords::saveLocale('ro', ['cuvant de test']);

        $this->assertEmpty(ReviewKeywords::matches('pariuri'));

        ReviewKeywords::resetLocale('ro');

        $this->assertNotEmpty(ReviewKeywords::matches('pariuri'));
        $this->assertSame('ro', ReviewKeywords::localeMap()['pariuri']);
        $this->assertEmpty(ReviewKeywords::overrides());
    }

    public function test_saved_overrides_are_persisted_in_settings(): void
    {
        ReviewKeywords::saveLocale('ro', ['a', 'b']);

        $this->assertDatabaseHas('settings', [
            'group' => 'moderation',
            'key' => 'review_keywords_ro',
        ]);
        $this->assertSame(['a', 'b'], Settings::get('review_keywords_ro', [], 'moderation'));
        $this->assertSame(['a', 'b'], ReviewKeywords::overrides()['ro']);

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

        $overrides = ReviewKeywords::overrides();

        $this->assertArrayHasKey('ro', $overrides);
        $this->assertSame(['ok'], $overrides['ro']);
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
        $this->assertSame('zh', ReviewKeywords::localeMap()['zhterm']);

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
        $this->assertSame('ro', ReviewKeywords::localeMap()['pariuri']);
        $this->assertSame('en', ReviewKeywords::localeMap()['casino']);
    }

    public function test_admin_keywords_page_renders_the_terms_textarea(): void
    {
        $level = $this->levelWithPermissions('keywords_editor_viewer', ['spam' => ['view', 'edit']]);
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
        $this->assertContains('cuvant de test', $stored);
        $this->assertContains('alt termen', $stored);
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
        $this->assertSame(['cuvant de test'], ReviewKeywords::overrides()['ro']);
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
        $this->assertSame(['cuvant de test'], ReviewKeywords::overrides()['ro']);
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
        $this->assertContains('cuvant de test', ReviewKeywords::overrides()['ro']);
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

        $this->assertSame(['cuvant de test'], Settings::get('review_keywords_ro', [], 'moderation'));
        $this->assertSame(['cuvant de test'], ReviewKeywords::overrides()['ro']);

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

        $this->assertSame(['cuvant de test'], Settings::get('review_keywords_ro', [], 'moderation'));
        $this->assertSame(['cuvant de test'], ReviewKeywords::overrides()['ro']);
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

        $this->assertSame(['cuvant de test'], Settings::get('review_keywords_ro', [], 'moderation'));
        $this->assertSame(['cuvant de test'], ReviewKeywords::overrides()['ro']);
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

        $this->assertSame(['cuvant de test'], Settings::get('review_keywords_ro', [], 'moderation'));
        $this->assertSame(['cuvant de test'], ReviewKeywords::overrides()['ro']);
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
        $this->assertSame('term1', $stored[0]);
        $this->assertSame('term'.ReviewKeywords::MAX_TERMS, $stored[ReviewKeywords::MAX_TERMS - 1]);
    }

    public function test_saving_without_the_terms_field_keeps_the_existing_list(): void
    {
        $level = $this->levelWithPermissions('keywords_editor', ['spam' => ['view', 'edit']]);
        $user = $this->userWithLevel($level);

        $this->actingAs($user, 'admin')->post(route('admin.keywords.save'), [
            'locale' => 'ro',
            'terms' => "cuvant de test\nalt termen",
        ])->assertRedirect();

        $this->assertContains('cuvant de test', ReviewKeywords::overrides()['ro']);

        $response = $this->actingAs($user, 'admin')->post(route('admin.keywords.save'), [
            'locale' => 'ro',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $response->assertSessionMissing('success');

        $overrides = ReviewKeywords::overrides();

        $this->assertArrayHasKey('ro', $overrides);
        $this->assertContains('cuvant de test', $overrides['ro']);
        $this->assertContains('alt termen', $overrides['ro']);
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
        $this->assertContains('cuvant nou de test', $overrides['ro']);
        $this->assertNotContains('cuvant de test', $overrides['ro']);

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
}
