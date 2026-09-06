<?php

/**
 * Shared-hosting entry point (project ROOT).
 * Upload the whole project into the subdomain folder and open /install
 * without needing document root = public/.
 */

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Maintenance mode
if (file_exists($maintenance = __DIR__.'/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Composer autoload
if (! file_exists(__DIR__.'/vendor/autoload.php')) {
    http_response_code(500);
    echo 'Missing vendor/autoload.php. Upload the full release ZIP including the vendor folder.';
    exit(1);
}

require __DIR__.'/vendor/autoload.php';

/** @var \Illuminate\Foundation\Application $app */
$app = require_once __DIR__.'/bootstrap/app.php';

// Treat project root as the public path on shared hosting
$app->usePublicPath(__DIR__);

$app->handleRequest(Request::capture());
