#!/usr/bin/env php
<?php

// Maps old prefix → pics folder name on the server
$prefixToFolder = [
    'ws_'     => 'en_US',
    'uk_'     => 'en_GB',
    'ca_'     => 'en_CA',
    'au_'     => 'en_AU',
    'nz_'     => 'en_NZ',
    'ie_'     => 'en_IE',
    'in_'     => 'en_IN',
    'ph_'     => 'en_PH',
    'eu_'     => 'en_EU',
    'it_'     => 'it_IT',
    'ch_it_'  => 'it_CH',
    'fr_'     => 'fr_FR',
    'ca_fr_'  => 'fr_CA',
    'ch_fr_'  => 'fr_CH',
    'de_'     => 'de_DE',
    'at_'     => 'de_AT',
    'ch_'     => 'de_CH',
    'es_'     => 'es_ES',
    'com_es_' => 'es_ES',
    'ar_'     => 'es_AR',
    'mx_'     => 'es_MX',
    'co_'     => 'es_CO',
    'pe_'     => 'es_PE',
    'pl_'     => 'pl_PL',
    'br_'     => 'pt_BR',
    'pt_'     => 'pt_PT',
    'nl_'     => 'nl_NL',
    'ru_'     => 'ru_RU',
    'gr_'     => 'el_GR',
    'ro_'     => 'ro_RO',
    'se_'     => 'sv_SE',
    'tr_'     => 'tr_TR',
    'dk_'     => 'da_DK',
];

// Load new DB config from .env
$envFile = __DIR__ . '/../.env';
$env = [];
foreach (file($envFile) as $line) {
    $line = trim($line);
    if (!$line || str_starts_with($line, '#')) continue;
    [$k, $v] = array_pad(explode('=', $line, 2), 2, '');
    $env[trim($k)] = trim($v, '"\'');
}

$new = new mysqli(
    $env['DB_HOST'] ?? '127.0.0.1',
    $env['DB_USERNAME'] ?? 'root',
    $env['DB_PASSWORD'] ?? '',
    $env['DB_DATABASE'] ?? 'freecause',
    (int)($env['DB_PORT'] ?? 3306)
);
if ($new->connect_error) die("New DB connection failed: " . $new->connect_error . "\n");
$new->set_charset('utf8mb4');

$old = new mysqli('127.0.0.1', 'freecause', 'secret', 'freecause_old', 3306);
if ($old->connect_error) die("Old DB connection failed: " . $old->connect_error . "\n");
$old->set_charset('utf8mb4');

echo "Loading old petition prefix + pic...\n";
$result = $old->query("SELECT id, prefix, pic FROM petitions WHERE pic IS NOT NULL AND pic != ''");

$updated = 0;
$skipped = 0;

while ($row = $result->fetch_assoc()) {
    $folder = $prefixToFolder[$row['prefix']] ?? 'en_US';
    $imageUrl = $folder . '/' . $row['pic'];

    $id  = (int)$row['id'];
    $val = $new->real_escape_string($imageUrl);

    $new->query("UPDATE petitions SET image_url = '{$val}' WHERE id = {$id}");
    $updated++;

    if ($updated % 1000 === 0) echo "  Updated {$updated}...\r";
}

echo "\nDone. Updated {$updated} petitions.\n";

$old->close();
$new->close();
