<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Petition;
use App\Models\PetitionTranslation;
use App\Models\Signature;
use App\Models\User;
use Database\Seeders\LanguageSeeder;
use Database\Seeders\UserLevelSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PetitionSignatureExportTest extends TestCase
{
    use RefreshDatabase;

    protected User $owner;

    protected Petition $petition;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([UserLevelSeeder::class, LanguageSeeder::class]);

        $this->owner = User::factory()->create(['verified' => true]);

        $category = Category::factory()->create(['is_active' => true]);

        $this->petition = Petition::factory()->create([
            'user_id' => $this->owner->id,
            'status' => 'published',
            'is_active' => true,
            'category_id' => $category->id,
        ]);

        PetitionTranslation::create([
            'petition_id' => $this->petition->id,
            'locale' => 'en',
            'title' => 'Protect The Reef',
            'slug' => 'protect-the-reef',
            'description' => 'Save it.',
        ]);
    }

    private function url(string $format): string
    {
        return "/en/petition/protect-the-reef/{$this->petition->id}/download/{$format}";
    }

    public function test_owner_can_download_csv_of_signatures(): void
    {
        Signature::factory()->count(3)->create([
            'petition_id' => $this->petition->id,
            'confirmed' => true,
        ]);

        $response = $this->actingAs($this->owner)->get($this->url('csv'));

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');

        $body = $response->streamedContent();
        // Owners export signatures without signer emails (privacy); admins keep them.
        $this->assertStringContainsString('#,name,comment,locale,confirmed,signed_at', $body);
        $this->assertStringNotContainsString('name,email,comment', $body);
    }

    public function test_csv_export_is_not_capped_at_100_rows(): void
    {
        // Legacy exported every signature; the rewrite previously capped at 100.
        Signature::factory()->count(105)->create([
            'petition_id' => $this->petition->id,
            'confirmed' => true,
        ]);

        $body = $this->actingAs($this->owner)->get($this->url('csv'))->streamedContent();

        // Header + 105 data rows; row 105 must be present.
        $lines = array_values(array_filter(explode("\n", trim($body)), fn ($l) => $l !== ''));
        $this->assertCount(106, $lines);
        $this->assertStringStartsWith('105,', $lines[105]);
    }

    public function test_non_owner_cannot_download_signatures(): void
    {
        $stranger = User::factory()->create(['verified' => true]);

        $this->actingAs($stranger)->get($this->url('csv'))->assertForbidden();
        $this->actingAs($stranger)->get($this->url('txt'))->assertForbidden();
    }

    public function test_guest_cannot_download_signatures(): void
    {
        // Export routes are behind auth; a guest is redirected to login, not 200.
        $this->get($this->url('csv'))->assertRedirect();
    }
}
