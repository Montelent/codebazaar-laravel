# CodeBazaar Laravel — Digital Marketplace

Full **Laravel (PHP)** port of the CodeBazaar CodeCanyon-style marketplace: storefront, product pages, cart, checkout (Stripe + free), buyer account, and admin panel.

**Repository:** https://github.com/Montelent/codebazaar-laravel  

**Original Next.js version:** https://github.com/Montelent/codebazaar-marketplace

---

## Requirements

| Requirement | Version |
|-------------|---------|
| PHP | **8.2+** with extensions: `bcmath`, `ctype`, `curl`, `dom`, `fileinfo`, `json`, `mbstring`, `openssl`, `pdo`, `tokenizer`, `xml` |
| Composer | 2.x |
| Database | **MySQL 8+** or **PostgreSQL 14+** (or SQLite for local demo) |
| Node.js (optional) | 18+ only if you switch from CDN Tailwind to Vite assets |

---

## Quick install (5–10 minutes)

```bash
# 1. Get the code
git clone https://github.com/Montelent/codebazaar-laravel.git
cd codebazaar-laravel

# 2. Install PHP dependencies
composer install

# 3. Environment
cp .env.example .env
php artisan key:generate

# 4. Configure database in .env (MySQL example)
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=codebazaar
# DB_USERNAME=root
# DB_PASSWORD=secret

# 5. Create empty database, then migrate & seed
php artisan migrate --seed

# 6. Storage link (for future uploads)
php artisan storage:link

# 7. Run
php artisan serve
```

Open **http://127.0.0.1:8000**

| Area | URL |
|------|-----|
| Storefront | `/` |
| Admin | `/admin` |
| Login | `/login` |

**Default admin** (from `.env`):

- Email: `admin@codebazaar.com`
- Password: `ChangeMeNow123!`

Change these in `.env` **before** seeding, or update the user in the database after.

---

## Download as ZIP

### Source files

1. https://github.com/Montelent/codebazaar-laravel  
2. **Code → Download ZIP**  

Or:

```bash
curl -L https://github.com/Montelent/codebazaar-laravel/archive/refs/heads/main.zip -o codebazaar-laravel.zip
unzip codebazaar-laravel.zip
cd codebazaar-laravel-main
```

### SQL / database

After install, export your data:

```bash
# MySQL
mysqldump -u root -p codebazaar > codebazaar-backup.sql

# PostgreSQL
pg_dump -U postgres codebazaar > codebazaar-backup.sql
```

Schema is created by Laravel migrations (`php artisan migrate`). There is no separate SQL file required for a fresh install.

---

## Environment variables

| Variable | Purpose |
|----------|---------|
| `APP_URL` | Public site URL |
| `DB_*` | Database connection |
| `ADMIN_EMAIL` / `ADMIN_PASSWORD` | Seeded admin account |
| `STRIPE_KEY` / `STRIPE_SECRET` | Paid checkout (optional for free-only) |

---

## Features included

- Homepage with hero (editable), categories, latest products  
- Search & category listings  
- Product detail (licenses Regular/Extended, free products, demo link)  
- Session cart + checkout  
- Stripe Checkout for paid orders; free orders complete immediately  
- Buyer account: purchases & downloads (main file URL)  
- Admin: dashboard stats, product CRUD, site/hero settings  
- Auth: register, login, logout  

---

## Production notes

```bash
composer install --optimize-autoloader --no-dev
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Point the web server document root to `/public`.  
Set `APP_ENV=production` and `APP_DEBUG=false`.

See **Documentation.md** for detailed hosting, Stripe webhooks, and customization.

---

## License

MIT — CodeBazaar branding is original. Do not use third-party trademarks or scraped marketplace assets.
