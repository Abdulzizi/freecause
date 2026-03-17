#!/usr/bin/env php
<?php

/**
 * Import missing category translations from old DB into new DB.
 * Uses one representative prefix per locale to get translated category names.
 */

// Position within prefix block → new category ID (same as fix_categories.php)
$positionToNewId = [
    1  => 6,  2  => 1,  3  => 2,  4  => 4,  5  => 9,
    6  => 8,  7  => 4,  8  => 14, 9  => 4,  10 => 4,
    11 => 9,  12 => 4,  13 => 7,  14 => 5,  15 => 11,
    16 => 10, 17 => 10, 18 => 13, 19 => 3,  20 => 6,
    21 => 4,  22 => 8,
];

// One representative prefix per missing locale
$localePrefix = [
    'pl' => 'pl_',
    'pt' => 'pt_',
    'nl' => 'nl_',
    'ru' => 'ru_',
    'el' => 'gr_',
    'ro' => 'ro_',
    'sv' => 'se_',
    'tr' => 'tr_',
    'da' => 'dk_',
];

function slugify(string $text): string {
    $text = mb_strtolower($text, 'UTF-8');
    $text = preg_replace('/[^\w\s-]/u', '', $text);
    $text = preg_replace('/[\s_-]+/', '-', $text);
    return trim($text, '-') ?: 'category';
}

// Load .env
$envFile = __DIR__ . '/../.env';
$env = [];
foreach (file($envFile) as $line) {
    $line = trim($line);
    if (!$line || str_starts_with($line, '#')) continue;
    [$k, $v] = array_pad(explode('=', $line, 2), 2, '');
    $env[trim($k)] = trim($v, '"\'');
}

$old = new mysqli('127.0.0.1', 'freecause', 'secret', 'freecause_old', 3306);
if ($old->connect_error) die("OLD DB error: " . $old->connect_error . "\n");
$old->set_charset('utf8mb4');

$new = new mysqli(
    $env['DB_HOST'] ?? '127.0.0.1',
    $env['DB_USERNAME'] ?? 'root',
    $env['DB_PASSWORD'] ?? '',
    $env['DB_DATABASE'] ?? 'freecause',
    (int)($env['DB_PORT'] ?? 3306)
);
if ($new->connect_error) die("NEW DB error: " . $new->connect_error . "\n");
$new->set_charset('utf8mb4');

$now = date('Y-m-d H:i:s');
$inserted = 0;

foreach ($localePrefix as $locale => $prefix) {
    echo "Processing locale '{$locale}' from prefix '{$prefix}'...\n";

    $result = $old->query("SELECT id, name FROM petition_categories WHERE prefix='{$prefix}' ORDER BY id");
    $categories = [];
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }

    if (empty($categories)) {
        echo "  No categories found for prefix '{$prefix}', skipping.\n";
        continue;
    }

    foreach ($categories as $pos => $cat) {
        $position  = $pos + 1;
        $newCatId  = $positionToNewId[$position] ?? null;
        if (!$newCatId) continue;

        // Skip if translation already exists
        $check = $new->query("SELECT id FROM category_translations WHERE category_id={$newCatId} AND locale='{$locale}' LIMIT 1");
        if ($check->num_rows > 0) continue;

        $name = $new->real_escape_string($cat['name']);
        $slug = $new->real_escape_string(slugify($cat['name']) . '-' . $locale);

        $new->query("INSERT INTO category_translations (category_id, locale, name, slug, created_at, updated_at)
                     VALUES ({$newCatId}, '{$locale}', '{$name}', '{$slug}', '{$now}', '{$now}')");
        $inserted++;
    }

    echo "  Done.\n";
}

echo "\nTotal inserted: {$inserted} category translations.\n";

$old->close();
$new->close();
