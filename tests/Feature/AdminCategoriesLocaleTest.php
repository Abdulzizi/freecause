<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\CategoryTranslation;
use App\Models\User;
use App\Models\UserLevel;
use Database\Seeders\LanguageSeeder;
use Database\Seeders\UserLevelSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCategoriesLocaleTest extends TestCase
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
    }

    public function test_valid_locale_still_renders_the_matching_translation(): void
    {
        $translation = CategoryTranslation::create([
            'category_id' => Category::factory()->create()->id,
            'locale' => 'de',
            'name' => 'Umwelt',
            'slug' => 'umwelt',
        ]);

        $this->actingAs($this->admin, 'admin')
            ->get('/admin/categories?locale=de')
            ->assertOk()
            ->assertSee('Umwelt')
            ->assertSee($translation->slug);
    }

    public function test_invalid_locale_redirects_instead_of_throwing(): void
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->get('/admin/categories?locale=zz');

        $response->assertRedirect(route('admin.categories', ['locale' => default_locale()]));
        $this->assertNotSame(500, $response->getStatusCode());
    }

    public function test_sql_injection_payload_is_rejected(): void
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->get("/admin/categories?locale=en' OR '1'='1");

        $response->assertRedirect(route('admin.categories', ['locale' => default_locale()]));
        $this->assertNotSame(500, $response->getStatusCode());
    }

    public function test_array_locale_is_rejected(): void
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->get('/admin/categories?locale[]=en');

        $response->assertRedirect(route('admin.categories', ['locale' => default_locale()]));
        $this->assertNotSame(500, $response->getStatusCode());
    }
}
