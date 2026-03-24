#!/usr/bin/env php
<?php
/**
 * FreeCause — Fix Corrupt image_url Entries
 *
 * The old `pic` column sometimes stored external HTTP URLs rather than local
 * filenames. update_image_urls.php blindly prefixed these with the locale
 * folder, producing invalid paths like `es_AR/http://profile.ak.fbcdn.net/...`.
 *
 * This script finds all such entries and sets image_url = NULL so that
 * Petition::coverUrl() falls back to the placeholder image instead of
 * trying to resolve a dead external URL as a local file path.
 *
 * Usage:
 *   php database/fix_corrupt_image_urls.php
 *   php database/fix_corrupt_image_urls.php --dry-run
 *
 * Run from the Laravel project root.
 */

$dryRun = in_array('--dry-run', $argv ?? []);
if ($dryRun) {
    echo "[DRY RUN] No changes will be made.\n\n";
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

$db = new mysqli(
    $env['DB_HOST']     ?? '127.0.0.1',
    $env['DB_USERNAME'] ?? 'root',
    $env['DB_PASSWORD'] ?? '',
    $env['DB_DATABASE'] ?? 'freecause',
    (int)($env['DB_PORT'] ?? 3306)
);
if ($db->connect_error) die("DB connection failed: " . $db->connect_error . "\n");
$db->set_charset('utf8mb4');

// Find all image_url values where the part after "locale/" is an external URL.
// Pattern: anything that contains "/http://" or "/https://"
$result = $db->query("
    SELECT id, image_url
    FROM petitions
    WHERE image_url LIKE '%/http://%'
       OR image_url LIKE '%/https://%'
");

$byLocale = [];
$ids      = [];

while ($row = $result->fetch_assoc()) {
    $locale = explode('/', $row['image_url'])[0] ?? 'unknown';
    $byLocale[$locale] = ($byLocale[$locale] ?? 0) + 1;
    $ids[] = (int)$row['id'];
}

$total = count($ids);

if ($total === 0) {
    echo "No corrupt image_url entries found. Nothing to do.\n";
    $db->close();
    exit(0);
}

echo "=== Corrupt image_url Entries ===\n\n";
ksort($byLocale);
foreach ($byLocale as $locale => $count) {
    printf("  %-10s %6d\n", $locale, $count);
}
echo "  " . str_repeat('-', 18) . "\n";
printf("  %-10s %6d\n\n", 'TOTAL', $total);

if (!$dryRun) {
    // Batch update in chunks to avoid giant IN() queries
    $chunks = array_chunk($ids, 500);
    $fixed  = 0;
    foreach ($chunks as $chunk) {
        $inList = implode(',', $chunk);
        $db->query("UPDATE petitions SET image_url = NULL WHERE id IN ({$inList})");
        $fixed += $db->affected_rows;
    }
    echo "Fixed {$fixed} entries (image_url set to NULL).\n";
    echo "Run 'php database/verify_petition_images.php' to check updated coverage.\n";
} else {
    echo "Would fix {$total} entries. Run without --dry-run to apply.\n";
}

echo "\n";
$db->close();
