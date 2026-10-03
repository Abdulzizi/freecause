<?php

return [
    // Serving-path activation requires a separately verified stateless renderer.
    'enabled' => env('EDGE_CACHE_ENABLED', false),
    'purge_enabled' => env('EDGE_CACHE_PURGE_ENABLED', false),
    'zone_id' => env('CLOUDFLARE_ZONE_ID', ''),
    'api_token' => env('CLOUDFLARE_API_TOKEN', ''),
    'base_url' => env('EDGE_CACHE_BASE_URL', 'https://www.freecause.com'),
    'ttl' => 600,
    'routes' => ['petitions.index', 'petitions.byCategory'],
];
