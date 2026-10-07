<?php

/**
 * Lang parity audit.
 *
 * Exits 0 when every locale directory has (a) the same lang files as `en`
 * and (b) at least the same flattened keys inside each shared file.
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
            $out = array_merge($out, $flatten($value, $flat));
        } else {
            $out[] = $flat;
        }
    }

    return $out;
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

        $enKeys = $flatten(require $root.'/en/'.$name);
        $locKeys = $flatten(require $fileMap[$name]);
        $missing = array_values(array_diff($enKeys, $locKeys));

        if ($missing !== []) {
            echo "[$locale] $name missing keys: ".implode(', ', $missing)."\n";
            $failures += count($missing);
        }
    }
}

if ($failures > 0) {
    fwrite(STDERR, "Lang parity FAILED with $failures gap(s).\n");
    exit(1);
}

echo "Lang parity OK: every locale matches en files and keys.\n";