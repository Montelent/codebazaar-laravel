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
| Admin gate | `app/Http/Middleware/EnsureAdmin.php` (alias `admin`) |

Data flow mirrors the Next.js CodeBazaar product model: **items**, **categories**, **orders**, **order_items**, **site_settings**, **users** (roles `admin` / `buyer`).

## 2. Installation (detailed)

### 2.1 System packages (Ubuntu example)

```bash
sudo apt update
sudo apt install php8.2 php8.2-cli php8.2-mysql php8.2-pgsql php8.2-mbstring php8.2-xml php8.2-curl php8.2-zip unzip
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

### 2.2 Database

**MySQL**

```sql
CREATE DATABASE codebazaar CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

**PostgreSQL**

```sql
CREATE DATABASE codebazaar;
```

**SQLite (fast local demo)**

```bash
# .env
DB_CONNECTION=sqlite
# touch database/database.sqlite

touch database/database.sqlite
php artisan migrate --seed
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

This creates tables and:

- Admin user from `ADMIN_EMAIL` / `ADMIN_PASSWORD`
- Sample categories
- Two sample products
- Default homepage hero settings

## 3. Admin panel

1. Visit `/login` with admin credentials.  
2. You are redirected to `/admin`.  
3. **Products** — create/edit title, slug, HTML description, free flag, regular/extended/sale prices, category, thumbnail, demo URL, **main download file URL**, features, screenshots, featured flag.  
4. **Settings** — site name, tagline, hero title/subtitle.  
5. **View on site** opens the public product page.

## 4. Checkout & Stripe

- Cart total **≤ 0** → order marked `paid`, cart cleared, redirect to downloads.  
- Cart total **> 0** → requires `STRIPE_SECRET`; creates Stripe Checkout Session and redirects.  
- Success URL marks order `paid` via `checkout.success`.

For production webhooks, add a route verifying `STRIPE_WEBHOOK_SECRET` and updating order status on `checkout.session.completed`.

## 5. Downloads

Buyers with a **paid** order can open `/account/downloads` and download via `main_file_url` on the product. Admins can always download.

Set the file URL in **Admin → Products → Main download file URL** (host ZIP on S3, R2, etc.).

## 6. Customization

- **Theme:** `resources/views/layouts/app.blade.php` (Tailwind CDN).  
- **Home content:** `HomeController` + `SiteSetting` keys `homepage.hero`, `site`.  
- **New admin modules:** add controller under `Admin/`, routes inside `middleware(['auth','admin'])` group, Blade under `resources/views/admin/`.

## 7. Shared hosting / cPanel

1. Upload project (or git pull).  
2. `composer install --no-dev` via SSH.  
3. Document root → `public/`.  
4. Ensure `storage/` and `bootstrap/cache/` are writable (`chmod -R 775 storage bootstrap/cache`).  

## 8. Troubleshooting

| Issue | Fix |
|-------|-----|
| 500 after install | `php artisan key:generate`; check `storage/logs/laravel.log` |
| SQLSTATE connection | Verify `.env` DB_* and that database exists |
| 403 on /admin | User `role` must be `admin` |
| Stripe error | Set `STRIPE_SECRET`; free products work without Stripe |
| Blank styles | CDN Tailwind requires network; or install Vite + Tailwind locally |

## 9. Relationship to Next.js CodeBazaar

| Concern | Next.js repo | This Laravel repo |
|---------|--------------|-------------------|
| Runtime | Node / Vercel | PHP / any host |
| DB access | `pg` + Supabase | Eloquent (MySQL/Postgres/SQLite) |
| Admin | App Router admin | Blade admin |
| Cart | Zustand client | Laravel session |

Feature parity is focused on core marketplace flows; extend Blade/admin modules as needed for blog CMS depth, multi-vendor, etc.

## 10. Support

- GitHub Issues: https://github.com/Montelent/codebazaar-laravel/issues
