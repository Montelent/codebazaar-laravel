# CodeBazaar Next.js → Laravel feature parity

## Done in Laravel repo

| Area | Status |
|------|--------|
| Web installer + lock | Done |
| Shared hosting `/index.php` URLs (`FORCE_INDEX_PHP`) | Done |
| Auth login/register/logout | Done |
| Storefront home, search, category | Done |
| Product detail (preview, gallery, features, attributes, licenses, wishlist, related) | Done |
| Cart + checkout (free / Stripe / manual pending) | Done |
| Account purchases & downloads | Done |
| Wishlist | Done |
| Author public profile | Done |
| Admin dashboard stats | Done |
| Admin products full form (prices, free, media URLs, tags, attributes JSON, TinyMCE, featured, status) | Done |
| Admin categories + attribute schema | Done |
| Admin users | Done |
| Admin blog + public blog | Done |
| Admin CMS pages + public pages | Done |
| Admin licenses + public license page | Done |
| Admin orders | Done |
| Settings: hero, announcement, footer, colors, SEO | Done |

## Still deeper parity vs Next.js (iterate next)

- Split settings screens (payments Stripe/PayPal/manual, navigation builder, header/footer menus UI, schema SEO JSON-LD builder)
- Attributes manager UI (presets per category like Next `/admin/attributes`)
- Tags manager page
- Newsletter send UI + email verification flow
- Reviews on product page
- Collections / follow authors
- Media library uploads (local disk) in addition to external URLs
- Pixel-perfect CodeCanyon CSS for every breakpoint

The Next.js app remains the reference UI; Laravel now carries the same **product, commerce, CMS, and admin information architecture** with working shared-hosting URLs.
