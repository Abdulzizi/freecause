<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\CategoryTranslation;
use App\Models\Petition;
use App\Models\PetitionTranslation;
use App\Models\Signature;
use App\Models\User;
use App\Support\Settings;
use Database\Seeders\LanguageSeeder;
use Database\Seeders\UserLevelSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PetitionTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([UserLevelSeeder::class, LanguageSeeder::class]);

        $this->user = User::factory()->create([
            'verified' => true,
        ]);

        $this->category = Category::factory()->create(['is_active' => true]);
        CategoryTranslation::create([
            'category_id' => $this->category->id,
            'locale' => 'en',
            'name' => 'Test Category',
            'slug' => 'test-category',
        ]);
    }

    public function test_petition_index_page_loads(): void
    {
        $response = $this->get('/en/petitions');
        $response->assertStatus(200);
    }

    public function test_petition_search_filters_published_petitions(): void
    {
        $matching = Petition::factory()->create(['status' => 'published', 'is_active' => true]);
        $other = Petition::factory()->create(['status' => 'published', 'is_active' => true]);

        PetitionTranslation::create([
            'petition_id' => $matching->id,
            'locale' => 'en',
            'title' => 'Protect Local Forests',
            'slug' => 'protect-local-forests',
            'description' => 'Description',
        ]);
        PetitionTranslation::create([
            'petition_id' => $other->id,
            'locale' => 'en',
            'title' => 'Improve Public Transit',
            'slug' => 'improve-public-transit',
            'description' => 'Description',
        ]);

        $this->get('/en/petitions?q=Forest')
            ->assertOk()
            ->assertSee('Protect Local Forests')
            ->assertDontSee('Improve Public Transit');
    }

    public function test_category_search_filters_only_that_category(): void
    {
        $matching = Petition::factory()->create([
            'category_id' => $this->category->id,
            'status' => 'published',
            'is_active' => true,
        ]);
        $other = Petition::factory()->create([
            'category_id' => $this->category->id,
            'status' => 'published',
            'is_active' => true,
        ]);

        PetitionTranslation::create([
            'petition_id' => $matching->id,
            'locale' => 'en',
            'title' => 'Protect Test Animals',
            'slug' => 'protect-test-animals',
            'description' => 'Description',
        ]);
        PetitionTranslation::create([
            'petition_id' => $other->id,
            'locale' => 'en',
            'title' => 'Build Safer Roads',
            'slug' => 'build-safer-roads',
            'description' => 'Description',
        ]);

        $this->get("/en/petitions/category-test-category-{$this->category->id}?q=Animals")
            ->assertOk()
            ->assertSee('Protect Test Animals')
            ->assertDontSee('Build Safer Roads');

        $this->get("/en/petitions/category-test-category-{$this->category->id}?q=Missing")
            ->assertOk()
            ->assertSee('No petitions found.');
    }

    public function test_category_index_page_loads(): void
    {
        $this->get("/en/petitions/category-test-category-{$this->category->id}")
            ->assertOk();
    }

    public function test_deep_pagination_page_is_clamped(): void
    {
        Petition::factory()->create(['status' => 'published', 'is_active' => true]);

        $this->get('/en/petitions?page=99999')
            ->assertOk();
        $this->get("/en/petitions/category-test-category-{$this->category->id}?page=99999")
            ->assertOk();
    }

    public function test_shoutbox_and_signatures_deep_pagination_is_clamped(): void
    {
        $petition = Petition::factory()->create(['status' => 'published', 'is_active' => true]);
        PetitionTranslation::create([
            'petition_id' => $petition->id,
            'locale' => 'en',
            'title' => 'Deep Pagination Petition',
            'slug' => 'deep-pagination-petition',
            'description' => 'Description',
        ]);

        $this->get("/en/petition/deep-pagination-petition/{$petition->id}?shoutbox=99999")
            ->assertOk();
        $this->get("/en/petition/deep-pagination-petition/{$petition->id}/signatures?page=99999")
            ->assertOk();
    }

    public function test_petition_search_pagination_preserves_query(): void
    {
        foreach (range(1, 16) as $number) {
            $petition = Petition::factory()->create(['status' => 'published', 'is_active' => true]);
            PetitionTranslation::create([
                'petition_id' => $petition->id,
                'locale' => 'en',
                'title' => "Forest Campaign {$number}",
                'slug' => "forest-campaign-{$number}",
                'description' => 'Description',
            ]);
        }

        $this->get('/en/petitions?q=Forest')
            ->assertOk()
            ->assertSee('page=2', false)
            ->assertSee('q=Forest', false);
    }

    public function test_can_view_published_petition(): void
    {
        $petition = Petition::factory()->create([
            'status' => 'published',
            'is_active' => true,
        ]);

        PetitionTranslation::create([
            'petition_id' => $petition->id,
            'locale' => 'en',
            'title' => 'Test Petition',
            'slug' => 'test-petition',
            'description' => '<p>Test description</p>',
        ]);

        $response = $this->get("/en/petition/test-petition/{$petition->id}");
        $response->assertStatus(200);
    }

    public function test_shoutbox_only_contains_real_signing_comments(): void
    {
        $petition = Petition::factory()->create([
            'status' => 'published',
            'is_active' => true,
        ]);

        PetitionTranslation::create([
            'petition_id' => $petition->id,
            'locale' => 'en',
            'title' => 'Shoutbox Petition',
            'slug' => 'shoutbox-petition',
            'description' => 'Description',
        ]);

        Signature::factory()->create([
            'petition_id' => $petition->id,
            'user_id' => $this->user->id,
            'name' => 'Visible Supporter',
            'text' => 'This cause matters to our community.',
            'confirmed' => true,
            'is_spam' => false,
        ]);

        Signature::factory()->create([
            'petition_id' => $petition->id,
            'text' => 'I support this petition',
            'confirmed' => true,
            'is_spam' => false,
        ]);

        $this->get("/en/petition/shoutbox-petition/{$petition->id}")
            ->assertOk()
            ->assertSee('This cause matters to our community.')
            ->assertViewHas('shoutbox', fn ($comments) => $comments->pluck('text')->all() === [
                'This cause matters to our community.',
            ]);
    }

    public function test_petition_view_is_counted_once_per_session(): void
    {
        $petition = Petition::factory()->create(['status' => 'published', 'is_active' => true]);
        PetitionTranslation::create([
            'petition_id' => $petition->id,
            'locale' => 'en',
            'title' => 'Viewed Petition',
            'slug' => 'viewed-petition',
            'description' => 'Description',
        ]);

        $this->get("/en/petition/viewed-petition/{$petition->id}")->assertOk();
        $this->get("/en/petition/viewed-petition/{$petition->id}")->assertOk();

        $this->assertDatabaseHas('petitions', ['id' => $petition->id, 'views' => 1]);
        $this->assertDatabaseHas('petition_views', ['petition_id' => $petition->id, 'views' => 1]);
    }

    public function test_inactive_published_petition_shows_offline_page(): void
    {
        $petition = Petition::factory()->create(['status' => 'published', 'is_active' => false]);
        PetitionTranslation::create([
            'petition_id' => $petition->id,
            'locale' => 'en',
            'title' => 'Offline Petition',
            'slug' => 'offline-petition',
            'description' => 'Description',
        ]);

        $this->get("/en/petition/offline-petition/{$petition->id}")
            ->assertOk()
            ->assertSee('This petition is currently offline.');
    }

    public function test_guest_signing_uses_confirmation_step(): void
    {
        $petition = Petition::factory()->create(['status' => 'published', 'is_active' => true]);
        PetitionTranslation::create([
            'petition_id' => $petition->id,
            'locale' => 'en',
            'title' => 'Confirm Petition',
            'slug' => 'confirm-petition',
            'description' => 'Description',
        ]);

        $confirmation = $this->post("/en/petition/confirm-petition/{$petition->id}/sign/confirm", [
            'name' => 'Guest',
            'email' => 'guest-confirm@example.com',
            'comment' => 'I support this petition',
            'agree1' => '1',
            'agree2' => '1',
            'agree3' => '1',
        ]);

        $confirmation->assertOk()->assertSee('Support and share your cause.');

        $this->assertDatabaseMissing('users', ['email' => 'guest-confirm@example.com']);

        $response = $this->post("/en/petition/confirm-petition/{$petition->id}/sign", [
            'agree1' => '1',
            'agree2' => '1',
            'agree3' => '1',
            'comment' => 'I support this petition',
        ]);

        $response->assertRedirect("/en/petition/confirm-petition/{$petition->id}/thanksforsigning/1");
        $this->followRedirects($response)->assertSee('Thank you for having signed:');
        $this->assertDatabaseHas('users', ['email' => 'guest-confirm@example.com']);
        $this->assertDatabaseHas('signatures', ['petition_id' => $petition->id, 'email' => 'guest-confirm@example.com']);
    }

    public function test_thanks_page_uses_the_signed_petition_locale(): void
    {
        $petition = Petition::factory()->create([
            'status' => 'published',
            'is_active' => true,
        ]);
        PetitionTranslation::create([
            'petition_id' => $petition->id,
            'locale' => 'pt',
            'title' => 'Proteja a rede elétrica',
            'slug' => 'proteja-a-rede-eletrica',
            'description' => 'Descrição da petição em português.',
        ]);

        $suggestion = Petition::factory()->create([
            'status' => 'published',
            'is_active' => true,
            'signature_count' => 50,
        ]);
        PetitionTranslation::create([
            'petition_id' => $suggestion->id,
            'locale' => 'pt',
            'title' => 'Melhore os transportes públicos',
            'slug' => 'melhore-os-transportes-publicos',
            'description' => 'Descrição da sugestão em português.',
        ]);

        $englishSuggestion = Petition::factory()->create([
            'status' => 'published',
            'is_active' => true,
            'signature_count' => 100,
        ]);
        PetitionTranslation::create([
            'petition_id' => $englishSuggestion->id,
            'locale' => 'en',
            'title' => 'English-only recommendation',
            'slug' => 'english-only-recommendation',
            'description' => 'English description.',
        ]);

        $response = $this->post("/en/petition/proteja-a-rede-eletrica/{$petition->id}/sign", [
            'name' => 'Visitante',
            'email' => 'visitante-portugues@example.com',
            'comment' => 'Apoio esta petição.',
            'agree1' => '1',
            'agree2' => '1',
            'agree3' => '1',
        ]);

        $thanksUrl = "/pt/petition/proteja-a-rede-eletrica/{$petition->id}/thanksforsigning/1";

        $response->assertRedirect($thanksUrl);

        $this->get($thanksUrl)
            ->assertOk()
            ->assertSee('Obrigado por assinar:')
            ->assertSee('Petições que poderão interessar-lhe')
            ->assertSee('Melhore os transportes públicos')
            ->assertDontSee('English-only recommendation')
            ->assertDontSee('Thank you for having signed:')
            ->assertDontSee('Petitions you might like');

        $this->get("/en/petition/proteja-a-rede-eletrica/{$petition->id}/thanksforsigning/1")
            ->assertRedirect($thanksUrl);
    }

    public function test_thanks_page_share_button_uses_mailto_fallback_with_petition_details(): void
    {
        $petition = Petition::factory()->create([
            'status' => 'published',
            'is_active' => true,
        ]);
        PetitionTranslation::create([
            'petition_id' => $petition->id,
            'locale' => 'en',
            'title' => 'Protect Trees & Rivers',
            'slug' => 'protect-trees-rivers',
            'description' => 'Description',
        ]);

        $petitionUrl = route('petition.show', [
            'locale' => 'en',
            'slug' => 'protect-trees-rivers',
            'id' => $petition->id,
        ]);
        $mailUrl = 'mailto:?'.http_build_query([
            'subject' => 'Protect Trees & Rivers',
            'body' => "Protect Trees & Rivers\r\n\r\n{$petitionUrl}",
        ], '', '&', PHP_QUERY_RFC3986);

        $this->get("/en/petition/protect-trees-rivers/{$petition->id}/thanksforsigning/1")
            ->assertOk()
            ->assertSee('data-share-petition', false)
            ->assertSee('data-share-title="Protect Trees &amp; Rivers"', false)
            ->assertSee('data-share-url="'.e($petitionUrl).'"', false)
            ->assertSee('href="'.e($mailUrl).'"', false);
    }

    public function test_existing_email_sign_attributes_to_account(): void
    {
        $user = User::factory()->create([
            'email' => 'existing@example.com',
            'password' => Hash::make('password123'),
            'verified' => false,
        ]);
        $petition = Petition::factory()->create(['status' => 'published', 'is_active' => true]);
        PetitionTranslation::create([
            'petition_id' => $petition->id,
            'locale' => 'en',
            'title' => 'Existing User Petition',
            'slug' => 'existing-user-petition',
            'description' => 'Description',
        ]);

        $usersBefore = User::count();

        $this->post("/en/petition/existing-user-petition/{$petition->id}/sign", [
            'name' => 'Existing',
            'email' => $user->email,
            'agree1' => '1',
            'agree2' => '1',
            'agree3' => '1',
        ])->assertRedirect();

        $this->assertDatabaseHas('signatures', ['petition_id' => $petition->id, 'user_id' => $user->id]);
        $this->assertSame($usersBefore, User::count());
    }

    public function test_petition_widget_script_and_render_endpoints_work(): void
    {
        $petition = Petition::factory()->create(['status' => 'published', 'is_active' => true]);
        PetitionTranslation::create([
            'petition_id' => $petition->id,
            'locale' => 'en',
            'title' => 'Widget Petition',
            'slug' => 'widget-petition',
            'description' => 'Description',
        ]);

        $this->get("/widgets/pbadge/{$petition->id}/728x90")
            ->assertOk()
            ->assertHeader('Content-Type', 'text/javascript; charset=UTF-8')
            ->assertSee("/widget-render/pbadge/{$petition->id}/728x90", false);

        $this->get("/widget-render/pbadge/{$petition->id}/728x90")
            ->assertOk()
            ->assertSee('Widget Petition');
    }

    public function test_cannot_sign_draft_petition(): void
    {
        $petition = Petition::factory()->create([
            'status' => 'draft',
            'is_active' => false,
        ]);

        PetitionTranslation::create([
            'petition_id' => $petition->id,
            'locale' => 'en',
            'title' => 'Draft Petition',
            'slug' => 'draft-petition',
            'description' => 'Draft petition description.',
        ]);

        $response = $this->post("/en/petition/draft-petition/{$petition->id}/sign", [
            'name' => 'Test User',
            'surname' => 'Last',
            'email' => 'test@example.com',
            'password' => 'password123',
            'agree1' => 'agree',
            'agree2' => 'agree',
            'agree3' => 'agree',
        ]);

        $response->assertStatus(404);
    }

    public function test_cannot_sign_inactive_petition(): void
    {
        $petition = Petition::factory()->create([
            'status' => 'published',
            'is_active' => false,
        ]);

        PetitionTranslation::create([
            'petition_id' => $petition->id,
            'locale' => 'en',
            'title' => 'Inactive Petition',
            'slug' => 'inactive-petition',
            'description' => 'Inactive petition description.',
        ]);

        $response = $this->post("/en/petition/inactive-petition/{$petition->id}/sign", [
            'name' => 'Test User',
            'surname' => 'Last',
            'email' => 'test@example.com',
            'password' => 'password123',
            'agree1' => 'agree',
            'agree2' => 'agree',
            'agree3' => 'agree',
        ]);

        $response->assertStatus(404);
    }

    public function test_authenticated_user_can_sign_petition(): void
    {
        $petition = Petition::factory()->create([
            'status' => 'published',
            'is_active' => true,
            'signature_count' => 0,
        ]);

        PetitionTranslation::create([
            'petition_id' => $petition->id,
            'locale' => 'en',
            'title' => 'Test Petition',
            'slug' => 'test-petition',
            'description' => 'Test petition description.',
        ]);

        $response = $this->actingAs($this->user)->post("/en/petition/test-petition/{$petition->id}/sign", [
            'comment' => 'I support this!',
            'agree1' => 'agree',
            'agree2' => 'agree',
            'agree3' => 'agree',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('signatures', [
            'petition_id' => $petition->id,
            'email' => $this->user->email,
            'confirmed' => true,
        ]);

        $response->assertRedirect("/en/petition/test-petition/{$petition->id}/thanksforsigning/1");

        $petition->refresh();
        $this->assertEquals(1, $petition->signature_count);
    }

    public function test_guest_can_sign_petition_and_creates_account(): void
    {
        $petition = Petition::factory()->create([
            'status' => 'published',
            'is_active' => true,
            'signature_count' => 0,
        ]);

        PetitionTranslation::create([
            'petition_id' => $petition->id,
            'locale' => 'en',
            'title' => 'Test Petition',
            'slug' => 'test-petition',
            'description' => 'Test petition description.',
        ]);

        $response = $this->post("/en/petition/test-petition/{$petition->id}/sign", [
            'name' => 'Test',
            'email' => 'newuser@example.com',
            'comment' => 'I fully support this cause!',
            'agree1' => '1',
            'agree2' => '1',
            'agree3' => '1',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('users', [
            'email' => 'newuser@example.com',
            'password' => null,
        ]);

        $this->assertDatabaseHas('signatures', [
            'petition_id' => $petition->id,
            'email' => 'newuser@example.com',
        ]);
    }

    public function test_verifying_guest_confirms_signature_and_updates_counter(): void
    {
        Settings::set('require_email_verification', true);

        $petition = Petition::factory()->create([
            'status' => 'published',
            'is_active' => true,
            'signature_count' => 0,
        ]);
        PetitionTranslation::create([
            'petition_id' => $petition->id,
            'locale' => 'en',
            'title' => 'Verification Petition',
            'slug' => 'verification-petition',
            'description' => 'Test petition description.',
        ]);

        $this->post("/en/petition/verification-petition/{$petition->id}/sign", [
            'name' => 'Pending',
            'email' => 'pending@example.com',
            'agree1' => '1',
            'agree2' => '1',
            'agree3' => '1',
        ])->assertRedirect("/en/petition/verification-petition/{$petition->id}/thanksforsigning/0");

        $user = User::where('email', 'pending@example.com')->firstOrFail();
        $this->assertNotNull($user->verification_token_sent_at);
        $this->assertDatabaseHas('signatures', [
            'petition_id' => $petition->id,
            'user_id' => $user->id,
            'confirmed' => false,
        ]);
        $this->assertDatabaseHas('petitions', ['id' => $petition->id, 'signature_count' => 0]);

        $this->get("/en/verify/{$user->verification_token}")->assertRedirect('/en');

        $this->assertDatabaseHas('users', ['id' => $user->id, 'verified' => true]);
        $this->assertDatabaseHas('signatures', [
            'petition_id' => $petition->id,
            'user_id' => $user->id,
            'confirmed' => true,
        ]);
        $this->assertDatabaseHas('petitions', ['id' => $petition->id, 'signature_count' => 1]);
    }

    public function test_cannot_sign_same_petition_twice(): void
    {
        $petition = Petition::factory()->create([
            'status' => 'published',
            'is_active' => true,
        ]);

        PetitionTranslation::create([
            'petition_id' => $petition->id,
            'locale' => 'en',
            'title' => 'Test Petition',
            'slug' => 'test-petition',
            'description' => 'Test petition description.',
        ]);

        Signature::create([
            'petition_id' => $petition->id,
            'user_id' => $this->user->id,
            'email' => $this->user->email,
            'name' => $this->user->name,
            'locale' => 'en',
        ]);

        $response = $this->actingAs($this->user)->post("/en/petition/test-petition/{$petition->id}/sign", [
            'comment' => 'I support this!',
            'agree1' => 'agree',
            'agree2' => 'agree',
            'agree3' => 'agree',
        ]);

        $response->assertSessionHas('toast.type', 'info');
    }

    public function test_guest_create_page_shows_tabbed_authentication_without_petition_form(): void
    {
        $this->get('/en/create-petition')
            ->assertOk()
            ->assertSee('id="create-login-tab"', false)
            ->assertSee('id="create-register-tab"', false)
            ->assertSee('id="create-login-pane"', false)
            ->assertDontSee('data-petition-form', false);
    }

    public function test_authenticated_create_page_shows_petition_form_without_authentication_tabs(): void
    {
        $this->actingAs($this->user)->get('/en/create-petition')
            ->assertOk()
            ->assertSee('data-petition-form', false)
            ->assertSee('class="text-center! max-md:[&_.fcui-btn]:w-full!"', false)
            ->assertDontSee('position: fixed;', false)
            ->assertDontSee('id="create-login-tab"', false)
            ->assertDontSee('id="create-register-tab"', false);
    }

    public function test_petition_creation_requires_authentication(): void
    {
        $response = $this->post('/en/create-petition', [
            'title' => 'Test Petition Title Here',
            'description' => '<p>This is a test petition with enough content to pass validation.</p>',
            'goal_signatures' => 100,
            'category_id' => $this->category->id,
        ]);

        $response->assertRedirect('/en/login');
    }

    public function test_authenticated_user_can_create_petition(): void
    {
        $response = $this->actingAs($this->user)->post('/en/create-petition', [
            'title' => 'Test Petition Title Here',
            'description' => '<p>This is a test petition with enough content to pass validation and be accepted.</p>',
            'goal_signatures' => 100,
            'category_id' => $this->category->id,
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('petitions', [
            'user_id' => $this->user->id,
            'status' => 'draft',
        ]);
    }

    public function test_inactive_category_cannot_be_used_for_creation(): void
    {
        $inactive = Category::factory()->create(['is_active' => false]);

        $this->actingAs($this->user)->post('/en/create-petition', [
            'title' => 'Test Petition Title Here',
            'description' => '<p>This description contains enough meaningful content for validation.</p>',
            'goal_signatures' => 100,
            'category_id' => $inactive->id,
        ])->assertSessionHasErrors('category_id');
    }

    public function test_creation_accepts_legacy_youtube_id_and_extensionless_image_url(): void
    {
        $this->actingAs($this->user)->post('/en/create-petition', [
            'title' => 'Test Petition Title Here',
            'description' => '<p>This description contains enough meaningful content for validation.</p>',
            'goal_signatures' => 100,
            'category_id' => $this->category->id,
            'youtube' => 'dQw4w9WgXcQ',
            'image_url' => 'https://images.example.com/render?id=123',
        ])->assertRedirect();

        $this->assertDatabaseHas('petitions', [
            'user_id' => $this->user->id,
            'youtube_url' => 'dQw4w9WgXcQ',
            'image_url' => 'https://images.example.com/render?id=123',
        ]);
    }

    public function test_uploaded_image_keeps_format_and_aspect_ratio(): void
    {
        Storage::fake('public');

        $this->actingAs($this->user)->post('/en/create-petition', [
            'title' => 'Test Petition Title Here',
            'description' => '<p>This description contains enough meaningful content for validation.</p>',
            'goal_signatures' => 100,
            'category_id' => $this->category->id,
            'image' => UploadedFile::fake()->image('portrait.png', 400, 1200),
        ])->assertRedirect();

        $petition = Petition::where('user_id', $this->user->id)->latest('id')->firstOrFail();
        [$width, $height, $type] = getimagesize(Storage::disk('public')->path($petition->cover_image));

        $this->assertSame(267, $width);
        $this->assertSame(800, $height);
        $this->assertSame(IMAGETYPE_PNG, $type);
    }

    public function test_webp_image_is_stored_and_linked_to_created_petition(): void
    {
        Storage::fake('public');

        $response = $this->actingAs($this->user)->post('/en/create-petition', [
            'title' => 'Test Petition Title Here',
            'description' => '<p>This description contains enough meaningful content for validation.</p>',
            'goal_signatures' => 100,
            'category_id' => $this->category->id,
            'image' => UploadedFile::fake()->image('petition.webp', 1200, 600),
        ]);

        $response->assertSessionHasNoErrors()->assertRedirect();

        $petition = Petition::where('user_id', $this->user->id)->latest('id')->firstOrFail();
        $this->assertNotNull($petition->cover_image);
        Storage::disk('public')->assertExists($petition->cover_image);
        $this->assertSame(IMAGETYPE_WEBP, getimagesize(Storage::disk('public')->path($petition->cover_image))[2]);
    }

    public function test_oversized_image_does_not_create_petition(): void
    {
        Storage::fake('public');

        $this->actingAs($this->user)->post('/en/create-petition', [
            'title' => 'Test Petition Title Here',
            'description' => '<p>This description contains enough meaningful content for validation.</p>',
            'goal_signatures' => 100,
            'category_id' => $this->category->id,
            'image' => UploadedFile::fake()->create('large.webp', 4097, 'image/webp'),
        ])->assertSessionHasErrors('image');

        $this->assertDatabaseMissing('petitions', ['user_id' => $this->user->id]);
    }

    public function test_php_rejected_upload_does_not_create_petition(): void
    {
        $rejectedUpload = new UploadedFile(
            '',
            'large.webp',
            'image/webp',
            UPLOAD_ERR_INI_SIZE,
            true,
        );

        $this->actingAs($this->user)->post('/en/create-petition', [
            'title' => 'Test Petition Title Here',
            'description' => '<p>This description contains enough meaningful content for validation.</p>',
            'goal_signatures' => 100,
            'category_id' => $this->category->id,
            'image' => $rejectedUpload,
        ])->assertSessionHasErrors('image');

        $this->assertDatabaseMissing('petitions', ['user_id' => $this->user->id]);
    }

    public function test_image_file_and_external_url_cannot_be_submitted_together(): void
    {
        Storage::fake('public');

        $this->actingAs($this->user)->post('/en/create-petition', [
            'title' => 'Test Petition Title Here',
            'description' => '<p>This description contains enough meaningful content for validation.</p>',
            'goal_signatures' => 100,
            'category_id' => $this->category->id,
            'image' => UploadedFile::fake()->image('petition.webp'),
            'image_url' => 'https://images.example.com/petition.webp',
        ])->assertSessionHasErrors(['image', 'image_url']);

        $this->assertDatabaseMissing('petitions', ['user_id' => $this->user->id]);
    }

    public function test_owner_cannot_edit_after_another_user_signed(): void
    {
        $petition = Petition::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
        ]);
        PetitionTranslation::create([
            'petition_id' => $petition->id,
            'locale' => 'en',
            'title' => 'Original Petition Title',
            'slug' => 'original-petition-title',
            'description' => 'Original petition description.',
        ]);
        $signer = User::factory()->create();
        Signature::create([
            'petition_id' => $petition->id,
            'user_id' => $signer->id,
            'name' => $signer->name,
            'email' => $signer->email,
            'locale' => 'en',
        ]);

        $this->actingAs($this->user)
            ->get("/en/petition/original-petition-title/{$petition->id}/edit")
            ->assertForbidden();
    }

    public function test_edit_updates_slug_but_preserves_goal(): void
    {
        $petition = Petition::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'goal_signatures' => 100,
        ]);
        PetitionTranslation::create([
            'petition_id' => $petition->id,
            'locale' => 'en',
            'title' => 'Original Petition Title',
            'slug' => 'original-petition-title',
            'description' => 'Original petition description.',
        ]);

        $this->actingAs($this->user)->post("/en/petition/original-petition-title/{$petition->id}/edit", [
            'title' => 'Updated Petition Title',
            'description' => '<p>This updated description contains enough meaningful content.</p>',
            'category_id' => $this->category->id,
            'goal_signatures' => 500,
        ])->assertRedirect("/en/petition/updated-petition-title/{$petition->id}");

        $this->assertDatabaseHas('petitions', ['id' => $petition->id, 'goal_signatures' => 100]);
        $this->assertDatabaseHas('petition_translations', [
            'petition_id' => $petition->id,
            'slug' => 'updated-petition-title',
        ]);
    }

    public function test_edit_can_remove_current_image_without_orphaning_file(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('petitions/original.png', 'image');

        $petition = Petition::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'cover_image' => 'petitions/original.png',
        ]);
        PetitionTranslation::create([
            'petition_id' => $petition->id,
            'locale' => 'en',
            'title' => 'Original Petition Title',
            'slug' => 'original-petition-title',
            'description' => 'Original petition description.',
        ]);

        $this->actingAs($this->user)->post("/en/petition/original-petition-title/{$petition->id}/edit", [
            'title' => 'Original Petition Title',
            'description' => '<p>This updated description contains enough meaningful content.</p>',
            'category_id' => $this->category->id,
            'remove_image' => 1,
        ])->assertRedirect();

        Storage::disk('public')->assertMissing('petitions/original.png');
        $this->assertDatabaseHas('petitions', ['id' => $petition->id, 'cover_image' => null]);
    }

    public function test_petition_title_requires_minimum_words(): void
    {
        $response = $this->actingAs($this->user)->post('/en/create-petition', [
            'title' => 'Short',
            'description' => '<p>This is a test petition with enough content to pass validation and be accepted.</p>',
            'goal_signatures' => 100,
            'category_id' => $this->category->id,
        ]);

        $response->assertSessionHasErrors('title');
    }

    public function test_petition_description_requires_minimum_length(): void
    {
        $response = $this->actingAs($this->user)->post('/en/create-petition', [
            'title' => 'Test Petition Title Here',
            'description' => '<p>Too short</p>',
            'goal_signatures' => 100,
            'category_id' => $this->category->id,
        ]);

        $response->assertSessionHasErrors('description');
    }

    public function test_goal_signatures_must_be_valid_value(): void
    {
        $response = $this->actingAs($this->user)->post('/en/create-petition', [
            'title' => 'Test Petition Title Here',
            'description' => '<p>This is a test petition with enough content to pass validation and be accepted.</p>',
            'goal_signatures' => 500,
            'category_id' => $this->category->id,
        ]);

        $response->assertSessionHasErrors('goal_signatures');
    }

    public function test_spam_content_is_rejected(): void
    {
        $petition = Petition::factory()->create([
            'status' => 'published',
            'is_active' => true,
        ]);

        PetitionTranslation::create([
            'petition_id' => $petition->id,
            'locale' => 'en',
            'title' => 'Test Petition',
            'slug' => 'test-petition',
            'description' => 'Test petition description.',
        ]);

        $response = $this->post("/en/petition/test-petition/{$petition->id}/sign", [
            'name' => 'Test',
            'email' => 'spam@example.com',
            'comment' => 'Buy v1agra now! http://spam.com',
            'agree1' => '1',
            'agree2' => '1',
            'agree3' => '1',
        ]);

        $response->assertSessionHas('toast.type', 'error');
        $this->assertDatabaseMissing('signatures', ['petition_id' => $petition->id, 'email' => 'spam@example.com']);
    }

    public function test_signature_rate_limited(): void
    {
        $petition = Petition::factory()->create([
            'status' => 'published',
            'is_active' => true,
        ]);

        PetitionTranslation::create([
            'petition_id' => $petition->id,
            'locale' => 'en',
            'title' => 'Test Petition',
            'slug' => 'test-petition',
            'description' => 'Test petition description.',
        ]);

        for ($i = 0; $i < 10; $i++) {
            $this->post("/en/petition/test-petition/{$petition->id}/sign", [
                'name' => 'Test',
                'surname' => 'User',
                'email' => "user{$i}@example.com",
                'password' => 'password123',
                'comment' => 'I support this!',
                'agree1' => 'agree',
                'agree2' => 'agree',
                'agree3' => 'agree',
            ]);
        }

        $response = $this->post("/en/petition/test-petition/{$petition->id}/sign", [
            'name' => 'Test',
            'surname' => 'User',
            'email' => 'toomany@example.com',
            'password' => 'password123',
            'comment' => 'I support this!',
            'agree1' => 'agree',
            'agree2' => 'agree',
            'agree3' => 'agree',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors();
    }

    public function test_agree_checkboxes_required(): void
    {
        $petition = $this->makePublishedPetition('agree-required-petition');

        $response = $this->post("/en/petition/agree-required-petition/{$petition->id}/sign", [
            'name' => 'Agree',
            'email' => 'agree-required@example.com',
            'comment' => 'I support this petition',
            'agree1' => '1',
            'agree3' => '1',
        ]);

        $response->assertStatus(302)->assertSessionHasErrors('agree2');
        $this->assertDatabaseMissing('signatures', ['petition_id' => $petition->id]);
        $this->assertDatabaseMissing('users', ['email' => 'agree-required@example.com']);
    }

    public function test_legacy_agree_value_rejected(): void
    {
        $petition = $this->makePublishedPetition('legacy-agree-petition');

        $response = $this->post("/en/petition/legacy-agree-petition/{$petition->id}/sign", [
            'name' => 'Legacy',
            'email' => 'legacy-agree@example.com',
            'comment' => 'I support this petition',
            'agree1' => 'agree',
            'agree2' => '1',
            'agree3' => '1',
        ]);

        $response->assertStatus(302)->assertSessionHasErrors('agree1');
        $this->assertDatabaseMissing('signatures', ['petition_id' => $petition->id]);
    }

    public function test_claim_endpoint_sets_password_once(): void
    {
        $petition = $this->makePublishedPetition('claim-once-petition');
        $email = 'claim-once@example.com';
        $signUrl = "/en/petition/claim-once-petition/{$petition->id}";

        $this->post("{$signUrl}/sign/confirm", [
            'name' => 'Claim',
            'email' => $email,
            'comment' => 'I support this petition',
            'agree1' => '1',
            'agree2' => '1',
            'agree3' => '1',
        ])->assertOk();

        $usersBeforeSign = User::count();

        $this->post("{$signUrl}/sign", [
            'agree1' => '1',
            'agree2' => '1',
            'agree3' => '1',
            'comment' => 'I support this petition',
        ])->assertRedirect("{$signUrl}/thanksforsigning/1");

        $this->get("{$signUrl}/thanksforsigning/1")
            ->assertOk()
            ->assertViewHas('canClaim', true);

        $this->post("{$signUrl}/claim", ['password' => 'secret123'])
            ->assertRedirect('/en/login');

        $user = User::where('email', $email)->firstOrFail();
        $this->assertNotNull($user->password);
        $this->assertTrue(Hash::check('secret123', $user->password));

        // claim session is consumed on success, so a repeat attempt 404s instead of re-validating
        $this->post("{$signUrl}/claim", ['password' => 'secret123'])->assertStatus(404);

        // exactly one new user from signing (the petition owner user already existed)
        $this->assertSame($usersBeforeSign + 1, User::count());
        $this->assertSame(1, User::where('email', $email)->count());
    }

    public function test_claim_rejected_when_account_already_has_password(): void
    {
        $user = User::factory()->create([
            'email' => 'passworded@example.com',
            'password' => Hash::make('password123'),
            'verified' => true,
        ]);
        $petition = $this->makePublishedPetition('claim-passworded-petition');
        $signUrl = "/en/petition/claim-passworded-petition/{$petition->id}";

        $this->post("{$signUrl}/sign", [
            'name' => 'Existing',
            'email' => $user->email,
            'comment' => 'I support this petition',
            'agree1' => '1',
            'agree2' => '1',
            'agree3' => '1',
        ])->assertRedirect("{$signUrl}/thanksforsigning/1");

        $this->post("{$signUrl}/claim", ['password' => 'secret123'])
            ->assertStatus(302)
            ->assertSessionHasErrors(['password' => 'This account already has a password — please log in.']);

        $this->assertTrue(Hash::check('password123', $user->fresh()->password));
    }

    public function test_double_submit_race_gets_friendly_error(): void
    {
        $petition = $this->makePublishedPetition('double-submit-petition');
        $url = "/en/petition/double-submit-petition/{$petition->id}/sign";
        $payload = [
            'name' => 'Double',
            'email' => 'double-submit@example.com',
            'comment' => 'I support this petition',
            'agree1' => '1',
            'agree2' => '1',
            'agree3' => '1',
        ];

        $this->post($url, $payload)->assertRedirect();

        $second = $this->post($url, $payload);
        $second->assertStatus(302)->assertSessionHasErrors('email');

        $this->assertSame(1, Signature::where('petition_id', $petition->id)->count());
        $this->assertSame(1, User::where('email', 'double-submit@example.com')->count());
    }

    private function makePublishedPetition(string $slug): Petition
    {
        $petition = Petition::factory()->create([
            'status' => 'published',
            'is_active' => true,
            'signature_count' => 0,
        ]);

        PetitionTranslation::create([
            'petition_id' => $petition->id,
            'locale' => 'en',
            'title' => ucfirst(str_replace('-', ' ', $slug)),
            'slug' => $slug,
            'description' => 'Description',
        ]);

        return $petition;
    }
}
