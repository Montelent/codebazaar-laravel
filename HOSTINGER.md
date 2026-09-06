# Hostinger / shared hosting (pretty URLs like other Laravel sites)

Other Laravel sites on Hostinger usually point the **domain document root to the `public` folder**. That is the normal setup and does **not** need `/index.php/` in the URL.

## Recommended setup (same as typical Laravel)

1. Upload and extract the project so you have:

```text
/home/USER/domains/yoursite.com/
  codebazaar/          ← full app (or directly in public_html parent)
    app/
    bootstrap/
    public/            ← only this folder is web-accessible
      index.php
      .htaccess
    vendor/
    storage/
    ...
```

2. In **hPanel → Domains → your domain → Document root** set it to:

```text
.../codebazaar/public
```

or if the app lives in `public_html`:

```text
public_html/public
```

3. Open `https://yoursite.com/install` — no `index.php` in the path.

## Alternative (everything in public_html root)

If document root **must** stay `public_html`:

- Keep root `index.php` + root `.htaccess` (included in the package).
- Delete `storage/installed` if reinstalling.

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
