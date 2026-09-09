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

Remove or ensure these are **never** in the sales ZIP:

```bash
rm -rf .git
rm -f .env
rm -f .env.local .env.testing
rm -rf storage/logs/*
rm -rf storage/framework/cache/*
rm -rf storage/framework/sessions/*
rm -rf storage/framework/views/*
rm -rf bootstrap/cache/*.php
# Keep .gitignore, .env.example, .env.install
```

Do **not** include:
- Your real `.env`
- Author master passphrase in any buyer-facing file
- Development tools, tests (optional — many authors keep a minimal tests folder)
- `node_modules` (none in this project)
- Any personal API keys

---

## 4. Verify required documentation is present

These files must be in the root of the ZIP:

| File | Purpose |
|------|---------|
| `Installations.html` | Buyer-friendly HTML install guide (CodeCanyon loves this) |
| `INSTALL.md` | Technical install |
| `Documentation.md` or `USER_GUIDE.md` | Full usage documentation |
| `CHANGELOG.md` | Version history |
| `README.md` | Overview |
| `VERSION` | Plain text version number |
| `LICENSE` or license note (MIT or proprietary as you choose for commercial) |

---

## 5. Create the release ZIP

```bash
# From inside the project root
zip -r ../codebazaar-laravel-v1.0.0.zip . \
  -x "*.git*" \
  -x "*node_modules*" \
  -x "*.env" \
  -x "storage/logs/*" \
  -x "storage/framework/cache/*" \
  -x "storage/framework/sessions/*" \
  -x "storage/framework/views/*" \
  -x "bootstrap/cache/*.php" \
  -x "tests/*" \
  -x "*.DS_Store" \
  -x "dist/*"
```

Recommended final name for CodeCanyon:
`codebazaar-laravel-v1.0.0.zip`

---

## 6. Optional: Author-only unrestricted build

For your own sites (no license lock):

```bash
# Set in .env or hard-code for private ZIP
# DISABLE_PRODUCT_LICENSE=true
# And/or disable the EnsureProductActivated middleware
```

A pre-built no-license ZIP already exists in previous artifacts (`codebazaar-no-license.zip`).

---

## 7. CodeCanyon upload checklist

Before uploading:

- [ ] Version number matches `composer.json`, `VERSION`, `CHANGELOG.md`
- [ ] `Installations.html` opens cleanly in a browser
- [ ] No `.env` or secret keys inside the ZIP
- [ ] `vendor/` is present (or clear note that Composer is required)
- [ ] Screenshots of homepage, product page, admin, mobile
- [ ] Demo credentials prepared (if you host a live demo)
- [ ] Item description, tags, and support policy ready
- [ ] License type chosen (Regular / Extended) and price set
- [ ] Test the ZIP on a clean shared-hosting account (upload → /install → activate → create product)

---

## 8. After approval — updates

For future versions:

1. Bump version in `composer.json` and `VERSION`
2. Update `CHANGELOG.md`
3. Re-run the packaging steps
4. Upload as a new version on CodeCanyon (buyers receive free updates)

---

## Quick one-liner (after composer install)

```bash
zip -r ../codebazaar-laravel-v1.0.0.zip . -x "*.git*" -x "*.env" -x "storage/logs/*" -x "storage/framework/*/*" -x "bootstrap/cache/*.php" -x "tests/*"
```

That’s it. Upload the resulting ZIP to CodeCanyon.
