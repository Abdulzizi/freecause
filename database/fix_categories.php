#!/usr/bin/env php
<?php

$positionToNewId = [
    1 => 6,
    2 => 1,
    3 => 2,
    4 => 4,
    5 => 9,
    6 => 8,
    7 => 4,
    8 => 14,
    9 => 4,
    10 => 4,
    11 => 9,
    12 => 4,
    13 => 7,
    14 => 5,
    15 => 11,
    16 => 10,
    17 => 10,
    18 => 13,
    19 => 3,
    20 => 6,
    21 => 4,
    22 => 8,
];

$envFile = __DIR__.'/../.env';
$env = [];
foreach (file($envFile) as $line) {
    $line = trim($line);
    if (! $line || str_starts_with($line, '#')) {
        continue;
    }
    [$k, $v] = array_pad(explode('=', $line, 2), 2, '');
    $env[trim($k)] = trim($v, '"\'');
}

$old = new mysqli('127.0.0.1', 'freecause', 'secret', 'freecause_old', 3306);
if ($old->connect_error) {
    exit('OLD DB error: '.$old->connect_error."\n");
}

$new = new mysqli(
    $env['DB_HOST'] ?? '127.0.0.1',
    $env['DB_USERNAME'] ?? 'root',
    $env['DB_PASSWORD'] ?? '',
    $env['DB_DATABASE'] ?? 'freecause',
    (int) ($env['DB_PORT'] ?? 3306)
);
if ($new->connect_error) {
    exit('NEW DB error: '.$new->connect_error."\n");
}

echo "Building old category id → new category id map...\n";

$result = $old->query('SELECT id, prefix FROM petition_categories ORDER BY prefix, id');

$categoryMap = [];
$prefixGroups = [];

while ($row = $result->fetch_assoc()) {
    $prefixGroups[$row['prefix']][] = $row['id'];
}

foreach ($prefixGroups as $prefix => $ids) {
    foreach ($ids as $pos => $oldId) {
        $position = $pos + 1;
        $newId = $positionToNewId[$position] ?? 1;
        $categoryMap[$oldId] = $newId;
    }
}

echo '  Mapped '.count($categoryMap)." old categories.\n";

echo "Updating petition categories...\n";

$result = $old->query('SELECT id, category_id FROM petitions WHERE category_id IS NOT NULL');
$updated = 0;

$cases = [];
while ($row = $result->fetch_assoc()) {
    $petId = (int) $row['id'];
    $newCatId = $categoryMap[(int) $row['category_id']] ?? 1;
    $cases[$newCatId][] = $petId;
}

foreach ($cases as $newCatId => $petIds) {
    $chunks = array_chunk($petIds, 1000);
    foreach ($chunks as $chunk) {
        $ids = implode(',', $chunk);
        $new->query("UPDATE petitions SET category_id = {$newCatId} WHERE id IN ({$ids})");
        $updated += count($chunk);
    }
    echo "  Set category {$newCatId} for ".count($petIds)." petitions...\n";
}

echo "\nDone. Updated {$updated} petitions.\n";

$old->close();
$new->close();
