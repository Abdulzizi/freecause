<?php

namespace Tests\Feature;

use App\Models\Language;
use App\Models\Petition;
use App\Models\User;
use App\Models\UserLevel;
use App\Support\ImageVariants;
use Database\Seeders\LanguageSeeder;
use Database\Seeders\UserLevelSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class E2eRegressionFixesTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([UserLevelSeeder::class, LanguageSeeder::class]);

        $this->admin = User::factory()->create([
            'email' => 'admin@example.com',
            'level_id' => UserLevel::where('name', 'admin')->value('id'),
            'verified' => true,
        ]);
    }

    public function test_admin_user_info_rejects_an_email_taken_by_another_user(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);

        $this->actingAs($this->admin, 'admin')
            ->from('/admin/system/user-info')
            ->post('/admin/system/user-info', ['email' => 'taken@example.com'])
            ->assertRedirect('/admin/system/user-info')
            ->assertSessionHasErrors('email');

        $this->assertSame('admin@example.com', $this->admin->fresh()->email);
    }

    public function test_admin_user_info_rejects_a_taken_email_typed_in_another_case(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);

        $this->actingAs($this->admin, 'admin')
            ->post('/admin/system/user-info', ['email' => ' Taken@Example.COM '])
            ->assertSessionHasErrors('email');

        $this->assertSame('admin@example.com', $this->admin->fresh()->email);
    }

    public function test_admin_user_info_still_saves_the_admins_own_email(): void
    {
        $this->actingAs($this->admin, 'admin')
            ->post('/admin/system/user-info', ['email' => 'Admin@Example.com'])
            ->assertSessionHasNoErrors();

        $this->assertSame('admin@example.com', $this->admin->fresh()->email);
    }

    public function test_admin_user_info_email_change_requires_the_current_password(): void
    {
        $this->actingAs($this->admin, 'admin')
            ->post('/admin/system/user-info', ['email' => 'new@example.com'])
            ->assertSessionHasErrors('current_password');
        $this->assertSame('admin@example.com', $this->admin->fresh()->email);

        $this->actingAs($this->admin, 'admin')
            ->post('/admin/system/user-info', ['email' => 'new@example.com', 'current_password' => 'wrong-password'])
            ->assertSessionHasErrors(['current_password' => 'Current password is incorrect.']);
        $this->assertSame('admin@example.com', $this->admin->fresh()->email);

        $passwordChangedAt = $this->admin->fresh()->password_changed_at;
        $this->actingAs($this->admin, 'admin')
            ->post('/admin/system/user-info', [
                'email' => 'new@example.com',
                'current_password' => 'wrong-password',
                'new_password' => 'new-secret-1',
                'new_password_confirmation' => 'new-secret-1',
            ])
            ->assertSessionHasErrors(['current_password' => 'Current password is incorrect.']);
        $fresh = $this->admin->fresh();
        $this->assertSame('admin@example.com', $fresh->email);
        $this->assertTrue(Hash::check('password', $fresh->password));
        $this->assertEquals($passwordChangedAt, $fresh->password_changed_at);

        $this->actingAs($this->admin, 'admin')
            ->post('/admin/system/user-info', ['email' => 'new@example.com', 'current_password' => 'password'])
            ->assertSessionHasNoErrors();
        $this->assertSame('new@example.com', $this->admin->fresh()->email);
    }

    public function test_admin_stats_with_an_unknown_type_does_not_error(): void
    {
        $this->actingAs($this->admin, 'admin')
            ->get('/admin/stats?type=bogus')
            ->assertOk();

        $this->assertFalse(Cache::has('admin:stat:type:bogus:all:30d:page:1'));
    }

    public function test_admin_cannot_create_a_language_from_the_vendor_translations_directory(): void
    {
        $this->actingAs($this->admin, 'admin')
            ->post('/admin/languages', ['code' => 'vendor', 'name' => 'Vendor'])
            ->assertSessionHasErrors('code');

        $this->assertFalse(Language::where('code', 'vendor')->exists());
    }

    public function test_reset_password_form_prefills_the_email_from_the_link(): void
    {
        $this->get('/en/reset-password/some-token?email=reset%40example.com')
            ->assertOk()
            ->assertSee('value="reset@example.com"', false);
    }

    public function test_reset_password_form_ignores_an_array_email_and_escapes_markup(): void
    {
        $this->get('/en/reset-password/some-token?email[]=x')->assertOk();

        $this->get('/en/reset-password/some-token?email='.urlencode('"><script>alert(1)</script>'))
            ->assertOk()
            ->assertDontSee('"><script>alert(1)</script>', false)
            ->assertSee('&quot;&gt;&lt;script&gt;', false);
    }

    public function test_petition_page_renders_a_cover_that_has_only_the_400w_variant(): void
    {
        // Covers uploaded while small images skipped the 800w variant still exist with only 400w.
        Storage::fake('public');
        ob_start();
        imagepng(imagecreatetruecolor(600, 400));
        $png = ob_get_clean();
        Storage::disk('public')->put('petitions/small.png', $png);
        Storage::disk('public')->put('petitions/small_400w.webp', $png);
        $this->assertNull(ImageVariants::urls('petitions/small.png'));

        $petition = Petition::factory()->create(['status' => 'published', 'is_active' => true, 'cover_image' => 'petitions/small.png']);
        $petition->translations()->create(['locale' => 'en', 'title' => 'Small cover', 'slug' => 'small-cover', 'description' => 'Description']);

        $this->get("/en/petition/small-cover/{$petition->id}")
            ->assertOk()
            ->assertSee('storage/petitions/small.png', false);
    }
}
