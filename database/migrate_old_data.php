#!/usr/bin/env php
<?php

/**
 * FreeCause — Old DB → New DB Migration Script
 *
 * BEFORE RUNNING:
 *   1. Open SSH tunnel in a separate terminal and leave it running:
 *      ssh -L 3307:localhost:3306 azizi@138.197.120.103
 *
 *   2. Reset your local DB first:
 *      php artisan migrate:fresh --seed
 *
 *   3. Then run this script:
 *      php database/migrate_old_data.php
 *
 * Order: user_levels → users → categories → petitions → petition_translations → signatures
 */

// ─── CONFIG ──────────────────────────────────────────────────────────────────

// OLD DB — accessed via SSH tunnel on port 3307
define('OLD_HOST', '127.0.0.1');
define('OLD_PORT', 3306);
define('OLD_USER', 'freecause');
define('OLD_PASS', 'secret');
define('OLD_DB',   'freecause_old');

// New DB — auto-read from your Laravel .env
$envFile = __DIR__ . '/../.env';
$env = [];
foreach (file($envFile) as $line) {
    $line = trim($line);
    if (!$line || str_starts_with($line, '#')) continue;
    [$k, $v] = array_pad(explode('=', $line, 2), 2, '');
    $env[trim($k)] = trim($v, '"\'');
}

define('NEW_HOST', $env['DB_HOST']     ?? '127.0.0.1');
define('NEW_PORT', (int)($env['DB_PORT'] ?? 3306));
define('NEW_USER', $env['DB_USERNAME'] ?? 'root');
define('NEW_PASS', $env['DB_PASSWORD'] ?? '');
define('NEW_DB',   $env['DB_DATABASE'] ?? 'freecause');

define('BATCH_SIZE', 1000);

// ─── CONNECT ─────────────────────────────────────────────────────────────────

echo "Connecting to OLD DB (via SSH tunnel on port " . OLD_PORT . ")...\n";
$old = new mysqli(OLD_HOST, OLD_USER, OLD_PASS, OLD_DB, OLD_PORT);
if ($old->connect_error) die("OLD DB connection failed: " . $old->connect_error . "\n  Make sure your SSH tunnel is running:\n  ssh -L 3307:localhost:3306 azizi@138.197.120.103\n");
$old->set_charset('utf8mb4');
echo "OLD DB connected.\n";

echo "Connecting to NEW DB...\n";
$new = new mysqli(NEW_HOST, NEW_USER, NEW_PASS, NEW_DB, NEW_PORT);
if ($new->connect_error) die("NEW DB connection failed: " . $new->connect_error . "\n");
$new->set_charset('utf8mb4');
echo "NEW DB connected.\n\n";

// ─── HELPERS ─────────────────────────────────────────────────────────────────

function prefix_to_locale(string $prefix): string {
    $map = [
        'ws_'     => 'en',  // world site (main English)
        'uk_'     => 'en',
        'ca_'     => 'en',
        'au_'     => 'en',
        'nz_'     => 'en',
        'ie_'     => 'en',
        'in_'     => 'en',
        'ph_'     => 'en',
        'it_'     => 'it',
        'ch_it_'  => 'it',
        'fr_'     => 'fr',
        'ca_fr_'  => 'fr',
        'ch_fr_'  => 'fr',
        'de_'     => 'de',
        'at_'     => 'de',
        'ch_'     => 'de',
        'es_'     => 'es',
        'com_es_' => 'es',
        'ar_'     => 'es',
        'mx_'     => 'es',
        'co_'     => 'es',
        'pe_'     => 'es',
        'pl_'     => 'pl',
        'br_'     => 'pt',
        'pt_'     => 'pt',
        'nl_'     => 'nl',
        'ru_'     => 'ru',
        'gr_'     => 'el',
        'ro_'     => 'ro',
        'se_'     => 'sv',
        'tr_'     => 'tr',
        'dk_'     => 'da',
        'eu_'     => 'eu',
    ];
    return $map[$prefix] ?? 'en';
}

function esc($new, $val): string {
    return "'" . $new->real_escape_string((string)$val) . "'";
}

function now_sql(): string {
    return date('Y-m-d H:i:s');
}

function slugify(string $text): string {
    $text = mb_strtolower($text, 'UTF-8');
    $text = preg_replace('/[^\w\s-]/u', '', $text);
    $text = preg_replace('/[\s_-]+/', '-', $text);
    return trim($text, '-');
}

function batch_insert($new, string $table, array $rows, array $columns): void {
    if (empty($rows)) return;
    $cols = '`' . implode('`, `', $columns) . '`';
    foreach (array_chunk($rows, BATCH_SIZE) as $chunk) {
        $values = implode(",\n", $chunk);
        $sql = "INSERT IGNORE INTO `{$table}` ({$cols}) VALUES {$values}";
        if (!$new->query($sql)) {
            echo "  ERROR inserting into {$table}: " . $new->error . "\n";
        }
    }
}

// ─── STEP 1: USER LEVELS ─────────────────────────────────────────────────────

echo "=== STEP 1: User Levels ===\n";

$result = $old->query("SELECT id, name FROM user_levels");
$rows = [];
while ($row = $result->fetch_assoc()) {
    $rows[] = "(" . (int)$row['id'] . ", " . esc($new, $row['name']) . ", " . esc($new, now_sql()) . ", " . esc($new, now_sql()) . ")";
}

$new->query("SET FOREIGN_KEY_CHECKS=0");
batch_insert($new, 'user_levels', $rows, ['id', 'name', 'created_at', 'updated_at']);
$new->query("SET FOREIGN_KEY_CHECKS=1");
echo "  Inserted " . count($rows) . " user levels.\n\n";

// ─── STEP 2: USERS ───────────────────────────────────────────────────────────

echo "=== STEP 2: Users ===\n";

$lastUserId = (int)$new->query("SELECT COALESCE(MAX(id), 0) as m FROM users")->fetch_assoc()['m'];
$total      = $old->query("SELECT COUNT(*) as c FROM users WHERE deleted='0' AND id > {$lastUserId}")->fetch_assoc()['c'];
echo "  Resuming from user id > {$lastUserId}. Remaining: {$total}\n";

$migrated = 0;

// Build google_id / facebook_id lookup from users_socials
echo "  Loading social logins...\n";
$socials = [];
$sr = $old->query("SELECT user_id, oauth_provider, oauth_uid FROM users_socials");
while ($s = $sr->fetch_assoc()) {
    $socials[$s['user_id']][$s['oauth_provider']] = $s['oauth_uid'];
}

$lastId = $lastUserId;
while (true) {
    $result = $old->query("
        SELECT id, username, password, email, name, surname,
               level_id, active, city, ip, newsletter, prefix, dt_registered
        FROM users
        WHERE deleted='0' AND id > {$lastId}
        ORDER BY id ASC
        LIMIT " . BATCH_SIZE
    );

    if ($result->num_rows === 0) break;

    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $locale    = prefix_to_locale($row['prefix']);
        $firstName = $row['name'] ?? '';
        $lastName  = $row['surname'] ?? '';
        $fullName  = trim($firstName . ' ' . $lastName) ?: $row['username'];
        $verified  = $row['active'] === '1' ? 1 : 0;
        $googleId  = $socials[$row['id']]['google'] ?? null;
        $fbId      = $socials[$row['id']]['facebook'] ?? null;
        $createdAt = ($row['dt_registered'] && $row['dt_registered'] !== '0000-00-00 00:00:00')
                     ? $row['dt_registered'] : now_sql();

        $rows[] = "(" .
            (int)$row['id'] . "," .
            esc($new, $fullName) . "," .
            esc($new, $firstName) . "," .
            esc($new, $lastName) . "," .
            esc($new, $row['email']) . "," .
            esc($new, $row['password']) . "," .  // SHA1 — legacy login handles it
            esc($new, $locale) . "," .
            (int)$row['level_id'] . "," .
            $verified . "," .
            esc($new, $row['ip']) . "," .
            esc($new, $row['city']) . "," .
            ($googleId ? esc($new, $googleId) : 'NULL') . "," .
            ($fbId ? esc($new, $fbId) : 'NULL') . "," .
            esc($new, $createdAt) . "," .
            esc($new, $createdAt) .
        ")";
        $lastId = (int)$row['id'];
    }

    batch_insert($new, 'users', $rows, [
        'id', 'name', 'first_name', 'last_name', 'email', 'password',
        'locale', 'level_id', 'verified', 'ip', 'city',
        'google_id', 'facebook_id', 'created_at', 'updated_at'
    ]);

    $migrated += count($rows);
    echo "  Migrated {$migrated}/{$total} users...\r";
}

echo "\n  Done.\n\n";

// ─── STEP 3: CATEGORIES ──────────────────────────────────────────────────────

echo "=== STEP 3: Categories ===\n";
echo "  NOTE: New DB uses 15 fixed categories with translations.\n";
echo "  Old DB has " . $old->query("SELECT COUNT(*) as c FROM petition_categories")->fetch_assoc()['c'] . " categories.\n";
echo "  Building old_id → new_id map from petition_categorymeta...\n";

// The new categories are already seeded (15 fixed ones).
// We need to map old category_ids to new ones by matching names.
// Get old category names in English
$oldCats = [];
$cr = $old->query("SELECT pc.id, pc.name, pc.prefix FROM petition_categories pc");
while ($c = $cr->fetch_assoc()) {
    $oldCats[$c['id']] = ['name' => $c['name'], 'prefix' => $c['prefix']];
}

// Get new categories with English translation
$newCats = [];
$nr = $new->query("
    SELECT c.id, ct.name
    FROM categories c
    JOIN category_translations ct ON ct.category_id = c.id
    WHERE ct.locale = 'en'
");
while ($c = $nr->fetch_assoc()) {
    $newCats[strtolower($c['name'])] = $c['id'];
}

// Build mapping: old_category_id → new_category_id
// For unmapped ones, default to first category (id=1)
$categoryMap = [];
foreach ($oldCats as $oldId => $cat) {
    $name = strtolower($cat['name']);
    // Try exact match first
    if (isset($newCats[$name])) {
        $categoryMap[$oldId] = $newCats[$name];
        continue;
    }
    // Try partial match
    $matched = false;
    foreach ($newCats as $newName => $newId) {
        if (str_contains($name, $newName) || str_contains($newName, $name)) {
            $categoryMap[$oldId] = $newId;
            $matched = true;
            break;
        }
    }
    if (!$matched) {
        $categoryMap[$oldId] = 1; // fallback to first category
    }
}

// Save map to a file for review
file_put_contents('/tmp/category_map.json', json_encode($categoryMap, JSON_PRETTY_PRINT));
echo "  Category map saved to /tmp/category_map.json — review before continuing.\n";
echo "  Mapped " . count($categoryMap) . " old categories to new ones.\n\n";

// ─── STEP 4: PETITIONS ───────────────────────────────────────────────────────

echo "=== STEP 4: Petitions ===\n";

$lastPetitionId = (int)$new->query("SELECT COALESCE(MAX(id), 0) as m FROM petitions")->fetch_assoc()['m'];
$total          = $old->query("SELECT COUNT(*) as c FROM petitions WHERE id > {$lastPetitionId}")->fetch_assoc()['c'];
echo "  Resuming from petition id > {$lastPetitionId}. Remaining: {$total}\n";

$migrated = 0;
$lastId   = $lastPetitionId;

while (true) {
    $result = $old->query("
        SELECT id, user_id, active, verified, alive, featured,
               goal, target, city, community, community_url,
               yt, pic, category_id, dt, likes
        FROM petitions
        WHERE id > {$lastId}
        ORDER BY id ASC
        LIMIT " . BATCH_SIZE
    );

    if ($result->num_rows === 0) break;

    $petRows = [];
    while ($row = $result->fetch_assoc()) {
        $isPublished = ($row['active'] === '1' && $row['verified'] === '1' && $row['alive'] === '1');
        $status      = $isPublished ? 'published' : 'draft';
        $isActive    = $isPublished ? 1 : 0;
        $isFeatured  = $row['featured'] === '1' ? 1 : 0;
        $newCatId    = $categoryMap[$row['category_id']] ?? 1;
        $createdAt   = ($row['dt'] && $row['dt'] !== '0000-00-00 00:00:00') ? $row['dt'] : now_sql();

        $petRows[] = "(" .
            (int)$row['id'] . "," .
            (int)$row['user_id'] . "," .
            esc($new, $status) . "," .
            $isActive . "," .
            $isFeatured . "," .
            (int)$row['goal'] . "," .
            esc($new, $row['target']) . "," .
            esc($new, $row['city']) . "," .
            esc($new, $row['community']) . "," .
            esc($new, $row['community_url']) . "," .
            esc($new, $row['yt']) . "," .
            esc($new, $row['pic']) . "," .
            (int)$newCatId . "," .
            "0," . // signature_count — will update after signatures migrate
            esc($new, $createdAt) . "," .
            esc($new, $createdAt) .
        ")";
        $lastId = (int)$row['id'];
    }

    batch_insert($new, 'petitions', $petRows, [
        'id', 'user_id', 'status', 'is_active', 'is_featured',
        'goal_signatures', 'target', 'city', 'community', 'community_url',
        'youtube_url', 'image_url', 'category_id', 'signature_count',
        'created_at', 'updated_at'
    ]);

    $migrated += count($petRows);
    echo "  Migrated {$migrated}/{$total} petitions...\r";
}

echo "\n  Done.\n\n";

// ─── STEP 5: PETITION TRANSLATIONS ───────────────────────────────────────────

echo "=== STEP 5: Petition Translations ===\n";
echo "  (Each old petition row = one translation row, locale from prefix)\n";

$lastTranslationId = (int)$new->query("SELECT COALESCE(MAX(petition_id), 0) as m FROM petition_translations")->fetch_assoc()['m'];
$total             = $old->query("SELECT COUNT(*) as c FROM petitions WHERE id > {$lastTranslationId}")->fetch_assoc()['c'];
echo "  Resuming from petition_id > {$lastTranslationId}. Remaining: {$total}\n";

$migrated = 0;
$lastId   = $lastTranslationId;

while (true) {
    $result = $old->query("
        SELECT id, title, text, slug, prefix
        FROM petitions
        WHERE id > {$lastId}
        ORDER BY id ASC
        LIMIT " . BATCH_SIZE
    );

    if ($result->num_rows === 0) break;

    $trRows = [];
    while ($row = $result->fetch_assoc()) {
        $locale = prefix_to_locale($row['prefix']);
        $slug   = $row['slug'] ?: slugify($row['title']) ?: 'petition-' . $row['id'];

        $trRows[] = "(" .
            (int)$row['id'] . "," .
            esc($new, $locale) . "," .
            esc($new, $row['title']) . "," .
            esc($new, $row['text']) . "," .
            esc($new, $slug) . "," .
            esc($new, now_sql()) . "," .
            esc($new, now_sql()) .
        ")";
        $lastId = (int)$row['id'];
    }

    batch_insert($new, 'petition_translations', $trRows, [
        'petition_id', 'locale', 'title', 'description', 'slug',
        'created_at', 'updated_at'
    ]);

    $migrated += count($trRows);
    echo "  Migrated {$migrated}/{$total} translations...\r";
}

echo "\n  Done.\n\n";

// ─── STEP 6: SIGNATURES ──────────────────────────────────────────────────────

echo "=== STEP 6: Signatures ===\n";

$lastSigId = (int)$new->query("SELECT COALESCE(MAX(id), 0) as m FROM signatures")->fetch_assoc()['m'];
$total     = $old->query("SELECT COUNT(*) as c FROM signatures WHERE deleted='0' AND id > {$lastSigId}")->fetch_assoc()['c'];
echo "  Resuming from signature id > {$lastSigId}. Remaining: {$total}\n";

$migrated = 0;
$lastId   = $lastSigId;

while (true) {
    $result = $old->query("
        SELECT s.id, s.user_id, s.petition_id, s.verified,
               s.text, s.ip, s.prefix, s.dt,
               u.name, u.surname, u.email
        FROM signatures s
        LEFT JOIN users u ON u.id = s.user_id
        WHERE s.deleted='0' AND s.id > {$lastId}
        ORDER BY s.id ASC
        LIMIT " . BATCH_SIZE
    );

    if ($result->num_rows === 0) break;

    $sigRows = [];
    while ($row = $result->fetch_assoc()) {
        $locale    = prefix_to_locale($row['prefix']);
        $confirmed = $row['verified'] === '1' ? 1 : 0;
        $name      = trim(($row['name'] ?? '') . ' ' . ($row['surname'] ?? '')) ?: 'Anonymous';
        $createdAt = ($row['dt'] && $row['dt'] !== '0000-00-00 00:00:00') ? $row['dt'] : now_sql();

        $sigRows[] = "(" .
            (int)$row['id'] . "," .
            (int)$row['petition_id'] . "," .
            (int)$row['user_id'] . "," .
            esc($new, $name) . "," .
            esc($new, $row['email'] ?? '') . "," .
            esc($new, $locale) . "," .
            esc($new, $row['text'] ?? '') . "," .
            $confirmed . "," .
            esc($new, $row['ip']) . "," .
            "0," . // is_spam
            esc($new, $createdAt) . "," .
            esc($new, $createdAt) .
        ")";
        $lastId = (int)$row['id'];
    }

    batch_insert($new, 'signatures', $sigRows, [
        'id', 'petition_id', 'user_id', 'name', 'email',
        'locale', 'text', 'confirmed', 'ip_address', 'is_spam',
        'created_at', 'updated_at'
    ]);

    $migrated += count($sigRows);
    echo "  Migrated {$migrated}/{$total} signatures...\r";
}

echo "\n  Done.\n\n";

// ─── STEP 7: UPDATE SIGNATURE COUNTS ─────────────────────────────────────────

echo "=== STEP 7: Updating signature counts on petitions ===\n";

$new->query("
    UPDATE petitions p
    SET signature_count = (
        SELECT COUNT(*) FROM signatures s
        WHERE s.petition_id = p.id AND s.confirmed = 1
    )
");

echo "  Done.\n\n";

// ─── STEP 8: BANNED IPs ───────────────────────────────────────────────────────

echo "=== STEP 8: Banned IPs ===\n";

$result = $old->query("SELECT ip FROM _banned_ips");
$rows   = [];
while ($row = $result->fetch_assoc()) {
    $rows[] = "(" . esc($new, $row['ip']) . ", " . esc($new, now_sql()) . ", " . esc($new, now_sql()) . ")";
}

batch_insert($new, 'banned_ips', $rows, ['ip', 'created_at', 'updated_at']);
echo "  Inserted " . count($rows) . " banned IPs.\n\n";

// ─── DONE ─────────────────────────────────────────────────────────────────────

$old->close();
$new->close();

echo "=== MIGRATION COMPLETE ===\n";
echo "Next steps:\n";
echo "  1. Review /tmp/category_map.json and verify category mappings\n";
echo "  2. Run: php artisan optimize:clear\n";
echo "  3. Test login with an old user account\n";
echo "  4. Check petition pages load correctly\n";