# Installation guide — CodeBazaar Laravel

This guide covers **Git deploy**, **manual ZIP upload**, Hostinger notes, and **license activation**.

---

## Requirements

- PHP **8.2+** (8.3 recommended; match `composer.json`)
- Extensions: `bcmath`, `ctype`, `fileinfo`, `json`, `mbstring`, `openssl`, `pdo`, `tokenizer`, `xml`, `curl`
- MySQL 5.7+ / MariaDB 10.3+ (or PostgreSQL / SQLite for demos)
- Composer 2.x (skip on server if `vendor/` is already in the package)
- Writable: `storage/`, `bootstrap/cache/`, and project root (installer writes `.env`)

---

## A) Install with Git (SSH)

```bash
cd /home/USER/domains/YOURDOMAIN/public_html
git clone https://github.com/Montelent/codebazaar-laravel.git coderrr
cd coderrr

composer install --no-dev --optimize-autoloader
cp .env.example .env
php artisan key:generate
# Edit .env: DB_*, APP_URL, mail, optional ENVATO_*
php artisan migrate --force
php artisan storage:link
chmod -R 775 storage bootstrap/cache
```

Point the domain document root at `public/` when possible. Open `/install` if not yet installed, or log in at `/login`.

### Updates

```bash
git pull origin main
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan optimize:clear
```

---

## B) Manual install (ZIP / File Manager) — CodeCanyon path

1. Download the ZIP and extract under e.g. `public_html/coderrr/`.
2. Make `storage/`, `bootstrap/cache/`, and the **project root** writable (755/775).
3. **You do not need to create `.env` manually.** On the first request the script copies `.env.example` → `.env` and injects a temporary `APP_KEY` so the installer can boot. The wizard later replaces it with a unique key + your database settings.
4. If `vendor/` is missing, run `composer install` over SSH.
5. Open `https://your-domain.com/install` and finish the wizard.
6. Log in at `/login` → Admin.

After install, `/install` returns **404**. Reinstall only by deleting `storage/installed` via SSH/File Manager.

If you ever see “No application encryption key has been specified”, ensure the project root is writable, delete any broken `.env`, and refresh `/install`.

---

## C) License activation (commercial / CodeCanyon builds)

Until activated, Admin locks all features **except** Basic settings, Blog, System tools, and the Activation page.

1. **Admin → License activation** (`/admin/activation`)
2. Enter the **Envato purchase code** (or JigSource license key)
3. Code is bound to the **current domain**

### Live Envato API (author)

```env
ENVATO_PERSONAL_TOKEN=your_token
ENVATO_ITEM_ID=your_item_id
```

### Unrestricted author build

```env
DISABLE_PRODUCT_LICENSE=true
```

---

## Cron

```cron
* * * * * cd /full/path/to/coderrr && php artisan schedule:run >> /dev/null 2>&1
```

---

## Maintenance without SSH

Admin → **System tools**: migrations + clear caches.

---

## CodeCanyon buyers

See **Installations.html** (buyer-focused HTML guide).

---

## Download ZIP

https://github.com/Montelent/codebazaar-laravel/archive/refs/heads/main.zip
