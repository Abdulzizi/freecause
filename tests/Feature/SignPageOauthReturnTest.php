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
use Tests\TestCase;

class SignPageOauthReturnTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([UserLevelSeeder::class, LanguageSeeder::class]);

        $category = Category::factory()->create(['is_active' => true]);
        CategoryTranslation::create([
            'category_id' => $category->id,
            'locale' => 'en',
            'name' => 'Test Category',
            'slug' => 'test-category',
        ]);

        $this->petition = Petition::factory()->create([
            'category_id' => $category->id,
            'status' => 'published',
            'is_active' => true,
        ]);
        PetitionTranslation::create([
            'petition_id' => $this->petition->id,
            'locale' => 'en',
            'title' => 'Test Petition',
            'slug' => 'test-petition',
            'description' => 'Description',
        ]);
    }

    public function test_authenticated_get_renders_sign_confirm_page(): void
    {
        $user = User::factory()->create(['verified' => true]);

        $this->actingAs($user)
            ->get('/en/petition/test-petition/'.$this->petition->id.'/sign/confirm?oauth_logged_in=1')
            ->assertOk()
            ->assertSee('Test Petition');
    }

    public function test_guest_get_renders_the_sign_form(): void
    {
        $this->get('/en/petition/test-petition/'.$this->petition->id.'/sign/confirm')
            ->assertOk()
            ->assertSee('fc-sign-form', false)
            ->assertSee('name="_token"', false);
    }

    public function test_post_without_agreements_still_validates(): void
    {
        $user = User::factory()->create(['verified' => true]);

        $this->actingAs($user)
            ->post('/en/petition/test-petition/'.$this->petition->id.'/sign/confirm')
            ->assertSessionHasErrors(['agree1']);
    }
}
