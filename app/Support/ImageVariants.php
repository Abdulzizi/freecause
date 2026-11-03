<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

class ImageVariants
{
    private const VARIANTS = ['w800' => 800, 'w400' => 400];

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

        // Older uploads up to 800px wide have only the 400w variant; every srcset needs both, so fall back to the original then.
        if (! $disk->exists($variants['w400']) || ! $disk->exists($variants['w800'])) {
            return null;
        }

        return [
            '400' => asset('storage/'.$variants['w400']),
            '800' => asset('storage/'.$variants['w800']),
        ];
    }

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
            $widths = self::VARIANTS;

            foreach ($widths as $label => $width) {
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

            foreach (array_keys($widths) as $label) {
                if (! is_file($variants[$label])) {
                    report(new \RuntimeException('WebP variants incomplete after conversion'));

                    return null;
                }
            }

            return $variants;
        } catch (\Throwable $e) {
            report($e);

            return null;
        }
    }

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
        }
    }

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

    private static function absoluteVariantPaths(string $absPath): array
    {
        $dir = dirname($absPath);
        $stem = self::stem($absPath);

        return [
            'w800' => $dir.DIRECTORY_SEPARATOR.$stem.'_800w.webp',
            'w400' => $dir.DIRECTORY_SEPARATOR.$stem.'_400w.webp',
        ];
    }

    public static function relativeVariantPaths(string $relativePath): array
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
