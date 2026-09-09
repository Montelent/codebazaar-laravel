# CodeBazaar Laravel — Complete User & Admin Guide (v1.0.0)

This guide covers installation, initial setup, day-to-day use of the marketplace, managing products, blogs, all settings, license activation, and sales packaging.

---

## Table of Contents

1. [Version & Requirements](#1-version--requirements)
2. [Installation](#2-installation)
3. [First Login & License Activation](#3-first-login--license-activation)
4. [Dashboard Overview](#4-dashboard-overview)
5. [Adding & Editing Products](#5-adding--editing-products)
6. [Categories, Attributes & Tags](#6-categories-attributes--tags)
7. [Media Library](#7-media-library)
8. [Blog: Adding & Editing Posts](#8-blog-adding--editing-posts)
9. [CMS Pages](#9-cms-pages)
10. [Orders & Users](#10-orders--users)
11. [All Settings Explained](#11-all-settings-explained)
12. [Newsletter](#12-newsletter)
13. [System Tools (Maintenance)](#13-system-tools-maintenance)
14. [Storefront Features (Buyer Side)](#14-storefront-features-buyer-side)
15. [Cron & Scheduled Tasks](#15-cron--scheduled-tasks)
16. [Troubleshooting](#16-troubleshooting)
17. [Packaging for CodeCanyon / Sales](#17-packaging-for-codecanyon--sales)

---

## 1. Version & Requirements

**Current version:** `1.0.0`  
(Defined in `composer.json` and `VERSION` file.)

**Server requirements**
- PHP 8.2 or higher (8.3 recommended)
- MySQL 5.7+ / MariaDB 10.3+ (or PostgreSQL / SQLite for testing)
- PHP extensions: bcmath, ctypetype, curl, fileinfo, json, mbstring, openssl, pdo, tokenizer, xml, zip
- Composer 2.x (only needed if `vendor/` is not included in the package)
- Writable directories: `storage/` and `bootstrap/cache/`
- Apache with mod_rewrite (or Nginx equivalent) — or use the included root `.htaccess` + `FORCE_INDEX_PHP`

---

## 2. Installation

### Option A — Web Installer (recommended for shared hosting / Hostinger)

1. Upload the extracted package to your server (e.g. `public_html/coderrr/`).
2. Preferably set the domain **document root** to the `public/` folder inside the project.
3. Visit `https://your-domain.com/install`
4. Follow the wizard:
   - Requirements check
   - Database credentials + App URL
   - Create admin account
5. After success, `/install` becomes 404 (locked). Log in at `/login`.

### Option B — Git / SSH (developers)

```bash
cd /path/to/domains/yoursite.com/public_html
git clone https://github.com/Montelent/codebazaar-laravel.git coderrr
cd coderrr
composer install --no-dev --optimize-autoloader
cp .env.example .env
php artisan key:generate
# Edit .env: APP_URL, DB_*, mail, optional payment keys
php artisan migrate --force
php artisan storage:link
chmod -R 775 storage bootstrap/cache
```

### Option C — Manual ZIP without Composer

If the sales ZIP includes `vendor/`, just upload, make `storage` and `bootstrap/cache` writable, open `/install`.

**Important files after install**
- `storage/installed` — presence locks the installer
- `.env` — never commit this

See also: `INSTALL.md`, `HOSTINGER.md`, `Installations.html`.

---

## 3. First Login & License Activation

1. Go to `/login` with the admin credentials created during install.
2. You land on the Admin dashboard.
3. **Until activated**, most admin sections are locked except:
   - Dashboard
   - License Activation
   - Basic / General settings
   - Blog
   - System Tools

4. Open **Admin → License activation**
5. Enter one of:
   - **Envato / CodeCanyon purchase code** (UUID format)
   - **JigSource.store license key** (or signed `JS1.*.*` key)
   - Author master key (private — only for your own installs)

The license is **bound to the current domain**. Changing domain requires re-activation or author intervention.

For live Envato verification set in `.env`:
```env
ENVATO_PERSONAL_TOKEN=your_token
ENVATO_ITEM_ID=your_item_id
```

For author unrestricted builds:
```env
DISABLE_PRODUCT_LICENSE=true
```

---

## 4. Dashboard Overview

After activation you have full access:

- **Dashboard** — quick stats (products, orders, users, revenue)
- **Products** — manage digital items
- **Categories / Attributes / Tags**
- **Media** — upload images & files
- **Blog** — posts with TinyMCE editor
- **Pages** — static CMS pages
- **Licenses** — product license types (Regular, Extended, etc.)
- **Orders** — view & manage purchases
- **Users / Members / Staff**
- **Newsletter**
- **Settings hub** — all configuration screens
- **System tools** — migrate & clear caches without SSH

Mobile: use the hamburger menu (☰) to open the full sidebar.

---

## 5. Adding & Editing Products

**Path:** Admin → Products → Create / Edit

### Key fields

| Field | Description |
|-------|-------------|
| Title | Product name (used for slug generation) |
| Slug | URL-friendly name (auto or manual) |
| Description | Rich HTML via free TinyMCE (jsDelivr GPL build) |
| Short description | Plain text summary for cards |
| Category | Primary category (triggers dynamic attributes) |
| Sub-category | Optional |
| Price / Sale price | Regular & discounted price |
| License options | Link to defined license types |
| Main download file | URL or media path for the primary ZIP/file |
| Bundle / additional media | Extra downloadable files |
| Preview images / screenshots | Gallery |
| Featured | Show on homepage |
| Status | Draft / Published |
| Attributes | Dynamic fields based on selected category (e.g. Framework, Compatible with, Files included) |
| Tags | Free-form or pre-defined |
| SEO title / meta description | Per-product SEO |
| Canonical / Open Graph | Handled automatically + schema settings |

### Workflow tips

1. First create Categories and define Attribute schema for that category.
2. Upload images to Media library (or upload directly on product form).
3. Set “Main download file” to a publicly accessible URL or a file stored via the configured storage driver.
4. Use **Featured** for homepage highlight cards.
5. After saving, visit the public product page to verify layout, breadcrumbs, attribute links, and download button (buyers only after paid order).

Dynamic attributes appear automatically when you change the category on the product form.

---

## 6. Categories, Attributes & Tags

- **Categories** — hierarchical (parent + children). Used for navigation, filters, and attribute schema.
- **Attributes** — Admin → Attributes. Define key/value or select options per category. These become filterable links on product pages and search.
- **Tags** — simple labels for further filtering.

Recommended structure (CodeCanyon style):
- PHP Scripts → Laravel, WordPress, CodeIgniter…
- Templates → HTML, React, Vue…
- Plugins → etc.

---

## 7. Media Library

Admin → Media

- Upload images, ZIPs, PDFs, etc.
- Files are stored according to **Settings → Storage** (local, S3, Backblaze, etc.).
- Use the media picker on product forms or insert URLs into descriptions.
- Public serving route: `/media/file/{path}`

After changing storage driver, run System tools → Clear caches and ensure `php artisan storage:link` (or manual symlink) if using local public disk.

---

## 8. Blog: Adding & Editing Posts

**Path:** Admin → Blog → Create / Edit

- Title, slug, featured image
- Body: free TinyMCE editor
- Excerpt / summary
- Status: Draft / Published
- SEO fields
- Publish date

Public routes:
- `/blog` — grid listing
- `/blog/{slug}` — single post

Blog remains available even when the product is license-locked, so you can start content marketing immediately.

---

## 9. CMS Pages

Admin → Pages

Create static pages (About, Terms, Privacy, Support, etc.).  
They appear at `/page/{slug}` and can be linked from the Navigation / Footer settings.

---

## 10. Orders & Users

- **Orders** — list + detail view. Shows buyer, items, total, payment status, download access.
- **Users / Members / Staff** — manage buyers and admin accounts. Roles: `admin`, `buyer` (and staff variants if configured).

Buyers access downloads at `/account/downloads` after a paid (or free) order.

---

## 11. All Settings Explained

Access via **Admin → Settings** (hub) or individual sidebar links.

### General
- Site name, tagline, logo, favicon
- Homepage hero title, subtitle, CTA buttons
- Announcement bar
- Default currency / timezone
- Contact email

### Payments
Enable and configure:
- Stripe
- Paystack
- Monnify
- Crypto wallet addresses
- Other gateways as implemented

Test mode vs live keys. Free products (price ≤ 0) complete without payment gateway.

### SMTP / Email
Presets for Gmail, Outlook, Hostinger, Resend, Mailgun, custom SMTP.  
Always send a **Test email** after saving.

Used for:
- Order confirmations
- Password resets
- Email verification
- Newsletter

### Storage
- Local (default)
- Amazon S3 / compatible
- Backblaze B2
- Other S3-compatible endpoints

Affects product media and main download files.

### Navigation
Primary menu, secondary links, mobile drawer items. Drag-and-drop or ordered list (depending on UI).

### Header / Footer
Logo placement, social links, copyright text, footer columns, custom HTML blocks.

### Ads
Ad placement slots (homepage, product page, sidebar, blog, etc.). Paste HTML/JS ad codes or image banners.

### Schema / SEO
- Site-wide title template, meta description
- Open Graph defaults
- JSON-LD organization / website schema
- Canonical handling
- Sitemap is auto-generated at `/sitemap.xml`

### Colors / Theme
Dynamic CSS variables (primary, secondary, accent) — change brand colors without editing CSS files.

---

## 12. Newsletter

Admin → Newsletter

- Compose with shortcodes (e.g. `{{site_name}}`, `{{unsubscribe_url}}`, latest products, etc.)
- Send to registered users or a custom list
- Requires working SMTP

Schedule weekly digests via cron if configured in `routes/console.php`.

---

## 13. System Tools (Maintenance)

**Admin → System tools**

| Action | Effect |
|--------|--------|
| Run DB migrations | `php artisan migrate --force` |
| Clear all caches | config, route, view, event, optimize, application cache |

Use after every code update or `.env` change when you lack SSH.

Also available:
- Manual `php artisan storage:link` (or create symlink yourself if `exec` is disabled)

---

## 14. Storefront Features (Buyer Side)

- Homepage with hero, featured products, categories, blog teasers
- Search + filters (category, attributes, price, tags)
- Product detail: gallery, attributes as links, reviews, related items, license selection, add to cart
- Cart → Checkout (Stripe / Paystack / Monnify / crypto / free)
- Account area: purchases, downloads, wishlist, collections
- Author profiles
- Blog
- Static pages
- SEO: sitemap, robots.txt, canonicals, Open Graph, structured data

Mobile-first responsive design with drawer navigation.

---

## 15. Cron & Scheduled Tasks

Add **one** cron entry (every minute):

```cron
* * * * * cd /full/path/to/codebazaar && php artisan schedule:run >> /dev/null 2>&1
```

On Hostinger: hPanel → Advanced → Cron Jobs.

Tasks (password-reset cleanup, future newsletter, etc.) live in `routes/console.php`.

---

## 16. Troubleshooting

| Problem | Solution |
|---------|----------|
| 500 error | Check `storage/logs/laravel.log`; ensure `APP_KEY` exists; clear caches |
| 404 on all routes | Document root must be `public/` or enable rewrite; try `/index.php/...` |
| Admin locked | Complete License Activation |
| Unknown column / SQL error | System tools → Run migrations |
| Images / downloads 404 | Run storage:link or check Storage settings + file permissions |
| TinyMCE warning | Hard-refresh; code uses free GPL build from jsDelivr |
| Email not sending | Test SMTP; check spam; verify credentials |
| License domain mismatch | Re-activate on the new domain or contact author |

---

## 17. Packaging for CodeCanyon / Sales

See **CODECANYON_PACKAGING.md** for the full step-by-step build process.

**Version is already set to 1.0.0.**

For your own unrestricted copy use the no-license ZIP (middleware disabled + `DISABLE_PRODUCT_LICENSE=true`).

Never include the author master passphrase in buyer documentation.

---

**Support**  
CodeCanyon item support tab or the contact method listed in the item description. Always include purchase code, domain, and PHP version.
