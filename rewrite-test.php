<?php
/**
 * Upload stays next to index.php. Open:
 *   https://yoursite.com/rewrite-test.php
 * Then open:
 *   https://yoursite.com/this-path-does-not-exist-xyz
 * If rewrite works, the second URL should NOT be a Hostinger 404 page —
 * it should hit Laravel (app 404 or install page).
 */
header('Content-Type: text/plain; charset=utf-8');
echo "CodeBazaar rewrite test\n\n";
echo 'SCRIPT_NAME: ' . ($_SERVER['SCRIPT_NAME'] ?? '') . "\n";
echo 'REQUEST_URI: ' . ($_SERVER['REQUEST_URI'] ?? '') . "\n";
echo 'DOCUMENT_ROOT: ' . ($_SERVER['DOCUMENT_ROOT'] ?? '') . "\n";
echo 'PHP: ' . PHP_VERSION . "\n\n";
echo "Files in document root:\n";
foreach (['index.php', '.htaccess', 'public/index.php', 'public/.htaccess', 'vendor/autoload.php', 'storage/installed'] as $f) {
    $path = __DIR__ . '/' . $f;
    echo (is_file($path) ? '[OK] ' : '[NO] ') . $f . "\n";
}
echo "\nIf storage/installed exists, /install is blocked until you delete that file.\n";
echo "Recommended Hostinger setup: set document root to .../public (folder inside the app).\n";
