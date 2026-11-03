<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\PageTranslation;
use App\Models\Petition;
use App\Models\PetitionTranslation;
use App\Models\Signature;
use App\Models\User;
use App\Models\UserLevel;
use Database\Seeders\LanguageSeeder;
use Database\Seeders\UserLevelSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPanelRevampB1Test extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([UserLevelSeeder::class, LanguageSeeder::class]);
        $this->admin = User::factory()->create([
            'level_id' => UserLevel::where('name', 'admin')->value('id'),
            'verified' => true,
        ]);

        $this->actingAs($this->admin, 'admin');
    }

    public function test_page_save_redirect_preserves_selected_page_and_locale(): void
    {
        $page = Page::create();
        PageTranslation::create([
            'page_id' => $page->id,
            'locale' => 'fr',
            'title' => 'Ancien titre',
            'slug' => 'ancien-titre',
            'content' => '<p>Contenu ancien</p>',
            'published' => true,
        ]);

        $response = $this->post(route('admin.pages.save'), [
            'page_id' => $page->id,
            'locale' => 'fr',
            'title' => 'Nouveau titre',
            'slug' => 'nouveau-titre',
            'content' => '<p>Contenu nouveau</p>',
            'published' => '1',
        ]);

        $response->assertRedirect();
        $this->assertSame('/admin/pages', parse_url($response->headers->get('Location'), PHP_URL_PATH));
        parse_str((string) parse_url($response->headers->get('Location'), PHP_URL_QUERY), $redirectQuery);
        $this->assertSame(['locale' => 'fr', 'select' => (string) $page->id], $redirectQuery);
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('page_translations', [
            'page_id' => $page->id,
            'locale' => 'fr',
            'title' => 'Nouveau titre',
            'slug' => 'nouveau-titre',
        ]);
    }

    public function test_page_save_view_url_uses_translation_for_saved_page_and_locale(): void
    {
        $page = Page::create();
        $otherPage = Page::create();
        PageTranslation::create([
            'page_id' => $otherPage->id,
            'locale' => 'fr',
            'title' => 'Other French page',
            'slug' => 'other-french-page',
            'content' => '',
            'published' => true,
        ]);
        PageTranslation::create([
            'page_id' => $otherPage->id,
            'locale' => 'es',
            'title' => 'Other Spanish page',
            'slug' => 'other-spanish-page',
            'content' => '',
            'published' => true,
        ]);

        $response = $this->post(route('admin.pages.save'), [
            'page_id' => $page->id,
            'locale' => 'en',
            'title' => 'Saved page',
            'slug' => 'saved-page',
            'content' => '',
            'published' => '1',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('view_url', url('en/saved-page'));
    }

    public function test_page_save_rejects_duplicate_slug_for_same_locale(): void
    {
        $existingPage = Page::create();
        PageTranslation::create([
            'page_id' => $existingPage->id,
            'locale' => 'en',
            'title' => 'Existing',
            'slug' => 'already-used',
            'content' => '',
            'published' => true,
        ]);

        $this->followingRedirects()
            ->from(route('admin.pages', ['select' => $existingPage->id, 'locale' => 'en']))
            ->post(route('admin.pages.save'), [
                'locale' => 'en',
                'title' => 'Another page',
                'slug' => 'Already Used',
                'content' => '',
            ])
            ->assertSee('Slug already used for this locale.');

        $this->assertSame(1, PageTranslation::where('locale', 'en')->where('slug', 'already-used')->count());
    }

    public function test_page_rich_text_editor_is_opt_in(): void
    {
        $page = Page::create();
        PageTranslation::create([
            'page_id' => $page->id,
            'locale' => 'en',
            'title' => 'Editor page',
            'slug' => 'editor-page',
            'content' => '<p>Page content</p>',
            'published' => true,
        ]);

        $this->get(route('admin.pages', ['select' => $page->id, 'locale' => 'en']))
            ->assertOk()
            ->assertSee('data-ckeditor="rich"', false);
    }

    public function test_language_option_html_textareas_remain_uninitialized_raw_editors(): void
    {
        $response = $this->get(route('admin.options.language', ['locale' => 'en']))
            ->assertOk();

        $document = new \DOMDocument;
        @$document->loadHTML($response->getContent());
        $xpath = new \DOMXPath($document);
        $editors = $xpath->query('//textarea[contains(concat(" ", normalize-space(@class), " "), " fc-editor ")]');

        $this->assertGreaterThan(0, $editors->length);
        foreach ($editors as $editor) {
            $this->assertFalse($editor->hasAttribute('data-ckeditor'));
        }
    }

    public function test_petition_translation_editor_prefills_missing_locale_from_default(): void
    {
        $petition = Petition::factory()->create(['is_featured' => true]);
        PetitionTranslation::create([
            'petition_id' => $petition->id,
            'locale' => 'en',
            'title' => 'Default petition title',
            'slug' => 'default-petition-title',
            'description' => '<p>Default petition text</p>',
            'is_featured' => true,
        ]);

        $response = $this->get(route('admin.petitions', ['select' => $petition->id, 'locale' => 'fr']))
            ->assertOk()
            ->assertSee('Create translation')
            ->assertSee('value="Default petition title"', false)
            ->assertSee('value="default-petition-title"', false)
            ->assertSee('data-ckeditor="rich"', false)
            ->assertSee('&lt;p&gt;Default petition text&lt;/p&gt;', false);

        $document = new \DOMDocument;
        @$document->loadHTML($response->getContent());
        $xpath = new \DOMXPath($document);
        $featuredCheckbox = $xpath->query('//input[@name="is_featured"]')->item(0);

        $this->assertNotNull($featuredCheckbox);
        $this->assertFalse($featuredCheckbox->hasAttribute('checked'), 'featured is per-locale; fr has no row yet');
    }

    public function test_petition_editor_rejects_goal_outside_supported_ladder(): void
    {
        $petition = Petition::factory()->create();
        PetitionTranslation::create([
            'petition_id' => $petition->id,
            'locale' => 'en',
            'title' => 'Test petition',
            'slug' => 'test-petition',
            'description' => '',
            'is_featured' => false,
        ]);

        $this->from(route('admin.petitions', ['select' => $petition->id, 'locale' => 'en']))
            ->post(route('admin.petitions.save'), [
                'id' => $petition->id,
                'locale' => 'en',
                'status' => 'published',
                'title' => 'Test petition',
                'slug' => 'test-petition',
                'goal_signatures' => 999,
            ])
            ->assertSessionHasErrors('goal_signatures');
    }

    public function test_petition_bulk_row_actions_update_feature_and_active_state(): void
    {
        $petition = Petition::factory()->create(['is_featured' => false, 'is_active' => false]);
        PetitionTranslation::create([
            'petition_id' => $petition->id,
            'locale' => 'en',
            'title' => 'Test petition',
            'slug' => 'test-petition',
            'description' => '',
            'is_featured' => false,
        ]);

        $this->get(route('admin.petitions'))
            ->assertOk()
            ->assertSee('data-petition-toggle', false)
            ->assertSee('data-kind="active"', false)
            ->assertSee('data-kind="featured"', false)
            ->assertSee('data-action="activate"', false)
            ->assertSee('data-action="feature"', false);

        $this->postJson(route('admin.petitions.bulkAction'), ['action' => 'feature', 'ids' => [$petition->id]])
            ->assertOk()
            ->assertJsonPath('ok', true);

        $this->assertDatabaseHas('petition_translations', ['petition_id' => $petition->id, 'locale' => 'en', 'is_featured' => 1]);

        $this->postJson(route('admin.petitions.bulkAction'), ['action' => 'activate', 'ids' => [$petition->id]])
            ->assertOk()
            ->assertJsonPath('ok', true);

        $this->assertDatabaseHas('petitions', ['id' => $petition->id, 'is_active' => 1]);
    }

    public function test_bulk_feature_falls_back_to_first_translation_without_default_locale(): void
    {
        $petition = Petition::factory()->create();
        $it = PetitionTranslation::create(['petition_id' => $petition->id, 'locale' => 'it', 'title' => 'T', 'slug' => 't-it', 'description' => '', 'is_featured' => false]);
        $fr = PetitionTranslation::create(['petition_id' => $petition->id, 'locale' => 'fr', 'title' => 'T', 'slug' => 't-fr', 'description' => '', 'is_featured' => false]);

        $this->postJson(route('admin.petitions.bulkAction'), ['action' => 'feature', 'ids' => [$petition->id]])->assertOk();

        $this->assertDatabaseHas('petition_translations', ['id' => $it->id, 'is_featured' => 1]);
        $this->assertDatabaseHas('petition_translations', ['id' => $fr->id, 'is_featured' => 0]);
    }

    public function test_petition_bulk_ban_and_single_reconcile_are_available(): void
    {
        $petition = Petition::factory()->create([
            'status' => 'published',
            'is_active' => true,
            'is_featured' => true,
            'signature_count' => 99,
        ]);

        $this->postJson(route('admin.petitions.bulkAction'), [
            'action' => 'ban',
            'ids' => [$petition->id],
        ])->assertOk()->assertJsonPath('ok', true);

        $this->assertDatabaseHas('petitions', [
            'id' => $petition->id,
            'status' => 'draft',
            'is_active' => 0,
        ]);

        Signature::factory()->create(['petition_id' => $petition->id, 'confirmed' => true]);
        Signature::factory()->create(['petition_id' => $petition->id, 'confirmed' => false]);

        $this->postJson(route('admin.petitions.reconcile'), ['id' => $petition->id])
            ->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('actual_count', 1);

        $this->assertDatabaseHas('petitions', ['id' => $petition->id, 'signature_count' => 1]);
    }

    public function test_signature_single_row_actions_update_state_and_reject_reversal(): void
    {
        $petition = Petition::factory()->create(['signature_count' => 0]);
        $signature = Signature::factory()->create([
            'petition_id' => $petition->id,
            'confirmed' => false,
        ]);

        $this->get(route('admin.signatures'))
            ->assertOk()
            ->assertSee('data-signature-actions', false)
            ->assertSee('data-confirmed="0"', false)
            ->assertSee('data-signature-action="approve"', false)
            ->assertSee('data-signature-action="reject"', false)
            ->assertSee('data-url="'.route('admin.signatures.action', $signature).'"', false);

        $this->postJson(route('admin.signatures.action', $signature), ['action' => 'approve'])
            ->assertOk()
            ->assertJsonPath('ok', true);

        $this->assertDatabaseHas('signatures', ['id' => $signature->id, 'confirmed' => 1]);
        $this->assertDatabaseHas('petitions', ['id' => $petition->id, 'signature_count' => 1]);

        $this->postJson(route('admin.signatures.action', $signature), ['action' => 'approve'])
            ->assertStatus(409)
            ->assertJsonPath('ok', false)
            ->assertJsonPath('msg', 'Already confirmed');

        $this->postJson(route('admin.signatures.action', $signature), ['action' => 'reject'])
            ->assertOk()
            ->assertJsonPath('ok', true);

        $this->assertDatabaseHas('signatures', ['id' => $signature->id, 'confirmed' => 0]);
        $this->assertDatabaseHas('petitions', ['id' => $petition->id, 'signature_count' => 0]);

        $this->postJson(route('admin.signatures.action', $signature), ['action' => 'reject'])
            ->assertStatus(409)
            ->assertJsonPath('ok', false)
            ->assertJsonPath('msg', 'Already rejected');
    }

    public function test_user_single_ban_records_reason_and_sets_expiry(): void
    {
        $user = User::factory()->create([
            'level_id' => UserLevel::where('name', 'user')->value('id'),
        ]);

        $this->get(route('admin.users', ['select' => $user->id]))
            ->assertOk()
            ->assertSee('data-user-single-action="ban"', false);

        $this->postJson(route('admin.users.ban', $user), [
            'reason' => 'Repeated spam',
            'days' => 30,
        ])->assertOk()->assertJsonPath('ok', true);

        $user->refresh();
        $this->assertSame(UserLevel::where('name', 'banned')->value('id'), $user->level_id);
        $this->assertSame('Repeated spam', $user->banned_reason);
        $this->assertNotNull($user->banned_until);
        $this->assertTrue($user->banned_until->isAfter(now()->addDays(29)));

        $this->get(route('admin.users', ['select' => $user->id]))
            ->assertOk()
            ->assertSee('data-user-single-action="unban"', false);

        $this->postJson(route('admin.users.unban', $user), [])
            ->assertOk()
            ->assertJsonPath('ok', true);

        $user->refresh();
        $this->assertSame(UserLevel::where('name', 'user')->value('id'), $user->level_id);
        $this->assertNull($user->banned_reason);
        $this->assertNull($user->banned_until);
    }

    public function test_user_single_ban_allows_permanent_no_reason_but_rejects_invalid_duration_and_protected_targets(): void
    {
        $user = User::factory()->create([
            'level_id' => UserLevel::where('name', 'user')->value('id'),
        ]);

        $this->postJson(route('admin.users.ban', $user), ['days' => 0])
            ->assertOk()
            ->assertJsonPath('ok', true);

        $user->refresh();
        $this->assertNull($user->banned_reason);
        $this->assertNull($user->banned_until);

        $this->postJson(route('admin.users.ban', $this->admin), ['reason' => 'Test', 'days' => 0])
            ->assertForbidden()
            ->assertJsonPath('ok', false);

        $this->postJson(route('admin.users.ban', $user), ['reason' => 'Test', 'days' => 36501])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('days');
    }

    public function test_user_email_domain_search_matches_domain_suffix(): void
    {
        $matching = User::factory()->create(['email' => 'matching@domain.example']);
        User::factory()->create(['email' => 'similar@other.example']);

        $this->get(route('admin.users', ['email' => 'domain.example', 'email_mode' => 'domain']))
            ->assertOk()
            ->assertSee('name="email_mode"', false)
            ->assertSee('<option value="domain"', false)
            ->assertSee($matching->email)
            ->assertDontSee('similar@other.example');
    }

    public function test_banned_level_remains_filterable_but_not_an_editable_role(): void
    {
        $banned = User::factory()->create([
            'email' => 'banned-member@example.test',
            'level_id' => UserLevel::where('name', 'banned')->value('id'),
        ]);

        $this->get(route('admin.users', ['level' => 'banned']))
            ->assertOk()
            ->assertSee($banned->email);

        $response = $this->get(route('admin.users', ['select' => $banned->id]));
        $response->assertOk()
            ->assertSee('Banned — use the unban control above');

        $document = new \DOMDocument;
        @$document->loadHTML($response->getContent());
        $xpath = new \DOMXPath($document);
        $levelSelects = $xpath->query('//select[@name="level"]');

        $this->assertGreaterThanOrEqual(2, $levelSelects->length);

        $filterOptionValues = [];
        foreach ($levelSelects->item(0)->getElementsByTagName('option') as $option) {
            $filterOptionValues[] = $option->getAttribute('value');
        }
        $this->assertContains('banned', $filterOptionValues);

        $editOptionValues = [];
        foreach ($levelSelects->item(1)->getElementsByTagName('option') as $option) {
            $editOptionValues[] = $option->getAttribute('value');
        }
        $this->assertNotContains('banned', $editOptionValues);
    }
}
