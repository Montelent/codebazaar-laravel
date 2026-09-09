# How to Compile / Package CodeBazaar for CodeCanyon Upload

**Version:** 1.0.0

Follow these steps exactly to produce a clean, buyer-ready ZIP that meets CodeCanyon requirements.

---

## 1. Prepare a clean working copy

```bash
# Clone or pull latest
git clone https://github.com/Montelent/codebazaar-laravel.git codebazaar-release
cd codebazaar-release
git checkout main
git pull
```

Or work from your existing local clone and make sure it is clean.

---

## 2. Install production dependencies

```bash
composer config audit.block-insecure false
composer install --no-dev --optimize-autoloader --no-interaction
```

This produces a complete `vendor/` folder (required for shared-hosting buyers who cannot run Composer).

---

## 3. Clean sensitive / development files

```bash
rm -rf .git
rm -f .env          # never ship your real secrets
rm -f .env.local .env.testing
rm -rf storage/logs/*
rm -rf storage/framework/cache/*
rm -rf storage/framework/sessions/*
rm -rf storage/framework/views/*
rm -rf bootstrap/cache/*.php
rm -f storage/installed   # must not be present — buyers need the installer
```

Do **not** include:
- Your real `.env`
- Author master passphrase in any buyer-facing file
- Personal API keys / tokens
- `node_modules`

**Important – APP_KEY / first boot**

The release ZIP must **not** contain a real `.env`.  
`public/index.php` automatically copies `.env.example` → `.env` and injects a temporary `APP_KEY` on the first request if the site is not yet installed. The web installer later overwrites `.env` with a unique key + the buyer’s database settings. This prevents the “No application encryption key has been specified” 500 error.

---

## 4. Verify required documentation is present

These files must be in the root of the ZIP:

| File | Purpose |
|------|---------|
| `Installations.html` | Buyer-friendly HTML install guide (CodeCanyon loves this) |
| `INSTALL.md` | Technical install |
| `USER_GUIDE.md` / `Documentation.md` | Full usage documentation |
| `CHANGELOG.md` | Version history |
| `README.md` | Overview |
| `VERSION` | Plain text version number |
| `.env.example` | Template (used by auto-bootstrap) |

---

## 5. Create the release ZIP

```bash
# From inside the project root (after composer install + cleanup)
zip -r ../codebazaar-laravel-v1.0.0.zip . \
  -x "*.git*" \
  -x "*node_modules*" \
  -x "*.env" \
  -x "storage/logs/*" \
  -x "storage/framework/cache/*" \
  -x "storage/framework/sessions/*" \
  -x "storage/framework/views/*" \
  -x "bootstrap/cache/*.php" \
  -x "storage/installed" \
  -x "tests/*" \
  -x "*.DS_Store" \
  -x "dist/*"
```

Recommended final name: `codebazaar-laravel-v1.0.0.zip`

---

## 6. Test the ZIP before uploading (mandatory)

1. Upload the ZIP to a clean shared-hosting account (or local folder).
2. Extract it.
3. Point the domain document root at `public/` (or use the root index + .htaccess).
4. Open `https://your-test-domain.com/install` — it must load without a 500.
5. Complete the wizard, log in, activate license, create a product.

If you still see “No application encryption key…”, the auto-bootstrap in `public/index.php` did not run (permissions on project root, or `storage/installed` was accidentally included).

---

## 7. Optional: Author-only unrestricted build

For your own sites (no license lock):

```env
DISABLE_PRODUCT_LICENSE=true
```

Or use the pre-built no-license ZIP with middleware disabled.

---

## 8. CodeCanyon upload checklist

- [ ] Version matches `composer.json`, `VERSION`, `CHANGELOG.md`
- [ ] `Installations.html` opens cleanly
- [ ] No real `.env` or secrets inside the ZIP
- [ ] `vendor/` is present
- [ ] `storage/installed` is **absent**
- [ ] First visit to `/install` works without 500 (APP_KEY auto-created)
- [ ] Screenshots ready (homepage, product page, admin, mobile)
- [ ] Demo credentials prepared
- [ ] Support policy ready

---

## 9. After approval — updates

1. Bump version in `composer.json` + `VERSION`
2. Update `CHANGELOG.md`
3. Re-run packaging steps
4. Upload as a new version on CodeCanyon

---

## Quick one-liner (after composer install + cleanup)

```bash
zip -r ../codebazaar-laravel-v1.0.0.zip . -x "*.git*" -x "*.env" -x "storage/logs/*" -x "storage/framework/*/*" -x "bootstrap/cache/*.php" -x "storage/installed" -x "tests/*"
```
