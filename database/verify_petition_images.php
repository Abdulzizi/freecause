#!/usr/bin/env php
<?php
/**
 * FreeCause — Petition Image Verification
 *
 * Reports how many petitions have their image file present vs missing,
 * broken down by locale folder.
 *
 * Usage:
 *   php database/verify_petition_images.php
 *
 * Run from the Laravel project root.
 */

// Load .env
$envFile = __DIR__ . '/../.env';
$env = [];
foreach (file($envFile) as $line) {
    $line = trim($line);
    if (!$line || str_starts_with($line, '#')) continue;
    [$k, $v] = array_pad(explode('=', $line, 2), 2, '');
    $env[trim($k)] = trim($v, '"\'');
}

$db = new mysqli(
    $env['DB_HOST']     ?? '127.0.0.1',
    $env['DB_USERNAME'] ?? 'root',
    $env['DB_PASSWORD'] ?? '',
    $env['DB_DATABASE'] ?? 'freecause',
    (int)($env['DB_PORT'] ?? 3306)
);
if ($db->connect_error) die("DB connection failed: " . $db->connect_error . "\n");
$db->set_charset('utf8mb4');

$storageBase = __DIR__ . '/../storage/app/public/petitions/';

$result = $db->query("SELECT id, image_url FROM petitions WHERE image_url IS NOT NULL AND image_url != ''");

$total       = 0;
$present     = 0;
$missing     = 0;
$byLocale    = [];
$missingList = [];

while ($row = $result->fetch_assoc()) {
    $total++;
    $fullPath = $storageBase . $row['image_url'];
    $locale   = explode('/', $row['image_url'])[0] ?? 'unknown';

    if (file_exists($fullPath)) {
        $present++;
        $byLocale[$locale]['present'] = ($byLocale[$locale]['present'] ?? 0) + 1;
    } else {
        $missing++;
        $byLocale[$locale]['missing'] = ($byLocale[$locale]['missing'] ?? 0) + 1;
        if (count($missingList) < 10) {
            $missingList[] = $row['image_url'];
        }
    }
}

echo "\n=== Petition Image Coverage ===\n\n";
printf("  %-10s %8s %8s %8s\n", 'Locale', 'Present', 'Missing', 'Total');
echo "  " . str_repeat('-', 40) . "\n";

ksort($byLocale);
foreach ($byLocale as $locale => $counts) {
    $p = $counts['present'] ?? 0;
    $m = $counts['missing'] ?? 0;
    printf("  %-10s %8d %8d %8d\n", $locale, $p, $m, $p + $m);
}

echo "  " . str_repeat('-', 40) . "\n";
printf("  %-10s %8d %8d %8d\n", 'TOTAL', $present, $missing, $total);

$pct = $total > 0 ? round(100 * $present / $total, 1) : 0;
echo "\n  Coverage: {$pct}% ({$present} of {$total} petitions have their image file)\n";

if ($missing > 0 && count($missingList)) {
    echo "\n  First missing files (up to 10):\n";
    foreach ($missingList as $path) {
        echo "    {$path}\n";
    }
    echo "\n  To fetch missing files, run:\n";
    echo "    bash database/fetch_petition_images.sh\n";
}

echo "\n";
$db->close();
