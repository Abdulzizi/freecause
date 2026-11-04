<?php

namespace Tests\Feature;

use App\Models\Petition;
use Database\Seeders\LanguageSeeder;
use Database\Seeders\UserLevelSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CleanOrphanImagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_keeps_referenced_images_variants_and_fresh_uploads(): void
    {
        $this->seed([UserLevelSeeder::class, LanguageSeeder::class]);
        $disk = Storage::fake('public');
        Petition::factory()->create(['cover_image' => 'petitions/kept.jpg']);

        $old = now()->subHours(2)->getTimestamp();
        foreach (['kept.jpg', 'kept_800w.webp', 'kept_400w.webp', 'gone.jpg', 'gone_400w.webp'] as $name) {
            $disk->put("petitions/{$name}", 'x');
            touch($disk->path("petitions/{$name}"), $old);
        }
        $disk->put('petitions/fresh.jpg', 'x');

        $this->artisan('images:cleanup')->assertExitCode(0);

        $disk->assertExists(['petitions/kept.jpg', 'petitions/kept_800w.webp', 'petitions/kept_400w.webp', 'petitions/fresh.jpg']);
        $disk->assertMissing(['petitions/gone.jpg', 'petitions/gone_400w.webp']);
    }

    public function test_keeps_legacy_bare_filename_image_url_and_its_variants(): void
    {
        $this->seed([UserLevelSeeder::class, LanguageSeeder::class]);
        $disk = Storage::fake('public');
        Petition::factory()->create(['cover_image' => null, 'image_url' => 'legacy.jpg']);
        Petition::factory()->create(['cover_image' => null, 'image_url' => 'http-banner.jpg']);
        Petition::factory()->create(['cover_image' => null, 'image_url' => '']);
        Petition::factory()->create(['cover_image' => null, 'image_url' => 'https://x/y.jpg']);
        Petition::factory()->create(['cover_image' => 'petitions/both.jpg', 'image_url' => 'other.jpg']);

        $old = now()->subHours(2)->getTimestamp();
        foreach (['legacy.jpg', 'legacy_800w.webp', 'legacy_400w.webp', 'http-banner.jpg', 'both.jpg', 'other.jpg', 'gone.jpg'] as $name) {
            $disk->put("petitions/{$name}", 'x');
            touch($disk->path("petitions/{$name}"), $old);
        }

        $this->artisan('images:cleanup')->assertExitCode(0);

        $disk->assertExists([
            'petitions/legacy.jpg', 'petitions/legacy_800w.webp', 'petitions/legacy_400w.webp',
            'petitions/http-banner.jpg', 'petitions/both.jpg', 'petitions/other.jpg',
        ]);
        $disk->assertMissing('petitions/gone.jpg');
    }
}
