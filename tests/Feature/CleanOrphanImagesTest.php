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
}
