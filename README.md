# CodeBazaar Laravel

Digital marketplace with **web installer** for shared hosting.

## Shared hosting (no SSH)

1. Build ZIP (PC or Termux) with PHP 8.2+ and Composer:
```bash
git clone https://github.com/Montelent/codebazaar-laravel.git
cd codebazaar-laravel
composer config audit.block-insecure false
composer install --no-dev --optimize-autoloader
mkdir -p dist
zip -r dist/codebazaar-laravel-release.zip . -x ".git/*" -x "dist/*" -x ".env"
```

2. Upload and extract into the **subdomain folder** (must include `vendor/`).

3. Required folders: `config/` (with database.php etc.), `storage/logs`, `storage/framework/sessions`, `bootstrap/cache`, root `index.php`, root `.htaccess`.

4. PHP **8.2 or 8.3**. Writable: `storage`, `bootstrap/cache`.

5. Open `https://yoursite.com/check.php` then `https://yoursite.com/install`.

6. Delete `check.php` after success.

Document root = folder with `index.php` (no `/public` in URL needed).

## License

MIT
