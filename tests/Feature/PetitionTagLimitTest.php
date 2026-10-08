<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\CategoryTranslation;
use App\Models\User;
use Database\Seeders\LanguageSeeder;
use Database\Seeders\UserLevelSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PetitionTagLimitTest extends TestCase
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

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Protect Local Forests Now',
            'description' => '<p>This description contains enough meaningful content for validation.</p>',
            'goal_signatures' => 100,
            'category_id' => $this->category->id,
        ], $overrides);
    }

    public function test_eleven_tags_are_rejected(): void
    {
        $tags = 'alpha,beta,gamma,delta,epsilon,zeta,eta,theta,iota,kappa,lambda';

        $this->actingAs($this->user)
            ->post('/en/create-petition', $this->validPayload(['tags' => $tags]))
            ->assertSessionHasErrors('tags');
    }

    public function test_tag_longer_than_thirty_characters_is_rejected(): void
    {
        $this->actingAs($this->user)
            ->post('/en/create-petition', $this->validPayload(['tags' => str_repeat('a', 31)]))
            ->assertSessionHasErrors('tags');
    }

    public function test_three_valid_tags_are_accepted(): void
    {
        $response = $this->actingAs($this->user)->post('/en/create-petition', $this->validPayload([
            'tags' => 'climate,forest,local',
        ]));

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $this->assertDatabaseHas('petitions', [
            'user_id' => $this->user->id,
            'tags' => 'climate,forest,local',
        ]);
    }
}
