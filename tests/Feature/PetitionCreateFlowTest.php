<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\CategoryTranslation;
use App\Models\Petition;
use App\Models\PetitionTranslation;
use App\Models\User;
use Database\Seeders\LanguageSeeder;
use Database\Seeders\UserLevelSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PetitionCreateFlowTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([UserLevelSeeder::class, LanguageSeeder::class]);
        $this->user = User::factory()->create(['verified' => true]);
        $this->category = Category::factory()->create(['is_active' => true]);
        CategoryTranslation::create([
            'category_id' => $this->category->id,
            'locale' => 'en',
            'name' => 'Test Category',
            'slug' => 'test-category',
        ]);
    }

    private function payload(array $over = []): array
    {
        return array_merge([
            'title' => 'Test Petition Title Here',
            'description' => '<p>This is a test petition with enough content to pass validation and be accepted.</p>',
            'goal_signatures' => 100,
            'category_id' => $this->category->id,
        ], $over);
    }

    public function test_valid_store_redirects_and_creates_petition(): void
    {
        $response = $this->actingAs($this->user)->post('/en/create-petition', $this->payload());
        $petition = Petition::latest('id')->firstOrFail();
        $response->assertRedirectToRoute('petition.thanks', ['locale' => 'en', 'slug' => 'test-petition-title-here', 'id' => $petition->id]);
        $this->assertDatabaseHas('petition_translations', ['petition_id' => $petition->id, 'locale' => 'en']);
    }

    public function test_short_description_fails_validation(): void
    {
        $this->actingAs($this->user)->post('/en/create-petition', $this->payload(['description' => '<p>Too short</p>']))
            ->assertSessionHasErrors('description');
    }

    public function test_validation_failure_renders_plain_linked_error_summary(): void
    {
        $response = $this->actingAs($this->user)
            ->followingRedirects()
            ->from('/en/create-petition')
            ->post('/en/create-petition', $this->payload(['description' => '<p>Too short</p>']));
        $response->assertOk()
            ->assertSee('href="#field-petition_description"', false)
            ->assertDontSee('data-alert-variant="error"', false);
    }

    public function test_invalid_country_fails_validation(): void
    {
        $this->actingAs($this->user)->post('/en/create-petition', $this->payload(['country' => 'ZZ']))
            ->assertSessionHasErrors('country');
    }

    public function test_non_owner_update_is_forbidden(): void
    {
        $other = User::factory()->create(['verified' => true]);
        $petition = Petition::factory()->create(['user_id' => $other->id, 'category_id' => $this->category->id]);
        PetitionTranslation::create(['petition_id' => $petition->id, 'locale' => 'en', 'title' => 'Original Petition Title', 'slug' => 'original-petition-title', 'description' => 'Original description.']);
        $this->actingAs($this->user)->post("/en/petition/original-petition-title/{$petition->id}/edit", $this->payload(['title' => 'Updated Petition Title']))
            ->assertForbidden();
    }

    public function test_text_only_update_keeps_cover(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('petitions/keep.png', 'image');
        $petition = Petition::factory()->create(['user_id' => $this->user->id, 'category_id' => $this->category->id, 'status' => 'published', 'is_active' => true, 'cover_image' => 'petitions/keep.png']);
        PetitionTranslation::create(['petition_id' => $petition->id, 'locale' => 'en', 'title' => 'Original Petition Title', 'slug' => 'original-petition-title', 'description' => 'Original description.']);
        $this->actingAs($this->user)->post("/en/petition/original-petition-title/{$petition->id}/edit", [
            'title' => 'Updated Petition Title Words',
            'description' => '<p>This updated description contains enough meaningful content.</p>',
            'category_id' => $this->category->id,
        ])->assertRedirect();
        $this->assertDatabaseHas('petitions', ['id' => $petition->id, 'cover_image' => 'petitions/keep.png']);
        Storage::disk('public')->assertExists('petitions/keep.png');
    }

    public function test_normal_html_description_is_not_flagged_as_spam(): void
    {
        $this->actingAs($this->user)->post('/en/create-petition', $this->payload([
            'description' => '<p>We <strong>demand</strong> action.</p><ul><li>First point here</li><li>Second point here</li></ul><p>Read more at <a href="https://example.com/info">our page</a>.</p>',
        ]))->assertRedirect();
    }
}
