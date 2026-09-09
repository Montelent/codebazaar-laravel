# CodeBazaar Laravel

**Version 1.0.0** — Full digital marketplace (CodeCanyon-style) built with Laravel 11.

Web installer, shared-hosting friendly, multi-payment gateways, dynamic attributes, license activation (Envato + JigSource), and complete admin panel.

## Features

- Storefront: home, search, categories, product detail, cart, checkout (Stripe / Paystack / Monnify / crypto / free)
- Buyer account: purchases, downloads, wishlist, collections, reviews
- **Admin:** dashboard, products (dynamic attributes + bundles), categories, media, blog (free TinyMCE), CMS pages, licenses, orders, newsletter, ads
- Full settings: general, payments, SMTP presets, storage (S3/Backblaze), navigation, header/footer, schema/SEO, theme colors
- Web installer at `/install` with lock file (silent 404 after install)
- License activation with domain binding
- `FORCE_INDEX_PHP` support for hosts without rewrite
- System tools (migrate + clear caches) usable without SSH

## Quick start

1. Upload / clone the project
2. Point document root to `public/` (recommended)
3. Open `/install` and finish the wizard
4. Log in → **License activation** → enter purchase code
5. Configure Payments + SMTP under Settings

## Documentation

| File | Content |
|------|---------|
| [USER_GUIDE.md](USER_GUIDE.md) | Complete guide: install, products, blogs, all settings, how to use |
| [Installations.html](Installations.html) | Buyer-focused HTML install guide |
| [INSTALL.md](INSTALL.md) | Technical install notes |
| [HOSTINGER.md](HOSTINGER.md) | Shared hosting / Hostinger tips |
| [CODECANYON_PACKAGING.md](CODECANYON_PACKAGING.md) | How to compile the ZIP for CodeCanyon upload |
| [CHANGELOG.md](CHANGELOG.md) | Version history |
| [Documentation.md](Documentation.md) | Architecture overview |

## Build release ZIP (for CodeCanyon)

```bash
composer config audit.block-insecure false
composer install --no-dev --optimize-autoloader --no-interaction
zip -r ../codebazaar-laravel-v1.0.0.zip . \
  -x "*.git*" -x "*.env" -x "storage/logs/*" \
  -x "storage/framework/*/*" -x "bootstrap/cache/*.php" -x "tests/*"
```

See **CODECANYON_PACKAGING.md** for the full checklist.

## License

MIT (commercial use via CodeCanyon / JigSource license required for buyers).
