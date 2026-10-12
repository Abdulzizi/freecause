#!/usr/bin/env php
<?php

$dryRun = in_array('--dry-run', $argv ?? []);
if ($dryRun) {
    echo "[DRY RUN] No changes will be made.\n\n";
}

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

$new = new mysqli(
    $env['DB_HOST'] ?? '127.0.0.1',
    $env['DB_USERNAME'] ?? 'root',
    $env['DB_PASSWORD'] ?? '',
    $env['DB_DATABASE'] ?? 'freecause',
    (int) ($env['DB_PORT'] ?? 3306)
);
if ($new->connect_error) {
    exit('New DB connection failed: '.$new->connect_error."\n");
}
$new->set_charset('utf8mb4');

$old = new mysqli('127.0.0.1', 'freecause', 'secret', 'freecause_old', 3306);
if ($old->connect_error) {
    exit('Old DB connection failed: '.$old->connect_error."\n");
}
$old->set_charset('utf8mb4');

$result = $old->query("
    SELECT id, pic
    FROM petitions
    WHERE pic LIKE 'http%'
      AND pic REGEXP '[.](jpg|jpeg|png|gif|webp)'
");

$restored = 0;
$skipped = 0;

while ($row = $result->fetch_assoc()) {
    $id = (int) $row['id'];
    $url = $row['pic'];

    $check = $new->query("SELECT id FROM petitions WHERE id = {$id} AND (cover_image IS NULL OR cover_image = '') LIMIT 1");
    if (! $check || $check->num_rows === 0) {
        $skipped++;

        continue;
    }

    if (! $dryRun) {
        $escaped = $new->real_escape_string($url);
        $new->query("UPDATE petitions SET cover_image = '{$escaped}' WHERE id = {$id}");
    }

    $restored++;

    if ($restored % 100 === 0) {
        echo "  Restored {$restored}...\r";
    }
}

echo "\nDone. Restored {$restored} external image URLs to cover_image";
if ($skipped > 0) {
    echo " ({$skipped} skipped — petition not in new DB or already has cover_image)";
}
echo ".\n\n";

$old->close();
$new->close();
