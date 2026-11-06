<?php

namespace Tests\Unit;

use App\Models\Petition;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PetitionHasCoverTest extends TestCase
{
    use RefreshDatabase;

    public function test_uploaded_cover_counts(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('covers/a.jpg', 'x');

        $this->assertTrue((new Petition(['cover_image' => 'covers/a.jpg']))->hasCover());
    }

    public function test_external_url_counts(): void
    {
        $this->assertTrue((new Petition(['image_url' => 'https://example.com/a.jpg']))->hasCover());
    }

    public function test_missing_file_does_not_count(): void
    {
        Storage::fake('public');

        $this->assertFalse((new Petition(['cover_image' => 'covers/gone.jpg', 'image_url' => 'gone.jpg']))->hasCover());
        $this->assertFalse((new Petition)->hasCover());
    }
}
