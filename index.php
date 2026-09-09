<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

/*
|--------------------------------------------------------------------------
| Root entry point for shared hosting (document root = project root)
| Prefer pointing the domain to /public when possible.
|--------------------------------------------------------------------------
*/

if (file_exists($maintenance = __DIR__.'/storage/framework/maintenance.php')) {
    require $maintenance;
}

if (! file_exists(__DIR__.'/vendor/autoload.php')) {
    http_response_code(500);
    header('Content-Type: text/plain; charset=utf-8');
    echo "Missing vendor/autoload.php.\nUpload a ZIP built with: composer install --no-dev\n";
    exit(1);
}

/*
|--------------------------------------------------------------------------
| CodeCanyon / shared-hosting bootstrap helper
| If no .env exists yet, copy .env.example so the installer can boot.
| Also ensure a temporary APP_KEY is present (installer overwrites it).
|--------------------------------------------------------------------------
*/
$envPath = __DIR__.'/.env';
$examplePath = __DIR__.'/.env.example';
$installedLock = __DIR__.'/storage/installed';

if (! is_file($envPath) && is_file($examplePath) && ! is_file($installedLock)) {
    @copy($examplePath, $envPath);
}

// Guarantee a usable APP_KEY before Laravel boots (prevents MissingAppKeyException)
if (is_file($envPath) && ! is_file($installedLock)) {
    $envContents = @file_get_contents($envPath) ?: '';
    if (! preg_match('/^APP_KEY=base64:[A-Za-z0-9+\/=]{40,}/m', $envContents)) {
        $tempKey = 'base64:'.base64_encode(random_bytes(32));
        if (preg_match('/^APP_KEY=.*$/m', $envContents)) {
            $envContents = preg_replace('/^APP_KEY=.*$/m', 'APP_KEY='.$tempKey, $envContents);
        } else {
            $envContents = "APP_KEY={$tempKey}\n".$envContents;
        }
        @file_put_contents($envPath, $envContents);
    }
}

require __DIR__.'/vendor/autoload.php';

/** @var \Illuminate\Foundation\Application $app */
$app = require_once __DIR__.'/bootstrap/app.php';
$app->usePublicPath(__DIR__);
$app->handleRequest(Request::capture());
