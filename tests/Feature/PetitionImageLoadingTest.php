<?php

namespace Tests\Feature;

use App\Models\Petition;
use Database\Seeders\LanguageSeeder;
use Database\Seeders\UserLevelSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PetitionImageLoadingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([LanguageSeeder::class, UserLevelSeeder::class]);
        Storage::fake('public');
    }

    public function test_responsive_cover_preload_matches_visible_cover_source(): void
    {
        foreach (['cover.jpg', 'cover_400w.webp', 'cover_800w.webp'] as $file) {
            Storage::disk('public')->put('petitions/'.$file, 'image');
        }
        $petition = $this->petition(['cover_image' => 'petitions/cover.jpg']);
        $html = $this->get("/en/petition/image-loading-{$petition->id}/{$petition->id}")->assertOk()->getContent();
        $document = new \DOMDocument;
        @$document->loadHTML($html);
        $xpath = new \DOMXPath($document);
        $preload = $xpath->query('//link[@rel="preload" and @as="image"]')->item(0);
        $hero = $xpath->query('//img[@fetchpriority="high"]')->item(0);
        $source = $xpath->query('//source[@sizes="(min-width: 992px) 640px, 100vw"]')->item(0);

        $this->assertSame($source->getAttribute('srcset'), $preload->getAttribute('imagesrcset'));
        $this->assertSame($source->getAttribute('sizes'), $preload->getAttribute('imagesizes'));
        $this->assertSame('image/webp', $preload->getAttribute('type'));
        $this->assertSame('eager', $hero->getAttribute('loading'));
        $this->assertSame('async', $hero->getAttribute('decoding'));
    }

    public function test_missing_cover_including_stale_path_does_not_preload_fallback(): void
    {
        foreach ([null, 'petitions/missing.jpg'] as $cover) {
            $petition = $this->petition(['cover_image' => $cover]);
            $this->get("/en/petition/image-loading-{$petition->id}/{$petition->id}")
                ->assertOk()->assertDontSee('<link rel="preload" as="image"', false);
        }
    }

    public function test_external_cover_preloads_original_and_list_does_not(): void
    {
        $petition = $this->petition(['cover_image' => 'https://images.example.com/cover.jpg']);
        $this->get("/en/petition/image-loading-{$petition->id}/{$petition->id}")
            ->assertOk()->assertSee('<link rel="preload" as="image" href="https://images.example.com/cover.jpg" fetchpriority="high">', false);
        $this->get('/en/petitions')->assertOk()->assertDontSee('<link rel="preload" as="image"', false);
    }

    private function petition(array $attributes): Petition
    {
        $petition = Petition::factory()->create(array_merge([
            'status' => 'published', 'is_active' => true, 'image_url' => null,
        ], $attributes));
        $petition->translations()->create([
            'locale' => 'en', 'title' => 'Image Loading', 'slug' => 'image-loading-'.$petition->id, 'description' => 'Description',
        ]);

        return $petition;
    }
}
