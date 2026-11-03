<?php

namespace Tests\Feature;

use App\Models\Petition;
use App\Models\PetitionTranslation;
use App\Models\User;
use App\Models\UserLevel;
use App\Support\CacheKeys;
use Database\Seeders\LanguageSeeder;
use Database\Seeders\UserLevelSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PetitionFeaturedSlugSignedTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([UserLevelSeeder::class, LanguageSeeder::class]);
    }

    private function admin(): User
    {
        return User::factory()->create(['level_id' => UserLevel::where('name', 'admin')->value('id'), 'verified' => true]);
    }

    private function petition(array $locales = ['en']): Petition
    {
        $p = Petition::factory()->create(['status' => 'published', 'is_active' => true]);
        foreach ($locales as $locale => $featured) {
            [$locale, $featured] = is_int($locale) ? [$featured, false] : [$locale, $featured];
            PetitionTranslation::create(['petition_id' => $p->id, 'locale' => $locale, 'title' => 'T '.$locale, 'slug' => 't-'.$locale, 'description' => 'd', 'is_featured' => $featured]);
        }

        return $p;
    }

    public function test_saving_non_default_locale_keeps_default_locale_featured(): void
    {
        $p = $this->petition(['en' => true, 'fr' => false]);

        $this->actingAs($this->admin(), 'admin')->post('/admin/petitions/save', [
            'id' => $p->id, 'locale' => 'fr', 'status' => 'published', 'title' => 'T fr', 'slug' => 't-fr',
        ])->assertRedirect();

        $flags = DB::table('petition_translations')->where('petition_id', $p->id)->pluck('is_featured', 'locale');
        $this->assertSame(1, (int) $flags['en']);
        $this->assertSame(0, (int) $flags['fr']);
    }

    public function test_admin_list_reads_featured_from_translations(): void
    {
        $p = $this->petition(['en' => true]);
        DB::table('petitions')->where('id', $p->id)->update(['is_featured' => 0]);

        $this->actingAs($this->admin(), 'admin')->get(route('admin.petitions', ['featured' => '1']))
            ->assertOk()
            ->assertSee('data-action="unfeature"', false);
    }

    public function test_admin_save_slugifies_slug(): void
    {
        $p = $this->petition();

        $this->actingAs($this->admin(), 'admin')->post('/admin/petitions/save', [
            'id' => $p->id, 'locale' => 'en', 'status' => 'published', 'title' => 'T en', 'slug' => 'My Slug / Ünïcode!',
        ])->assertRedirect();

        $this->assertSame('my-slug-unicode', DB::table('petition_translations')->where('petition_id', $p->id)->where('locale', 'en')->value('slug'));
    }

    public function test_admin_save_keeps_unchanged_legacy_slug(): void
    {
        $p = $this->petition();
        DB::table('petition_translations')->where('petition_id', $p->id)->where('locale', 'en')->update(['slug' => 'Old_Slug']);

        $this->actingAs($this->admin(), 'admin')->post('/admin/petitions/save', [
            'id' => $p->id, 'locale' => 'en', 'status' => 'published', 'title' => 'T en', 'slug' => 'Old_Slug',
        ])->assertRedirect();

        $this->assertSame('Old_Slug', DB::table('petition_translations')->where('petition_id', $p->id)->where('locale', 'en')->value('slug'));
    }

    public function test_signed_by_user_id_counts_as_signed_on_show_and_sign_page(): void
    {
        $user = User::factory()->create(['verified' => true, 'email' => 'new@x.test']);
        $p = $this->petition();
        // Signed earlier under a different email.
        DB::table('signatures')->insert(['petition_id' => $p->id, 'user_id' => $user->id, 'name' => 'n', 'email' => 'old@x.test', 'locale' => 'en', 'confirmed' => 1, 'is_spam' => 0, 'created_at' => now(), 'updated_at' => now()]);
        // Unsigned petition: an ungrouped OR would match the user_id row above.
        $other = Petition::factory()->create(['status' => 'published', 'is_active' => true]);
        PetitionTranslation::create(['petition_id' => $other->id, 'locale' => 'en', 'title' => 'O', 'slug' => 'o', 'description' => 'd']);

        $this->actingAs($user)->get("/en/petition/t-en/{$p->id}")->assertOk()->assertViewHas('hasSigned', true);
        $this->actingAs($user)->get("/en/petition/t-en/{$p->id}/sign/confirm")->assertRedirect("/en/petition/t-en/{$p->id}");
        $this->actingAs($user)->get("/en/petition/o/{$other->id}")->assertOk()->assertViewHas('hasSigned', false);
    }

    public function test_cache_warm_uses_listing_query(): void
    {
        $p = $this->petition();

        $this->artisan('cache:warm', ['--locale' => 'en'])->assertSuccessful();

        $cached = cache()->get(CacheKeys::petitionsIndex('en', 1));
        $this->assertSame([$p->id], collect($cached['ids'])->pluck('id')->all());
    }
}
