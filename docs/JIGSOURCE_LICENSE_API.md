# JigSource.store ↔ CodeBazaar license API

CodeBazaar accepts license keys from **jigsource.store** in addition to Envato purchase codes.

Configure on each CodeBazaar install (`.env`):

```env
JIGSOURCE_VERIFY_URL=https://jigsource.store/api/license/verify
JIGSOURCE_API_KEY=shared_secret_optional
JIGSOURCE_ITEM_ID=your_product_id_on_jigsource
JIGSOURCE_LICENSE_SECRET=long_random_hmac_secret
```

---

## Option A — HTTP verify endpoint (recommended)

Implement on **jigsource.store**:

`POST /api/license/verify`

### Request JSON

```json
{
  "license_key": "BUYER-KEY-HERE",
  "domain": "customer-site.com",
  "item_id": "1108",
  "product": "codebazaar"
}
```

Headers (if you set `JIGSOURCE_API_KEY`):

- `Authorization: Bearer {key}` and/or `X-Api-Key: {key}`

### Success response (HTTP 200)

```json
{
  "valid": true,
  "success": true,
  "buyer": "buyer@email.com",
  "email": "buyer@email.com",
  "item_id": "1108",
  "message": "License active"
}
```

### Failure response

```json
{
  "valid": false,
  "success": false,
  "message": "License not found or already used on another domain"
}
```

### Suggested server logic on jigsource.store

1. Look up `license_key` in your orders / licenses table.
2. Confirm it belongs to the CodeBazaar product (`item_id`).
3. Confirm payment is completed / not refunded.
4. If the key already has a bound domain and it differs from `domain`, reject (one site per license).
5. Otherwise bind `domain` to the key on first activation and return `valid: true`.

---

## Option B — Signed keys (no API call required)

When a sale completes on jigsource.store, generate:

```
JS1.{base64url(json_payload)}.{base64url(hmac_sha256)}
```

Payload example:

```json
{
  "item_id": "1108",
  "email": "buyer@example.com",
  "exp": 1893456000
}
```

HMAC: `hash_hmac('sha256', payload_b64, JIGSOURCE_LICENSE_SECRET, true)` then base64url.

PHP helper (same algorithm as CodeBazaar):

```php
use App\Support\ProductActivation;

$key = ProductActivation::issueJigsourceSignedKey([
    'item_id' => '1108',
    'email' => $order->email,
    // 'domain' => 'optional-prebind.com',
    // 'exp' => time() + 86400 * 365,
], env('JIGSOURCE_LICENSE_SECRET'));
```

Show `$key` on the buyer’s order/download page and in the purchase email.

---

## Buyer instructions

1. Buy on [jigsource.store](https://jigsource.store).
2. Copy the license key from the order / downloads page.
3. On the installed site: **Admin → License activation** → paste key → Activate.

Envato buyers use the same screen with their CodeCanyon purchase code.
