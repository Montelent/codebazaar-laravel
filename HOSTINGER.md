# Hostinger / shared hosting

## Recommended document root

Point the domain (or subdomain) **document root** to the project `public` folder:

```text
/home/USER/domains/yoursite.com/public_html/coderrr/public
```

or keep the app in `public_html` and set document root to `public_html/public`.

Then open `https://yoursite.com/install` (no `/index.php/` in the path).

## Alternative (everything in public_html root)

If document root **must** stay `public_html`:

- Keep root `index.php` + root `.htaccess` (included in the package).
- Delete `storage/installed` if reinstalling.

## Permissions

```bash
chmod -R 775 storage bootstrap/cache
```

Or use hPanel → **Fix File Ownership**.

## Cron jobs (Laravel scheduler)

Laravel only needs **one** cron job. In **hPanel → Advanced → Cron Jobs**:

| Field | Value |
|-------|--------|
| Common settings | Every Minute (`* * * * *`) |
| Command | see below |

**Command** (adjust path and PHP binary):

```bash
cd /home/u925754286/domains/simplifynaija.com/public_html/coderrr && /usr/bin/php artisan schedule:run >> /dev/null 2>&1
```

Find PHP path with `which php` in SSH, or Hostinger’s PHP selector path (often `/usr/bin/php` or `/opt/alt/php84/usr/bin/php`).

Scheduled tasks are defined in `routes/console.php` (e.g. daily `auth:clear-resets`).

## System tools (no SSH)

After login as admin:

1. Open **Admin → System tools** (sidebar → Maintenance).
2. **Run DB migrations** — applies pending migrations.
3. **Clear all caches** — runs `optimize:clear`, config/route/view/event/cache clear.

Use **Clear all caches** after `git pull` or editing `.env` / Blade files if the site looks outdated.

## /install returns 404

| Cause | Fix |
|-------|-----|
| File `storage/installed` exists | Delete it for a fresh install |
| Document root wrong | Point to `public` folder |
| `.htaccess` missing | Copy from package into the document root folder |
| Rewrite blocked | Hostinger support: enable rewrite for domain |

## Reinstall cleanly

1. Delete `storage/installed`
2. (Optional) drop database tables or create a new empty database
3. Open `/install` again

## Test rewrite

Open `/rewrite-test.php` then a fake path like `/hello-test-123`. If the fake path shows a Hostinger 404 page (not Laravel), rewrite is not active for that document root.
