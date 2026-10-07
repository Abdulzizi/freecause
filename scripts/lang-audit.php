<?php

/**
 * Lang parity audit.
 *
 * Exits 0 when every locale directory has (a) the same lang files as `en`,
 * (b) at least the same flattened keys inside each shared file, and (c) the
 * same `:placeholders` in every translated value as its English source.
 * Exits 1 and prints the gaps otherwise.
 *
 * Usage: php scripts/lang-audit.php
 */
$root = dirname(__DIR__).'/lang';

$locales = array_values(array_filter(glob($root.'/*', GLOB_ONLYDIR), function ($dir) {
    return basename($dir) !== 'vendor';
}));

$flatten = function ($arr, $prefix = '') use (&$flatten) {
    $out = [];

    foreach ($arr as $key => $value) {
        $flat = $prefix ? $prefix.'.'.$key : $key;
        if (is_array($value)) {
            $out += $flatten($value, $flat);
        } else {
            $out[$flat] = $value;
        }
    }

    return $out;
};

$placeholders = function ($value) {
    preg_match_all('/:[a-zA-Z_][a-zA-Z0-9_]*/', (string) $value, $matches);
    $found = array_values(array_unique($matches[0]));
    sort($found);

    return $found;
};

$enFiles = array_values(array_filter(glob($root.'/en/*.php'), 'is_file'));
$enFileNames = array_map('basename', $enFiles);
sort($enFileNames);

$failures = 0;

foreach ($locales as $dir) {
    $locale = basename($dir);

    if ($locale === 'en') {
        continue;
    }

    $files = array_values(array_filter(glob($dir.'/*.php'), 'is_file'));
    $fileMap = [];

    foreach ($files as $file) {
        $fileMap[basename($file)] = $file;
    }

    foreach ($enFileNames as $name) {
        if (! isset($fileMap[$name])) {
            echo "[$locale] missing file: $name\n";
            $failures++;

            continue;
        }

        $enValues = $flatten(require $root.'/en/'.$name);
        $locValues = $flatten(require $fileMap[$name]);
        $missing = array_values(array_diff(array_keys($enValues), array_keys($locValues)));

        if ($missing !== []) {
            echo "[$locale] $name missing keys: ".implode(', ', $missing)."\n";
            $failures += count($missing);
        }

        foreach ($enValues as $key => $enValue) {
            if (! array_key_exists($key, $locValues)) {
                continue;
            }

            $expected = $placeholders($enValue);
            if ($expected === []) {
                continue;
            }

            $actual = $placeholders($locValues[$key]);

            if ($expected !== $actual) {
                echo "[$locale] $name::$key placeholder mismatch (expected ".implode(', ', $expected).'; got '.implode(', ', $actual).")\n";
                $failures++;
            }
        }
    }
}

if ($failures > 0) {
    fwrite(STDERR, "Lang parity FAILED with $failures gap(s).\n");
    exit(1);
}

echo "Lang parity OK: every locale matches en files, keys and placeholders.\n";
