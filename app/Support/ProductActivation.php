<?php

namespace App\Support;

use App\Models\SiteSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * Product activation for commercial installs (live verification for sales).
 *
 * Supported unlock methods (same input field — auto-detected):
 * 1) Author master passphrase (hash-only in source)
 * 2) Envato / CodeCanyon purchase code — live API (author/sale)
 * 3) JigSource.store purchase code — live API + optional signed JS1 keys
 *
 * UUID-style codes are tried against Envato first, then JigSource, so the
 * same field works for buyers from either marketplace.
 */
class ProductActivation
{
    public const SETTING_KEY = 'product_activation';

    /**
     * SHA-256 hex of: cbz-master-v1|{author_master_passphrase}
     */
    private const MASTER_KEY_HASH = 'b5f42eef7ade7f8256a03f6e3bd441d9950b54f290e653cb57b7455d635687dc';

    public static function status(): array
    {
        try {
            $data = SiteSetting::getValue(self::SETTING_KEY, []);
        } catch (\Throwable) {
            $data = [];
        }

        if (! is_array($data)) {
            $data = [];
        }

        if ((bool) config('services.envato.licensing_disabled', false)) {
            return [
                'active' => true,
                'locked' => false,
                'type' => 'disabled',
                'domain' => self::currentDomain(),
                'source' => 'DISABLE_PRODUCT_LICENSE',
            ];
        }

        $active = ! empty($data['active']);
        $type = $data['type'] ?? null;

        if ($active && $type === 'master') {
            return array_merge($data, ['active' => true, 'locked' => false]);
        }

        if ($active && in_array($type, ['envato', 'jigsource'], true)) {
            $bound = strtolower((string) ($data['domain'] ?? ''));
            $current = self::currentDomain();
            if ($bound !== '' && $bound !== $current && empty($data['allow_domain_mismatch'])) {
                return array_merge($data, [
                    'active' => false,
                    'locked' => true,
                    'reason' => 'This license is bound to another domain ('.$bound.').',
                ]);
            }

            return array_merge($data, ['active' => true, 'locked' => false]);
        }

        return [
            'active' => false,
            'locked' => true,
            'type' => null,
            'domain' => null,
            'activated_at' => null,
        ];
    }

    public static function isActive(): bool
    {
        return (bool) (self::status()['active'] ?? false);
    }

    public static function isLocked(): bool
    {
        return ! self::isActive();
    }

    public static function currentDomain(): string
    {
        $host = request()->getHost() ?: parse_url((string) config('app.url'), PHP_URL_HOST) ?: 'localhost';

        return strtolower(preg_replace('/^www\./', '', $host) ?: 'localhost');
    }

    /**
     * @return array{ok:bool,message:string,type?:string}
     */
    public static function activate(string $code): array
    {
        $code = trim($code);
        if ($code === '') {
            return ['ok' => false, 'message' => 'Please enter a purchase code or license key.'];
        }

        // 1) Author master (private)
        $attempt = hash('sha256', 'cbz-master-v1|'.$code);
        if (hash_equals(self::MASTER_KEY_HASH, $attempt)) {
            self::persist([
                'active' => true,
                'type' => 'master',
                'domain' => self::currentDomain(),
                'code_hint' => 'master',
                'buyer' => 'Author',
                'source' => 'master',
                'activated_at' => now()->toIso8601String(),
            ]);

            return ['ok' => true, 'message' => 'Author master key accepted. Product unlocked permanently on this install.', 'type' => 'master'];
        }

        // 2) Explicit JigSource signed keys (JS1....)
        if (self::looksLikeJigsourceKey($code)) {
            $js = self::activateJigsource($code);
            if ($js['ok'] || ($js['hard_fail'] ?? false)) {
                return $js;
            }
        }

        // 3) UUID-style codes: used by BOTH Envato and JigSource → try both
        if (self::looksLikeUuidPurchaseCode($code)) {
            $envato = self::activateEnvato($code);
            if ($envato['ok']) {
                return $envato;
            }

            // Envato rejected (invalid / wrong item) → try JigSource
            $js = self::activateJigsource($code);
            if ($js['ok']) {
                return $js;
            }

            // Prefer the more specific message
            $envMsg = (string) ($envato['message'] ?? '');
            $jsMsg = (string) ($js['message'] ?? '');

            return [
                'ok' => false,
                'message' => 'Purchase code not recognized by Envato or JigSource.'
                    .($envMsg !== '' ? ' Envato: '.$envMsg : '')
                    .($jsMsg !== '' ? ' JigSource: '.$jsMsg : ''),
            ];
        }

        // 4) Any other string → JigSource only
        $js = self::activateJigsource($code);
        if ($js['ok']) {
            return $js;
        }

        return [
            'ok' => false,
            'message' => $js['message'] ?? 'Invalid license. Use your JigSource or Envato purchase code.',
        ];
    }

    protected static function looksLikeUuidPurchaseCode(string $code): bool
    {
        return (bool) preg_match(
            '/^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/i',
            $code
        );
    }

    protected static function looksLikeJigsourceKey(string $code): bool
    {
        if (preg_match('/^JS1\.[A-Za-z0-9_-]+\.[A-Za-z0-9_-]+$/', $code)) {
            return true;
        }

        if (preg_match('/^(JS|JIG|JIGSOURCE)[-_]/i', $code)) {
            return true;
        }

        return false;
    }

    /**
     * JigSource.store live validation.
     * POST https://jigsource.store/api/purchases/validation
     * Success: { "status": "success", "data": { "purchase": { ... } } }
     * Error:   { "status": "error", "msg": "Invalid purchase code" }
     *
     * @return array{ok:bool,message:string,type?:string,hard_fail?:bool}
     */
    protected static function activateJigsource(string $code): array
    {
        $domain = self::currentDomain();

        // A) Offline signed keys (optional)
        if (preg_match('/^JS1\.([A-Za-z0-9_-]+)\.([A-Za-z0-9_-]+)$/', $code, $m)) {
            $payloadB64 = $m[1];
            $sig = $m[2];
            $secret = (string) config('services.jigsource.license_secret', '');

            if ($secret !== '') {
                $expected = self::base64UrlEncode(hash_hmac('sha256', $payloadB64, $secret, true));
                if (! hash_equals($expected, $sig)) {
                    return ['ok' => false, 'message' => 'Invalid JigSource license signature.', 'hard_fail' => true];
                }

                $json = self::base64UrlDecode($payloadB64);
                $payload = json_decode($json ?: 'null', true);
                if (! is_array($payload)) {
                    return ['ok' => false, 'message' => 'Invalid JigSource license payload.', 'hard_fail' => true];
                }

                $itemId = (string) config('services.jigsource.item_id', '');
                if ($itemId !== '' && ! empty($payload['item_id']) && (string) $payload['item_id'] !== $itemId) {
                    return ['ok' => false, 'message' => 'This JigSource license is for a different product.', 'hard_fail' => true];
                }

                if (! empty($payload['exp']) && time() > (int) $payload['exp']) {
                    return ['ok' => false, 'message' => 'This JigSource license has expired.', 'hard_fail' => true];
                }

                if (! empty($payload['domain']) && strtolower((string) $payload['domain']) !== $domain) {
                    return ['ok' => false, 'message' => 'This JigSource license is locked to '.$payload['domain'].'.', 'hard_fail' => true];
                }

                self::persist([
                    'active' => true,
                    'type' => 'jigsource',
                    'domain' => $domain,
                    'code_hint' => Str::mask($code, '*', 6, max(0, strlen($code) - 10)),
                    'code_hash' => hash('sha256', $code),
                    'buyer' => $payload['email'] ?? $payload['buyer'] ?? null,
                    'item_id' => $payload['item_id'] ?? null,
                    'source' => 'jigsource_signed',
                    'activated_at' => now()->toIso8601String(),
                    'verified_via' => 'jigsource_signed',
                ]);

                return [
                    'ok' => true,
                    'message' => 'JigSource license verified and bound to '.$domain.'.',
                    'type' => 'jigsource',
                ];
            }
        }

        // B) Live API
        $apiUrl = rtrim((string) config('services.jigsource.verify_url', 'https://jigsource.store/api/purchases/validation'), '/');
        $apiKey = trim((string) config('services.jigsource.api_key', ''));
        $itemId = trim((string) config('services.jigsource.item_id', ''));
        $product = (string) config('services.jigsource.product_slug', 'codebazaar');

        if ($apiUrl === '') {
            return ['ok' => false, 'message' => 'JigSource verification is not configured (missing verify URL).'];
        }

        $payload = [
            'purchase_code' => $code,
            'license_key' => $code,
            'code' => $code,
            'domain' => $domain,
            'product' => $product,
            'product_slug' => $product,
        ];
        if ($itemId !== '') {
            $payload['item_id'] = $itemId;
        }

        try {
            $request = Http::acceptJson()
                ->timeout(25)
                ->asJson()
                ->withHeaders([
                    'User-Agent' => 'CodeBazaar-License/1.0',
                ]);

            if ($apiKey !== '') {
                $request = $request->withHeaders([
                    'X-Api-Key' => $apiKey,
                    'Authorization' => 'Bearer '.$apiKey,
                ]);
                // Some gateways also accept the key in the body
                $payload['api_key'] = $apiKey;
            }

            $response = $request->post($apiUrl, $payload);
        } catch (\Throwable $e) {
            return ['ok' => false, 'message' => 'Could not reach JigSource license server: '.$e->getMessage()];
        }

        $body = $response->json();
        if (! is_array($body)) {
            $body = [];
        }

        $status = strtolower((string) (data_get($body, 'status') ?? ''));

        // Official error shape: { "status": "error", "msg": "Invalid purchase code" }
        if ($status === 'error' || $response->status() === 404) {
            $msg = data_get($body, 'msg')
                ?: data_get($body, 'message')
                ?: data_get($body, 'error')
                ?: 'Invalid purchase code';

            return ['ok' => false, 'message' => is_string($msg) ? $msg : 'Invalid purchase code'];
        }

        // Official success shape: { "status": "success", "data": { "purchase": { ... } } }
        $isSuccess = $status === 'success'
            || (bool) data_get($body, 'valid')
            || (bool) data_get($body, 'success');

        if (! $isSuccess && ! $response->successful()) {
            $msg = data_get($body, 'msg')
                ?: data_get($body, 'message')
                ?: ('JigSource API error (HTTP '.$response->status().')');

            return ['ok' => false, 'message' => is_string($msg) ? $msg : 'JigSource rejected this purchase code.'];
        }

        if (! $isSuccess) {
            return [
                'ok' => false,
                'message' => (string) (
                    data_get($body, 'msg')
                    ?: data_get($body, 'message')
                    ?: 'JigSource rejected this purchase code.'
                ),
            ];
        }

        $purchase = data_get($body, 'data.purchase') ?: data_get($body, 'purchase') ?: [];
        $item = data_get($purchase, 'item') ?: [];

        $saleItemId = (string) (data_get($item, 'id') ?: data_get($purchase, 'item_id') ?: '');
        $saleItemName = (string) (data_get($item, 'name') ?: '');

        // Optional product binding
        if ($itemId !== '' && $saleItemId !== '' && $saleItemId !== $itemId) {
            return [
                'ok' => false,
                'message' => 'This JigSource code belongs to a different item'
                    .($saleItemName !== '' ? ' ('.$saleItemName.')' : '').'.',
            ];
        }

        self::persist([
            'active' => true,
            'type' => 'jigsource',
            'domain' => $domain,
            'code_hint' => Str::mask($code, '*', 4, max(0, strlen($code) - 8)),
            'code_hash' => hash('sha256', strtolower($code)),
            'buyer' => data_get($purchase, 'buyer')
                ?: data_get($purchase, 'email')
                ?: data_get($body, 'data.buyer'),
            'item_id' => $saleItemId ?: $itemId,
            'item_name' => $saleItemName ?: null,
            'license' => data_get($purchase, 'license_type'),
            'supported_until' => data_get($purchase, 'supported_until'),
            'purchased_at' => data_get($purchase, 'purchased_at'),
            'amount' => data_get($purchase, 'price'),
            'currency' => data_get($purchase, 'currency'),
            'source' => 'jigsource_api',
            'activated_at' => now()->toIso8601String(),
            'verified_via' => 'jigsource_api',
        ]);

        $label = $saleItemName !== '' ? $saleItemName : 'JigSource purchase';

        return [
            'ok' => true,
            'message' => $label.' verified with JigSource and bound to '.$domain.'.',
            'type' => 'jigsource',
        ];
    }

    /**
     * Live Envato verification via GET /v3/market/author/sale?code=
     * Docs: https://build.envato.com/api/#market_0_getAuthorSale
     *
     * @return array{ok:bool,message:string,type?:string}
     */
    protected static function activateEnvato(string $code): array
    {
        $token = trim((string) config('services.envato.token', ''));
        $itemId = trim((string) config('services.envato.item_id', ''));
        $requireLive = (bool) config('services.envato.require_live', true);

        if ($token === '') {
            if ($requireLive) {
                return [
                    'ok' => false,
                    'message' => 'Live Envato verification is required but no personal token is configured (ENVATO_PERSONAL_TOKEN).',
                ];
            }

            self::persist([
                'active' => true,
                'type' => 'envato',
                'domain' => self::currentDomain(),
                'code_hint' => Str::mask($code, '*', 4, max(0, strlen($code) - 8)),
                'code_hash' => hash('sha256', strtolower($code)),
                'buyer' => null,
                'item_id' => $itemId ?: null,
                'source' => 'envato_format',
                'activated_at' => now()->toIso8601String(),
                'verified_via' => 'format_only',
            ]);

            return [
                'ok' => true,
                'message' => 'Envato-style code saved (format-only / testing).',
                'type' => 'envato',
            ];
        }

        $url = 'https://api.envato.com/v3/market/author/sale';

        try {
            $response = Http::withToken($token)
                ->withHeaders(['User-Agent' => 'CodeBazaar-License/1.0'])
                ->acceptJson()
                ->timeout(25)
                ->get($url, ['code' => $code]);
        } catch (\Throwable $e) {
            return ['ok' => false, 'message' => 'Could not reach Envato API: '.$e->getMessage()];
        }

        $status = $response->status();

        if ($status === 404) {
            return ['ok' => false, 'message' => 'Envato purchase code not found or invalid.'];
        }

        if ($status === 401 || $status === 403) {
            return [
                'ok' => false,
                'message' => 'Envato API authentication failed. Check personal token scopes (View your sales).',
            ];
        }

        if ($status === 429) {
            $retry = $response->header('Retry-After');

            return [
                'ok' => false,
                'message' => 'Envato API rate limit reached'.($retry ? ' — retry after '.$retry.'s' : '').'.',
            ];
        }

        if (! $response->successful()) {
            return ['ok' => false, 'message' => 'Envato API error (HTTP '.$status.').'];
        }

        $sale = $response->json();
        if (! is_array($sale) || empty($sale)) {
            return ['ok' => false, 'message' => 'Envato returned an empty sale response.'];
        }

        $saleItemId = (string) data_get($sale, 'item.id', '');
        $saleItemName = (string) data_get($sale, 'item.name', '');

        if ($itemId !== '' && $saleItemId !== '' && $saleItemId !== $itemId) {
            return [
                'ok' => false,
                'message' => 'This Envato code belongs to a different item'
                    .($saleItemName !== '' ? ' ('.$saleItemName.')' : '').'.',
            ];
        }

        $buyer = data_get($sale, 'buyer')
            ?: data_get($sale, 'buyer_username')
            ?: data_get($sale, 'buyer_id');

        self::persist([
            'active' => true,
            'type' => 'envato',
            'domain' => self::currentDomain(),
            'code_hint' => Str::mask($code, '*', 4, max(0, strlen($code) - 8)),
            'code_hash' => hash('sha256', strtolower($code)),
            'buyer' => $buyer,
            'item_id' => $saleItemId ?: $itemId,
            'item_name' => $saleItemName ?: null,
            'license' => data_get($sale, 'license'),
            'sold_at' => data_get($sale, 'sold_at'),
            'supported_until' => data_get($sale, 'supported_until'),
            'amount' => data_get($sale, 'amount'),
            'source' => 'envato_api',
            'activated_at' => now()->toIso8601String(),
            'verified_via' => 'envato_api',
        ]);

        $label = $saleItemName !== '' ? $saleItemName : 'Envato purchase';

        return [
            'ok' => true,
            'message' => $label.' verified with Envato and bound to '.self::currentDomain().'.',
            'type' => 'envato',
        ];
    }

    public static function deactivate(): void
    {
        self::persist([
            'active' => false,
            'type' => null,
            'domain' => null,
            'activated_at' => null,
        ]);
    }

    protected static function persist(array $data): void
    {
        SiteSetting::setValue(self::SETTING_KEY, $data, 'system');

        try {
            $path = storage_path('app/product_activation.json');
            if (! is_dir(dirname($path))) {
                @mkdir(dirname($path), 0755, true);
            }
            file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT));
        } catch (\Throwable) {
            //
        }
    }

    public static function allowedRouteNames(): array
    {
        return [
            'admin.activation.*',
            'admin.dashboard',
            'admin.settings.hub',
            'admin.settings.general',
            'admin.settings.general.update',
            'admin.settings.edit',
            'admin.settings.update',
            'admin.blog.*',
            'admin.tools.*',
            'logout',
        ];
    }

    public static function routeIsAllowed(?string $routeName): bool
    {
        if (! $routeName) {
            return false;
        }

        foreach (self::allowedRouteNames() as $pattern) {
            if (Str::is($pattern, $routeName)) {
                return true;
            }
        }

        return false;
    }

    protected static function base64UrlEncode(string $raw): string
    {
        return rtrim(strtr(base64_encode($raw), '+/', '-_'), '=');
    }

    protected static function base64UrlDecode(string $b64): string
    {
        $remainder = strlen($b64) % 4;
        if ($remainder) {
            $b64 .= str_repeat('=', 4 - $remainder);
        }

        return (string) base64_decode(strtr($b64, '-_', '+/'));
    }

    /**
     * @param  array{item_id?:string|int,email?:string,buyer?:string,domain?:string,exp?:int}  $claims
     */
    public static function issueJigsourceSignedKey(array $claims, ?string $secret = null): string
    {
        $secret = $secret ?: (string) config('services.jigsource.license_secret', '');
        $payload = self::base64UrlEncode(json_encode($claims, JSON_UNESCAPED_SLASHES));
        $sig = self::base64UrlEncode(hash_hmac('sha256', $payload, $secret, true));

        return 'JS1.'.$payload.'.'.$sig;
    }
}
