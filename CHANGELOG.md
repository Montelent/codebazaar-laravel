# Changelog — CodeBazaar Laravel

All notable changes to this project are documented in this file.

## [1.0.0] — 2026-09-09

### Added
- Full CodeCanyon-style digital marketplace built on Laravel 11
- Web installer at `/install` with lock file and silent 404 after install
- Product management with dynamic category attributes, sub-categories, bundle media, galleries
- Blog module with free TinyMCE (jsDelivr GPL) and responsive grid
- Multi-gateway payments: Stripe, Paystack, Monnify, crypto wallets, free products
- Storage drivers: local, S3-compatible, Backblaze B2
- SMTP with presets (Gmail, Outlook, Hostinger, Resend, custom)
- Newsletter with shortcodes
- Ad placement system
- Full SEO: sitemap.xml, robots.txt, canonicals, Open Graph, JSON-LD schema
- Navigation / header / footer / theme color settings
- License activation: Envato purchase code + JigSource.store (API + signed JS1 keys) + author master key
- Domain binding on activation
- Admin lock (except basic settings, blog, system tools) until activated
- System tools (migrate + clear caches) for shared hosting without SSH
- Mobile-responsive admin with drawer menus
- Buyer account: purchases, downloads, wishlist, collections, reviews
- Shared-hosting friendly `FORCE_INDEX_PHP` support

### Security
- Product activation middleware
- Installer lock
- Role-based admin gate

### Compatibility
- PHP 8.2+
- Shared hosting (Hostinger tested)
- Apache / Nginx
