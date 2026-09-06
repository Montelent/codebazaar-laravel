# CodeBazaar Laravel

Full digital marketplace (CodeCanyon-style) in Laravel with web installer and **shared-hosting URL fix** (`/index.php/...` when Apache rewrite is off).

## Features

- Storefront: home, search, categories, product detail, cart, checkout (free + Stripe)
- Buyer account: purchases & downloads
- **Admin:** dashboard, products, categories (+ attribute schema), users, blog (TinyMCE), CMS pages, licenses, orders, site settings (hero, announcement, footer, colors, SEO)
- Web installer at `/install` with lock file
- `FORCE_INDEX_PHP=true` so all `route()` links work on Hostinger without rewrite

## Shared hosting URLs

After install, `.env` contains:

```env
APP_URL=https://yoursite.com/index.php
FORCE_INDEX_PHP=true
```

Use links like `/index.php/login` if rewrite is disabled. Menu links are generated automatically with `index.php`.

## Build release ZIP

```bash
composer config audit.block-insecure false
composer install --no-dev --optimize-autoloader
zip -r dist/codebazaar-laravel-release.zip . -x ".git/*" -x "dist/*" -x ".env"
```

## License

MIT
