<?php
/**
 * Temporary diagnostics — DELETE after install works.
 * Open: https://yoursite.com/check.php
 */
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');

header('Content-Type: text/plain; charset=utf-8');

echo "PHP version: " . PHP_VERSION . "\n";
echo "SAPI: " . PHP_SAPI . "\n";
echo "__DIR__: " . __DIR__ . "\n\n";

$checks = [
    'vendor/autoload.php' => file_exists(__DIR__ . '/vendor/autoload.php'),
    'bootstrap/app.php' => file_exists(__DIR__ . '/bootstrap/app.php'),
    'index.php' => file_exists(__DIR__ . '/index.php'),
    '.env' => file_exists(__DIR__ . '/.env'),
    'routes/web.php' => file_exists(__DIR__ . '/routes/web.php'),
    'storage writable' => is_dir(__DIR__ . '/storage') && is_writable(__DIR__ . '/storage'),
    'bootstrap/cache writable' => is_dir(__DIR__ . '/bootstrap/cache') && is_writable(__DIR__ . '/bootstrap/cache'),
];

foreach ($checks as $label => $ok) {
    echo ($ok ? '[OK] ' : '[MISSING] ') . $label . "\n";
}

echo "\n--- Extensions ---\n";
foreach (['pdo', 'pdo_mysql', 'pdo_sqlite', 'mbstring', 'openssl', 'tokenizer', 'json', 'curl', 'fileinfo', 'ctype'] as $ext) {
    echo (extension_loaded($ext) ? '[OK] ' : '[NO] ') . $ext . "\n";
}

echo "\n--- Try autoload ---\n";
try {
    require __DIR__ . '/vendor/autoload.php';
    echo "autoload OK\n";
} catch (Throwable $e) {
    echo "autoload FAIL: " . $e->getMessage() . "\n";
    exit;
}

echo "\n--- Try bootstrap ---\n";
try {
    $app = require __DIR__ . '/bootstrap/app.php';
    echo "bootstrap OK: " . get_class($app) . "\n";
} catch (Throwable $e) {
    echo "bootstrap FAIL: " . $e->getMessage() . "\n";
    echo $e->getFile() . ':' . $e->getLine() . "\n";
}

echo "\nDone. Delete check.php when finished.\n";
