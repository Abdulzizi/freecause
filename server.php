<?php

/**
 * Custom router for PHP built-in server (php artisan serve).
 * Routes /magazine/* requests to WordPress, everything else to Laravel.
 */

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
$publicPath = __DIR__ . '/public';

// Serve real static files directly
if ($uri !== '/' && file_exists($publicPath . $uri)) {
    return false;
}

// Route /magazine/* to WordPress index.php
if (str_starts_with($uri, '/magazine')) {
    $_SERVER['SCRIPT_FILENAME'] = $publicPath . '/magazine/index.php';
    $_SERVER['SCRIPT_NAME']     = '/magazine/index.php';
    $_SERVER['PHP_SELF']        = '/magazine/index.php';

    chdir($publicPath . '/magazine');
    require $publicPath . '/magazine/index.php';
    return true;
}

// Everything else goes to Laravel
require_once $publicPath . '/index.php';
