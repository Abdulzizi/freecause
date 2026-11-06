<?php

namespace Tests\Feature;

use App\Models\PageContent;
use App\Models\User;
use App\Models\UserLevel;
use Database\Seeders\LanguageSeeder;
use Database\Seeders\UserLevelSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HeadAdditionalHtmlTest extends TestCase
{
    use RefreshDatabase;

    // Production keeps Google site-verification tags here; dropping it unverifies Search Console.
    public function test_global_head_additional_html_is_rendered_in_the_layout(): void
    {
        $this->seed(LanguageSeeder::class);
        PageContent::updateOrCreate(
            ['page' => 'global', 'locale' => 'en', 'key' => 'head_additional_html'],
            ['value' => '<meta name="google-site-verification" content="test-token-123">']
        );

        $this->get('/en')
            ->assertOk()
            ->assertSee('<meta name="google-site-verification" content="test-token-123">', false);
    }

    public function test_system_admin_saves_a_verification_meta_through_the_admin_route(): void
    {
        $this->seed([UserLevelSeeder::class, LanguageSeeder::class]);

        $admin = User::factory()->create([
            'level_id' => UserLevel::where('name', 'admin')->value('id'),
            'verified' => true,
        ]);

        $this->actingAs($admin, 'admin')
            ->post('/admin/options/language', [
                'locale' => 'en',
                'head_additional_html' => '<meta name="google-site-verification" content="admin-token-456"><script>alert(1)</script>',
            ]);

        $this->assertDatabaseHas('page_contents', [
            'page' => 'global',
            'locale' => 'en',
            'key' => 'head_additional_html',
            'value' => '<meta name="google-site-verification" content="admin-token-456" />',
        ]);

        $this->get('/en')
            ->assertOk()
            ->assertSee('<meta name="google-site-verification" content="admin-token-456" />', false)
            ->assertDontSee('alert(1)', false);
    }
}
