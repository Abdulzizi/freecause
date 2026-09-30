<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

class ImageVariants
{
    /** Variant widths keyed by the suffix label appended to the basename. */
    private const VARIANTS = ['w800' => 800, 'w400' => 400];

    /**
     * URLs for srcset if both WebP variants exist on disk, else null.
     *
     * @param  string|null  $relativePath  DB value, e.g. "petitions/abc.jpg" (no "storage/" prefix, no leading slash).
     * @return array<string, string>|null ['800' => url, '400' => url]
     */
    public static function urls(?string $relativePath): ?array
    {
        if ($relativePath === null || $relativePath === '') {
            return null;
        }

        if (str_starts_with($relativePath, 'http://') || str_starts_with($relativePath, 'https://')) {
            return null;
        }

        if (self::isVariantStem(self::stem($relativePath))) {
            return null;
        }

        $variants = self::relativeVariantPaths($relativePath);

        $disk = Storage::disk('public');

        if (! $disk->exists($variants['w800']) || ! $disk->exists($variants['w400'])) {
            return null;
        }

        return [
            '800' => asset('storage/'.$variants['w800']),
            '400' => asset('storage/'.$variants['w400']),
        ];
    }

    /**
     * Create missing WebP variants next to the original file.
     *
     * Idempotent: existing variant files are never rewritten. Never throws.
     *
     * @return array<string, string>|null ['w800' => abs path, 'w400' => abs path]
     */
    public static function ensure(string $absPath): ?array
    {
        try {
            if (! is_file($absPath)) {
                return null;
            }

            if (self::isVariantStem(self::stem($absPath))) {
                return null;
            }

            $variants = self::absoluteVariantPaths($absPath);

            foreach (self::VARIANTS as $label => $width) {
                $dest = $variants[$label];

                if (is_file($dest)) {
                    continue;
                }

                Image::read($absPath)
                    ->orient()
                    ->scaleDown(width: $width, height: $width)
                    ->toWebp(quality: 82)
                    ->save($dest);
            }

            if (! is_file($variants['w800']) || ! is_file($variants['w400'])) {
                return null;
            }

            return $variants;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Delete both WebP variants for the given relative path (best-effort, never throws).
     */
    public static function purge(?string $relativePath): void
    {
        if ($relativePath === null || $relativePath === '') {
            return;
        }

        if (str_starts_with($relativePath, 'http://') || str_starts_with($relativePath, 'https://')) {
            return;
        }

        try {
            foreach (self::relativeVariantPaths($relativePath) as $variant) {
                Storage::disk('public')->delete($variant);
            }
        } catch (\Throwable $e) {
            // best-effort: deletion failures must not propagate
        }
    }

    /**
     * Basename without its extension; extensionless names are returned whole.
     */
    private static function stem(string $path): string
    {
        $base = basename($path);
        $extension = strtolower(pathinfo($base, PATHINFO_EXTENSION));

        return $extension === '' ? $base : substr($base, 0, -(strlen($extension) + 1));
    }

    private static function isVariantStem(string $stem): bool
    {
        return str_ends_with($stem, '_800w') || str_ends_with($stem, '_400w');
    }

    /**
     * @return array<string, string>
     */
    private static function absoluteVariantPaths(string $absPath): array
    {
        $dir = dirname($absPath);
        $stem = self::stem($absPath);

        return [
            'w800' => $dir.DIRECTORY_SEPARATOR.$stem.'_800w.webp',
            'w400' => $dir.DIRECTORY_SEPARATOR.$stem.'_400w.webp',
        ];
    }

    /**
     * @return array<string, string>
     */
    private static function relativeVariantPaths(string $relativePath): array
    {
        $relativePath = ltrim($relativePath, '/');
        $dir = dirname($relativePath);
        $dir = ($dir === '.' || $dir === '') ? '' : rtrim($dir, '/').'/';
        $stem = self::stem($relativePath);

        return [
            'w800' => $dir.$stem.'_800w.webp',
            'w400' => $dir.$stem.'_400w.webp',
        ];
    }
}
