<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
header('Content-Type: text/plain; charset=utf-8');

echo "CodeBazaar diagnostics\n";
echo 'PHP: ' . PHP_VERSION . "\n";
echo 'DIR: ' . __DIR__ . "\n\n";

foreach (['vendor/autoload.php','bootstrap/app.php','config/database.php','config/app.php','routes/web.php','index.php','.env'] as $f) {
    echo (file_exists(__DIR__ . '/' . $f) ? '[OK] ' : '[NO] ') . $f . "\n";
}
echo "\nstorage writable: " . (is_dir(__DIR__.'/storage') && is_writable(__DIR__.'/storage') ? 'YES' : 'NO') . "\n";
echo 'bootstrap/cache writable: ' . (is_dir(__DIR__.'/bootstrap/cache') && is_writable(__DIR__.'/bootstrap/cache') ? 'YES' : 'NO') . "\n";
echo "\n--- Extensions ---\n";
foreach (['pdo','pdo_mysql','pdo_sqlite','mbstring','openssl','tokenizer','json','curl','fileinfo','ctype','bcmath'] as $ext) {
    echo (extension_loaded($ext) ? '[OK] ' : '[NO] ') . $ext . "\n";
}
echo "\n--- Bootstrap ---\n";
try {
    require __DIR__.'/vendor/autoload.php';
    echo "autoload OK\n";
    $app = require __DIR__.'/bootstrap/app.php';
    echo 'bootstrap OK: ' . get_class($app) . "\n";
} catch (Throwable $e) {
    echo 'FAIL: ' . $e->getMessage() . "\n" . $e->getFile() . ':' . $e->getLine() . "\n";
}
echo "\nDelete check.php after install works.\n";
