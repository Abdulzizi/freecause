#!/usr/bin/env php
<?php

/**
 * Fix petition category mapping.
 * Old DB had 22 categories repeated per locale prefix.
 * Maps each old category's position within its prefix group → new category ID.
 */

// Position within prefix block → new category ID
$positionToNewId = [
    1  => 6,  // Nature and Environment     → Environment
    2  => 1,  // Animals                    → Animals
    3  => 2,  // Business and Companies     → Business and Companies
    4  => 4,  // Culture and Society        → Culture and Society
    5  => 9,  // International Policy       → Politics
    6  => 8,  // Justice, rights            → Human Rights
    7  => 4,  // Entertainment and Media    → Culture and Society
    8  => 14, // Work                       → Work and Employment
    9  => 4,  // Music                      → Culture and Society
    10 => 4,  // People and Organizations   → Culture and Society
    11 => 9,  // Politics and Government    → Politics
    12 => 4,  // Religion                   → Culture and Society
    13 => 7,  // Health                     → Health and Wellness
    14 => 5,  // School and Education       → Education
    15 => 11, // Sport                      → Sports
    16 => 10, // Technology and Internet    → Science and Technology
    17 => 10, // Telecommunications         → Science and Technology
    18 => 13, // Transport and infrastructure → Transportation
    19 => 3,  // City Life                  → City Life
    20 => 6,  // Sustainability             → Environment
    21 => 4,  // Food                       → Culture and Society
    22 => 8,  // LGTB                       → Human Rights
];

// Load .env for new DB
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

$new = new mysqli(
    $env['DB_HOST'] ?? '127.0.0.1',
    $env['DB_USERNAME'] ?? 'root',
    $env['DB_PASSWORD'] ?? '',
    $env['DB_DATABASE'] ?? 'freecause',
    (int)($env['DB_PORT'] ?? 3306)
);
if ($new->connect_error) die("NEW DB error: " . $new->connect_error . "\n");

echo "Building old category id → new category id map...\n";

// Get all old categories grouped by prefix, ordered by id (to get position)
$result = $old->query("SELECT id, prefix FROM petition_categories ORDER BY prefix, id");

$categoryMap = []; // old_id → new_id
$prefixGroups = [];

while ($row = $result->fetch_assoc()) {
    $prefixGroups[$row['prefix']][] = $row['id'];
}

foreach ($prefixGroups as $prefix => $ids) {
    foreach ($ids as $pos => $oldId) {
        $position = $pos + 1; // 1-based
        $newId = $positionToNewId[$position] ?? 1;
        $categoryMap[$oldId] = $newId;
    }
}

echo "  Mapped " . count($categoryMap) . " old categories.\n";

// Update petitions in new DB using old petition category_id
echo "Updating petition categories...\n";

$result = $old->query("SELECT id, category_id FROM petitions WHERE category_id IS NOT NULL");
$updated = 0;

$cases = [];
while ($row = $result->fetch_assoc()) {
    $petId   = (int)$row['id'];
    $newCatId = $categoryMap[(int)$row['category_id']] ?? 1;
    $cases[$newCatId][] = $petId;
}

foreach ($cases as $newCatId => $petIds) {
    $chunks = array_chunk($petIds, 1000);
    foreach ($chunks as $chunk) {
        $ids = implode(',', $chunk);
        $new->query("UPDATE petitions SET category_id = {$newCatId} WHERE id IN ({$ids})");
        $updated += count($chunk);
    }
    echo "  Set category {$newCatId} for " . count($petIds) . " petitions...\n";
}

echo "\nDone. Updated {$updated} petitions.\n";

$old->close();
$new->close();
