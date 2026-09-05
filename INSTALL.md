# Installation guide — CodeBazaar Laravel

## Recommended: merge into a fresh Laravel 11 app

This repository contains the **CodeBazaar application layer** (models, controllers, views, migrations, routes). For a complete framework skeleton (all default config files, `vendor` via Composer), use:

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
cp /tmp/codebazaar-laravel/bootstrap/app.php bootstrap/app.php
cp /tmp/codebazaar-laravel/.env.example .env.example

# 4) Add Stripe package
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
