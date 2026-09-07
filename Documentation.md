# CodeBazaar Laravel — Documentation

## 1. Architecture

| Layer | Path |
|-------|------|
| Routes | `routes/web.php` |
| Controllers | `app/Http/Controllers` (+ `Admin/`) |
| Models | `app/Models` |
| Views | `resources/views` (Blade + Tailwind CDN) |
| Migrations | `database/migrations` |
| Seeders | `database/seeders/DatabaseSeeder.php` |
| Schedule | `routes/console.php` |
| Admin gate | `app/Http/Middleware/EnsureAdmin.php` (alias `admin`) |

Data flow: **items**, **categories**, **orders**, **order_items**, **site_settings**, **users** (roles `admin` / `buyer`).

## 2. Installation (detailed)

See **INSTALL.md** and **HOSTINGER.md** for Hostinger-specific steps.

### 2.1 System packages (Ubuntu example)

```bash
sudo apt update
sudo apt install php8.2 php8.2-cli php8.2-mysql php8.2-pgsql php8.2-mbstring php8.2-xml php8.2-curl php8.2-zip unzip
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

### 2.2 Database

```sql
CREATE DATABASE codebazaar CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 2.3 Composer & app key

```bash
composer install
cp .env.example .env
php artisan key:generate
```

### 2.4 Migrate & seed

```bash
php artisan migrate --seed
```

Creates tables, admin user (`ADMIN_EMAIL` / `ADMIN_PASSWORD`), sample categories/products, homepage settings.

### 2.5 Web installer

On shared hosting you can use `/install` instead of SSH. It writes `.env`, runs migrations, and creates the admin user.

## 3. Admin panel

1. Visit `/login` with admin credentials → `/admin`.
2. **Products** — title, slug, HTML description (TinyMCE free build), prices, category, media, featured flag.
3. **Categories / Attributes / Tags / Media / Licenses**.
4. **Blog / Pages / Newsletter**.
5. **Orders / Users**.
6. **Settings** — general, payments, navigation, header/footer, schema SEO.
7. **System tools** — run migrations & clear caches from the browser.

### Mobile admin

Use the hamburger (☰) to open the full sidebar menu on phones.

## 4. System tools & caches

**Admin → System tools**

| Action | What it runs |
|--------|----------------|
| Run DB migrations | `php artisan migrate --force` |
| Clear all caches | `optimize:clear`, `config:clear`, `route:clear`, `view:clear`, `event:clear`, `cache:clear` |

Prefer this after deploying code or changing `.env` when you do not have SSH.

## 5. Cron / scheduler

Laravel expects **one** cron entry every minute:

```cron
* * * * * cd /path/to/app && php artisan schedule:run >> /dev/null 2>&1
```

Tasks live in `routes/console.php` (e.g. daily password-reset token cleanup). Add more with `Schedule::...` as needed.

**Hostinger:** hPanel → Advanced → Cron Jobs → Every Minute → use full path to the app and to `php`.

## 6. Checkout & Stripe

- Cart total **≤ 0** → order marked `paid`, cart cleared.
- Cart total **> 0** → requires `STRIPE_SECRET`; Stripe Checkout Session.
- Success URL marks order `paid` via `checkout.success`.

For production webhooks, verify `STRIPE_WEBHOOK_SECRET` on `checkout.session.completed`.

## 7. Downloads

Buyers with a **paid** order use `/account/downloads`. Set **Main download file URL** on each product.

## 8. Customization

- **Theme:** `resources/views/layouts/app.blade.php` (Tailwind CDN + custom CSS).
- **Home:** `HomeController` + `SiteSetting` keys `homepage.hero` / `hero`, `site`.
- **Rich text:** free TinyMCE from jsDelivr (`license_key: gpl`) — no API key.
- **New admin modules:** controller under `Admin/`, routes in the `auth`+`admin` group, Blade under `resources/views/admin/`.

## 9. Troubleshooting

| Issue | Fix |
|-------|-----|
| 500 after install | `php artisan key:generate`; check `storage/logs/laravel.log`; or Admin → Clear caches |
| SQLSTATE / unknown column | Admin → **Run DB migrations** or `php artisan migrate --force` |
| 405 on migrate | Use **Admin → System tools** (POST forms). Do not open `/admin/migrate` as GET |
| 403 on /admin | User `role` must be `admin` |
| TinyMCE API key warning | Use current code (jsDelivr GPL build); hard-refresh admin |
| Stale CSS/views | Admin → Clear all caches |
| Stripe error | Set `STRIPE_SECRET`; free products work without Stripe |

## 10. Support

- GitHub Issues: https://github.com/Montelent/codebazaar-laravel/issues
