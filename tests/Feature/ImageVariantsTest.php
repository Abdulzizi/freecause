<?php

namespace Tests\Feature;

use App\Support\ImageVariants;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImageVariantsTest extends TestCase
{
    public function test_ensure_creates_both_webp_variants_from_a_jpeg(): void
    {
        Storage::fake('public');

        $abs = $this->fixtureJpeg('petitions/fixture.jpg', 1200, 900);

        $variants = ImageVariants::ensure($abs);

        $this->assertNotNull($variants);
        $this->assertSame(dirname($abs).DIRECTORY_SEPARATOR.'fixture_800w.webp', $variants['w800']);
        $this->assertSame(dirname($abs).DIRECTORY_SEPARATOR.'fixture_400w.webp', $variants['w400']);

        Storage::disk('public')->assertExists('petitions/fixture_800w.webp');
        Storage::disk('public')->assertExists('petitions/fixture_400w.webp');

        $this->assertSame(IMAGETYPE_WEBP, getimagesize($variants['w800'])[2]);
        $this->assertSame(IMAGETYPE_WEBP, getimagesize($variants['w400'])[2]);
    }

    public function test_ensure_is_idempotent_and_does_not_rewrite_existing_variants(): void
    {
        Storage::fake('public');

        $abs = $this->fixtureJpeg('petitions/fixture.jpg', 800, 600);

        $first = ImageVariants::ensure($abs);
        $mtime800 = filemtime($first['w800']);
        $mtime400 = filemtime($first['w400']);

        usleep(50_000);

        $second = ImageVariants::ensure($abs);

        $this->assertNotNull($second);
        $this->assertSame($first, $second);
        $this->assertSame($mtime800, filemtime($second['w800']));
        $this->assertSame($mtime400, filemtime($second['w400']));
    }

    public function test_ensure_returns_null_for_missing_file(): void
    {
        $this->assertNull(ImageVariants::ensure('/nonexistent/path/does-not-exist.jpg'));
    }

    public function test_ensure_returns_null_for_garbage_file_without_throwing(): void
    {
        Storage::fake('public');

        $abs = Storage::disk('public')->path('petitions/garbage.jpg');
        if (! is_dir(dirname($abs))) {
            mkdir(dirname($abs), 0777, true);
        }
        file_put_contents($abs, 'this is not an image');

        $this->assertNull(ImageVariants::ensure($abs));
        Storage::disk('public')->assertMissing('petitions/garbage_800w.webp');
        Storage::disk('public')->assertMissing('petitions/garbage_400w.webp');
    }

    public function test_ensure_does_not_convert_an_existing_variant(): void
    {
        Storage::fake('public');

        // A filename whose stem already ends in "_800w" must never be converted.
        $stemmed = $this->fixtureJpeg('petitions/fixture_800w.jpg', 800, 600);

        $this->assertNull(ImageVariants::ensure($stemmed));
        Storage::disk('public')->assertMissing('petitions/fixture_800w_800w.webp');
        Storage::disk('public')->assertMissing('petitions/fixture_800w_400w.webp');
    }

    public function test_urls_returns_null_when_variants_are_absent(): void
    {
        Storage::fake('public');

        $this->assertNull(ImageVariants::urls('petitions/missing.jpg'));
        $this->assertNull(ImageVariants::urls(null));
        $this->assertNull(ImageVariants::urls(''));
    }

    public function test_urls_returns_asset_urls_after_ensure(): void
    {
        Storage::fake('public');

        $abs = $this->fixtureJpeg('petitions/fixture.jpg', 800, 600);
        ImageVariants::ensure($abs);

        $urls = ImageVariants::urls('petitions/fixture.jpg');

        $this->assertNotNull($urls);
        $this->assertSame([800, 400], array_keys($urls));
        $this->assertStringContainsString('storage/petitions/fixture_800w.webp', $urls['800']);
        $this->assertStringContainsString('storage/petitions/fixture_400w.webp', $urls['400']);
    }

    public function test_urls_returns_null_for_remote_urls(): void
    {
        $this->assertNull(ImageVariants::urls('https://images.example.com/petitions/abc.jpg'));
        $this->assertNull(ImageVariants::urls('http://images.example.com/petitions/abc.jpg'));
    }

    public function test_purge_removes_both_variants_and_is_safe_when_absent(): void
    {
        Storage::fake('public');

        $abs = $this->fixtureJpeg('petitions/fixture.jpg', 800, 600);
        ImageVariants::ensure($abs);

        Storage::disk('public')->assertExists('petitions/fixture_800w.webp');
        Storage::disk('public')->assertExists('petitions/fixture_400w.webp');

        ImageVariants::purge('petitions/fixture.jpg');

        Storage::disk('public')->assertMissing('petitions/fixture_800w.webp');
        Storage::disk('public')->assertMissing('petitions/fixture_400w.webp');

        // No-op when variants are absent (must not throw).
        ImageVariants::purge('petitions/fixture.jpg');
        ImageVariants::purge(null);
        ImageVariants::purge('https://images.example.com/petitions/abc.jpg');
    }

    private function fixtureJpeg(string $relativePath, int $width, int $height): string
    {
        $abs = Storage::disk('public')->path($relativePath);
        $dir = dirname($abs);

        if (! is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $image = imagecreatetruecolor($width, $height);
        imagefill($image, 0, 0, imagecolorallocate($image, 180, 40, 40));
        imagejpeg($image, $abs, 90);
        imagedestroy($image);

        return $abs;
    }
}
