<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\CategoryTranslation;
use App\Models\Petition;
use App\Models\PetitionTranslation;
use App\Models\Signature;
use App\Models\User;
use Database\Seeders\LanguageSeeder;
use Database\Seeders\UserLevelSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use App\Support\Settings;
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
            'surname' => 'Signer',
            'email' => 'guest-confirm@example.com',
            'password' => 'password123',
            'agree1' => 'agree',
            'agree2' => 'agree',
            'agree3' => 'agree',
        ]);

        $confirmation->assertOk()->assertSee('Support and share your cause.');

        $this->assertDatabaseMissing('users', ['email' => 'guest-confirm@example.com']);

        $response = $this->post("/en/petition/confirm-petition/{$petition->id}/sign", [
            'agree1' => 'agree',
            'agree2' => 'agree',
            'agree3' => 'agree',
            'comment' => 'I support this petition',
        ]);

        $response->assertRedirect("/en/petition/confirm-petition/{$petition->id}/thanksforsigning/1");
        $this->followRedirects($response)->assertSee('Thank you for having signed:');
        $this->assertDatabaseHas('users', ['email' => 'guest-confirm@example.com']);
        $this->assertDatabaseHas('signatures', ['petition_id' => $petition->id, 'email' => 'guest-confirm@example.com']);
    }

    public function test_existing_account_can_sign_with_valid_password(): void
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

        $this->post("/en/petition/existing-user-petition/{$petition->id}/sign", [
            'name' => 'Existing',
            'surname' => 'User',
            'email' => $user->email,
            'password' => 'password123',
            'agree1' => 'agree',
            'agree2' => 'agree',
            'agree3' => 'agree',
        ])->assertRedirect();

        $this->assertDatabaseHas('signatures', ['petition_id' => $petition->id, 'user_id' => $user->id]);
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
            'surname' => 'User',
            'email' => 'newuser@example.com',
            'password' => 'password123',
            'comment' => 'I fully support this cause!',
            'agree1' => 'agree',
            'agree2' => 'agree',
            'agree3' => 'agree',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('users', [
            'email' => 'newuser@example.com',
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
            'surname' => 'Signer',
            'email' => 'pending@example.com',
            'password' => 'password123',
            'agree1' => 'agree',
            'agree2' => 'agree',
            'agree3' => 'agree',
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
            'surname' => 'User',
            'email' => 'spam@example.com',
            'password' => 'password123',
            'comment' => 'Buy v1agra now! http://spam.com',
            'agree1' => 'agree',
            'agree2' => 'agree',
            'agree3' => 'agree',
        ]);

        $response->assertSessionHas('toast.type', 'error');
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
}
