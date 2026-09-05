# CodeBazaar Laravel — Digital Marketplace

CodeCanyon-style digital marketplace in **Laravel (PHP)** with a **web installer** for shared hosting.

**Repo:** https://github.com/Montelent/codebazaar-laravel

---

## Install like a commercial PHP script (shared hosting)

### Release ZIP (includes Composer `vendor/`)

GitHub does **not** store `vendor/` (too large). Build a distributable package once on any PC with PHP:

```bash
git clone https://github.com/Montelent/codebazaar-laravel.git
cd codebazaar-laravel
composer install --no-dev --optimize-autoloader
bash scripts/build-release.sh
# → dist/codebazaar-laravel-release.zip
```

That ZIP is what buyers/users upload.

### Steps (no SSH)

1. Create a **MySQL** database in cPanel.
2. Upload & extract the **release ZIP**.
3. Document root → Laravel **`public/`** folder (writable: `storage/`, `bootstrap/cache/`, project root for `.env`).
4. Open:

```text
https://yourdomain.com/install
```

5. Wizard:
   - **Requirements** — PHP 8.2+, extensions, writable paths, `vendor/` present  
   - **Database** — site URL + DB details (tested before continue)  
   - **Admin** — create admin account  
   - **Finish** — writes `.env`, migrates tables, seeds categories, creates `storage/installed`

6. After success, **`/install` returns 404** (locked).

7. Login: `/login` → Admin: `/admin`

To re-install: delete `storage/installed` (and optionally `.env`).

---

## Developer install (SSH / local)

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

See **INSTALL.md** and **Documentation.md**.

---

## License

MIT
