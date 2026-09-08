# Installation guide — CodeBazaar Laravel

## Quick install (web installer)

1. Upload / extract the project on your host (Hostinger: see **HOSTINGER.md**).
2. Point the domain **document root** at the project `public/` folder when possible.
3. Ensure `storage/` and `bootstrap/cache/` are writable (755/775).
4. Open `https://your-domain.com/install` and complete:
   - Requirements check
   - Database credentials (creates `.env` automatically)
   - Admin account
5. Log in at `/login` → Admin dashboard.

After install, use **Admin → System tools** to run migrations or clear caches without SSH.

### Security after install

Once the site is installed, **`/install` returns a normal 404**. There is no public message and no hint how to reinstall. That is intentional so scanners and attackers cannot confirm an installer exists or learn how to reset it.

### Reinstall (private — SSH / File Manager only)

Only do this if you intentionally want a **fresh** web install. This does **not** wipe the database by itself.

1. Via SSH or File Manager, delete the lock file:

   ```bash
   rm storage/installed
   ```

   (Path may be `storage/app/installed` on some layouts — delete the file named `installed` under `storage/`.)

2. Optionally back up and remove or empty `.env` if you want the wizard to recreate it.
3. Open `/install` again and complete the wizard.
4. Prefer restoring from a DB backup if you need a clean schema; or drop tables carefully before re-running migrations.

Never publish these steps on the live site or in customer-facing UI.

## Recommended: merge into a fresh Laravel 11 app

This repository contains the **CodeBazaar application layer** (models, controllers, views, migrations, routes). For a complete framework skeleton:

```bash
# 1) Create a stock Laravel 11 project
composer create-project laravel/laravel codebazaar-app
cd codebazaar-app

# 2) Clone this marketplace code beside it
git clone https://github.com/Montelent/codebazaar-laravel.git /tmp/codebazaar-laravel

# 3) Copy application files over the stock project
cp -R /tmp/codebazaar-laravel/app/* app/
cp -R /tmp/codebazaar-laravel/resources/views resources/
cp -R /tmp/codebazaar-laravel/database/migrations database/migrations/
cp /tmp/codebazaar-laravel/database/seeders/DatabaseSeeder.php database/seeders/
cp /tmp/codebazaar-laravel/routes/web.php routes/web.php
cp /tmp/codebazaar-laravel/routes/console.php routes/console.php
cp /tmp/codebazaar-laravel/bootstrap/app.php bootstrap/app.php
cp /tmp/codebazaar-laravel/.env.example .env.example

# 4) Add Stripe package (optional payments)
composer require stripe/stripe-php

# 5) Env + database
cp .env.example .env
php artisan key:generate
# Edit .env: DB_*, ADMIN_EMAIL, ADMIN_PASSWORD, STRIPE_*

php artisan migrate --seed
php artisan serve
```

Open http://127.0.0.1:8000  
Admin: `/login` with `ADMIN_EMAIL` / `ADMIN_PASSWORD` from `.env`.

## Alternative: install this repo directly

```bash
git clone https://github.com/Montelent/codebazaar-laravel.git
cd codebazaar-laravel
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

If Composer reports missing skeleton files, use the **Recommended** method above.

## Cron (required for schedules)

Add **one** system cron entry (every minute):

```cron
* * * * * cd /full/path/to/codebazaar && php artisan schedule:run >> /dev/null 2>&1
```

On Hostinger: **hPanel → Advanced → Cron Jobs** → Common settings: Every Minute → paste the command with your real path (and PHP binary if needed).

## Maintenance without SSH

In the admin panel open **System tools**:

- **Run DB migrations** — applies pending migrations
- **Clear all caches** — config, routes, views, events, application cache

## SQLite quick demo

```env
DB_CONNECTION=sqlite
```

```bash
touch database/database.sqlite
php artisan migrate --seed
php artisan serve
```

## Download ZIP

https://github.com/Montelent/codebazaar-laravel/archive/refs/heads/main.zip

## Database backup (SQL)

```bash
# MySQL
mysqldump -u root -p codebazaar > codebazaar.sql

# PostgreSQL
pg_dump codebazaar > codebazaar.sql
```

Schema is applied via `php artisan migrate` (no separate SQL file required for install).
