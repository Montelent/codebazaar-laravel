<?php

namespace App\Support;

use App\Models\SiteSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * Product activation for commercial installs.
 *
 * JigSource item: CodeBazaar (#1229)
 * https://jigsource.store/items/codebazaar-sell-code-scripts-digital-assets-laravel-marketplace/1229
 */
class ProductActivation
{
    public const SETTING_KEY = 'product_activation';

    /** Default JigSource item for CodeBazaar. */
    public const JIGSOURCE_ITEM_ID = '1229';

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

        if ((bool) config('services.license.disabled', false) || (bool) config('services.envato.licensing_disabled', false)) {
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

        if ($active && in_array($type, ['envato', 'jigsource', 'license_server'], true)) {
            $bound = strtolower((string) ($data['domain'] ?? ''));
            $current = self::currentDomain();
            if ($bound !== '' && $bound !== $current && empty($data['allow_domain_mismatch'])) {
                return array_merge($data, [
                    'active' => false,
                    'locked' => true,
                    'reason' => 'This license is registered to another domain ('.$bound.').',
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

    protected static function expectedItemId(): string
    {
        $id = trim((string) (
            config('services.license.item_id')
            ?: config('services.jigsource.item_id')
            ?: self::JIGSOURCE_ITEM_ID
        ));

        return $id !== '' ? $id : self::JIGSOURCE_ITEM_ID;
    }

    /**
     * @return array{ok:bool,message:string,type?:string}
     */
    public static function activate(string $code): array
    {
        $code = trim($code);
        if ($code === '') {
            return ['ok' => false, 'message' => 'Please enter your purchase code.'];
        }

        // 1) Author master
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

            return ['ok' => true, 'message' => 'License activated successfully.', 'type' => 'master'];
        }

        // 2) Optional offline JS1 signed keys
        if (self::looksLikeJigsourceSignedKey($code)) {
            $signed = self::activateJigsourceSigned($code);
            if ($signed['ok'] || ($signed['hard_fail'] ?? false)) {
                return $signed;
            }
        }

        // 3) JigSource / license server
        $server = self::activateViaLicenseServer($code);
        if ($server['ok']) {
            return $server;
        }

        // 4) Author-only direct Envato
        if (self::looksLikeUuidPurchaseCode($code) && trim((string) config('services.envato.token', '')) !== '') {
            $envato = self::activateEnvatoDirect($code);
            if ($envato['ok']) {
                return $envato;
            }
        }

        return [
            'ok' => false,
            'message' => $server['message'] ?? 'This purchase code is invalid or does not belong to CodeBazaar.',
        ];
    }

    protected static function looksLikeUuidPurchaseCode(string $code): bool
    {
        return (bool) preg_match(
            '/^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/i',
            $code
        );
    }

    protected static function looksLikeJigsourceSignedKey(string $code): bool
    {
        return (bool) preg_match('/^JS1\.[A-Za-z0-9_-]+\.[A-Za-z0-9_-]+$/', $code);
    }

    /**
     * Pull item id / name from varied API response shapes.
     *
     * @param  array<string,mixed>  $body
     * @return array{0:string,1:string,2:string} [itemId, itemName, slug]
     */
    protected static function extractPurchaseItem(array $body): array
    {
        $purchase = data_get($body, 'data.purchase');
        if (! is_array($purchase)) {
            $purchase = data_get($body, 'purchase');
        }
        if (! is_array($purchase)) {
            $purchase = data_get($body, 'data');
        }
        if (! is_array($purchase)) {
            $purchase = $body;
        }

        $item = data_get($purchase, 'item');
        if (! is_array($item)) {
            $item = data_get($body, 'data.item');
        }
        if (! is_array($item)) {
            $item = data_get($body, 'item');
        }
        if (! is_array($item)) {
            $item = [];
        }

        $idCandidates = [
            data_get($item, 'id'),
            data_get($purchase, 'item_id'),
            data_get($purchase, 'itemId'),
            data_get($body, 'data.item_id'),
            data_get($body, 'item_id'),
            data_get($body, 'data.purchase.item_id'),
            data_get($body, 'data.purchase.item.id'),
        ];

        $saleItemId = '';
        foreach ($idCandidates as $candidate) {
            if ($candidate !== null && $candidate !== '') {
                $saleItemId = (string) $candidate;
                break;
            }
        }

        $nameCandidates = [
            data_get($item, 'name'),
            data_get($purchase, 'item_name'),
            data_get($purchase, 'product_name'),
            data_get($body, 'data.item.name'),
            data_get($body, 'item_name'),
        ];

        $saleItemName = '';
        foreach ($nameCandidates as $candidate) {
            if (is_string($candidate) && trim($candidate) !== '') {
                $saleItemName = trim($candidate);
                break;
            }
        }

        $slugCandidates = [
            data_get($item, 'slug'),
            data_get($purchase, 'product_slug'),
            data_get($purchase, 'slug'),
            data_get($body, 'data.product_slug'),
        ];

        $saleSlug = '';
        foreach ($slugCandidates as $candidate) {
            if (is_string($candidate) && trim($candidate) !== '') {
                $saleSlug = strtolower(trim($candidate));
                break;
            }
        }

        return [$saleItemId, $saleItemName, $saleSlug];
    }

    /**
     * True when the sale is for CodeBazaar (item 1229 or name/slug match).
     */
    protected static function isCodeBazaarPurchase(string $saleItemId, string $saleItemName, string $saleSlug, string $expectedItemId): bool
    {
        if ($saleItemId !== '' && (string) $saleItemId === (string) $expectedItemId) {
            return true;
        }

        $haystack = strtolower($saleItemName.' '.$saleSlug);

        if ($haystack !== '' && (
            str_contains($haystack, 'codebazaar')
            || str_contains($haystack, 'code bazaar')
            || str_contains($haystack, 'code-bazaar')
        )) {
            return true;
        }

        return false;
    }

    /**
     * @return array{ok:bool,message:string,type?:string}
     */
    protected static function activateViaLicenseServer(string $code): array
    {
        $domain = self::currentDomain();
        $apiUrl = rtrim((string) config('services.license.verify_url', 'https://jigsource.store/api/purchases/validation'), '/');
        $product = (string) config('services.license.product', 'codebazaar');
        $itemId = self::expectedItemId();
        $clientId = trim((string) config('services.license.client_id', ''));

        $apiKey = trim((string) (
            config('services.license.api_key')
            ?: config('services.jigsource.api_key')
            ?: ''
        ));

        if ($apiUrl === '' || $apiKey === '') {
            return ['ok' => false, 'message' => 'License verification is temporarily unavailable. Please try again later.'];
        }

        $payload = [
            'api_key' => $apiKey,
            'purchase_code' => $code,
            'license_key' => $code,
            'code' => $code,
            'domain' => $domain,
            'product' => $product,
            'product_slug' => $product,
            'item_id' => $itemId,
        ];
        if ($clientId !== '') {
            $payload['client_id'] = $clientId;
        }

        try {
            $response = Http::acceptJson()
                ->timeout(25)
                ->asJson()
                ->withHeaders([
                    'User-Agent' => 'CodeBazaar-License/1.0',
                    'X-Api-Key' => $apiKey,
                    'Authorization' => 'Bearer '.$apiKey,
                ])
                ->post($apiUrl, $payload);
        } catch (\Throwable) {
            return ['ok' => false, 'message' => 'Unable to reach the license server. Please check your connection and try again.'];
        }

        $body = $response->json();
        if (! is_array($body)) {
            $body = [];
        }

        $status = strtolower((string) (data_get($body, 'status') ?? ''));

        $apiMessage = data_get($body, 'msg')
            ?: data_get($body, 'message')
            ?: data_get($body, 'error')
            ?: data_get($body, 'errors.api_key.0')
            ?: data_get($body, 'errors.purchase_code.0');

        if ($status === 'error' || $response->status() === 404) {
            $msg = is_string($apiMessage) && $apiMessage !== ''
                ? $apiMessage
                : 'This purchase code is invalid.';

            return ['ok' => false, 'message' => $msg];
        }

        $isSuccess = $status === 'success'
            || (bool) data_get($body, 'valid')
            || (bool) data_get($body, 'success');

        if (! $isSuccess) {
            $msg = is_string($apiMessage) && $apiMessage !== ''
                ? $apiMessage
                : 'This purchase code could not be verified.';

            return ['ok' => false, 'message' => $msg];
        }

        [$saleItemId, $saleItemName, $saleSlug] = self::extractPurchaseItem($body);

        // Reject only when we can prove it is a different product
        if ($saleItemId !== '' && (string) $saleItemId !== (string) $itemId) {
            $other = $saleItemName !== '' ? $saleItemName : 'another product';

            return [
                'ok' => false,
                'message' => 'This purchase code belongs to '.$other.'. Please use a CodeBazaar purchase code.',
            ];
        }

        if ($saleItemId === '' && $saleItemName !== '' && ! self::isCodeBazaarPurchase('', $saleItemName, $saleSlug, $itemId)) {
            return [
                'ok' => false,
                'message' => 'This purchase code belongs to '.$saleItemName.'. Please use a CodeBazaar purchase code.',
            ];
        }

        // If API returned success but no item fields, still accept only when
        // we cannot detect a different product (JigSource may omit item on some responses).
        // Prefer accept when status=success after sending item_id in the request.

        $purchase = data_get($body, 'data.purchase');
        if (! is_array($purchase)) {
            $purchase = data_get($body, 'purchase') ?: [];
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
            'item_id' => $saleItemId !== '' ? $saleItemId : $itemId,
            'item_name' => $saleItemName !== '' ? $saleItemName : 'CodeBazaar',
            'license' => data_get($purchase, 'license_type') ?: data_get($purchase, 'license'),
            'supported_until' => data_get($purchase, 'supported_until'),
            'purchased_at' => data_get($purchase, 'purchased_at'),
            'amount' => data_get($purchase, 'price') ?: data_get($purchase, 'amount'),
            'currency' => data_get($purchase, 'currency'),
            'source' => 'jigsource',
            'activated_at' => now()->toIso8601String(),
            'verified_via' => 'jigsource',
        ]);

        return [
            'ok' => true,
            'message' => 'License activated successfully for '.$domain.'.',
            'type' => 'jigsource',
        ];
    }

    /**
     * @return array{ok:bool,message:string,type?:string,hard_fail?:bool}
     */
    protected static function activateJigsourceSigned(string $code): array
    {
        if (! preg_match('/^JS1\.([A-Za-z0-9_-]+)\.([A-Za-z0-9_-]+)$/', $code, $m)) {
            return ['ok' => false, 'message' => 'Invalid license key.'];
        }

        $secret = (string) config('services.jigsource.license_secret', '');
        if ($secret === '') {
            return ['ok' => false, 'message' => 'This license key cannot be verified on this install.'];
        }

        $payloadB64 = $m[1];
        $sig = $m[2];
        $expected = self::base64UrlEncode(hash_hmac('sha256', $payloadB64, $secret, true));
        if (! hash_equals($expected, $sig)) {
            return ['ok' => false, 'message' => 'Invalid license key.', 'hard_fail' => true];
        }

        $json = self::base64UrlDecode($payloadB64);
        $payload = json_decode($json ?: 'null', true);
        if (! is_array($payload)) {
            return ['ok' => false, 'message' => 'Invalid license key.', 'hard_fail' => true];
        }

        $domain = self::currentDomain();
        $itemId = self::expectedItemId();

        if (! empty($payload['item_id']) && (string) $payload['item_id'] !== (string) $itemId) {
            return ['ok' => false, 'message' => 'This license key is for a different product.', 'hard_fail' => true];
        }

        if (! empty($payload['exp']) && time() > (int) $payload['exp']) {
            return ['ok' => false, 'message' => 'This license key has expired.', 'hard_fail' => true];
        }

        if (! empty($payload['domain']) && strtolower((string) $payload['domain']) !== $domain) {
            return ['ok' => false, 'message' => 'This license key is registered to another domain.', 'hard_fail' => true];
        }

        self::persist([
            'active' => true,
            'type' => 'jigsource',
            'domain' => $domain,
            'code_hint' => Str::mask($code, '*', 6, max(0, strlen($code) - 10)),
            'code_hash' => hash('sha256', $code),
            'buyer' => $payload['email'] ?? $payload['buyer'] ?? null,
            'item_id' => $payload['item_id'] ?? $itemId,
            'source' => 'jigsource',
            'activated_at' => now()->toIso8601String(),
            'verified_via' => 'jigsource_signed',
        ]);

        return [
            'ok' => true,
            'message' => 'License activated successfully for '.$domain.'.',
            'type' => 'jigsource',
        ];
    }

    /**
     * @return array{ok:bool,message:string,type?:string}
     */
    protected static function activateEnvatoDirect(string $code): array
    {
        $token = trim((string) config('services.envato.token', ''));
        $itemId = trim((string) config('services.envato.item_id', ''));

        if ($token === '') {
            return ['ok' => false, 'message' => 'This purchase code could not be verified.'];
        }

        try {
            $response = Http::withToken($token)
                ->withHeaders(['User-Agent' => 'CodeBazaar-License/1.0'])
                ->acceptJson()
                ->timeout(25)
                ->get('https://api.envato.com/v3/market/author/sale', ['code' => $code]);
        } catch (\Throwable) {
            return ['ok' => false, 'message' => 'Unable to reach the license server. Please try again later.'];
        }

        if ($response->status() === 404) {
            return ['ok' => false, 'message' => 'This purchase code is invalid.'];
        }

        if (in_array($response->status(), [401, 403], true)) {
            return ['ok' => false, 'message' => 'License verification is temporarily unavailable. Please try again later.'];
        }

        if (! $response->successful()) {
            return ['ok' => false, 'message' => 'This purchase code could not be verified.'];
        }

        $sale = $response->json();
        if (! is_array($sale) || empty($sale)) {
            return ['ok' => false, 'message' => 'This purchase code could not be verified.'];
        }

        $saleItemId = (string) data_get($sale, 'item.id', '');
        $saleItemName = (string) data_get($sale, 'item.name', '');

        if ($itemId !== '' && $saleItemId !== '' && $saleItemId !== $itemId) {
            return [
                'ok' => false,
                'message' => 'This purchase code belongs to a different product. Please use a CodeBazaar purchase code.',
            ];
        }

        self::persist([
            'active' => true,
            'type' => 'envato',
            'domain' => self::currentDomain(),
            'code_hint' => Str::mask($code, '*', 4, max(0, strlen($code) - 8)),
            'code_hash' => hash('sha256', strtolower($code)),
            'buyer' => data_get($sale, 'buyer') ?: data_get($sale, 'buyer_username'),
            'item_id' => $saleItemId ?: $itemId,
            'item_name' => $saleItemName ?: null,
            'license' => data_get($sale, 'license'),
            'supported_until' => data_get($sale, 'supported_until'),
            'source' => 'envato',
            'activated_at' => now()->toIso8601String(),
            'verified_via' => 'envato',
        ]);

        return [
            'ok' => true,
            'message' => 'License activated successfully for '.self::currentDomain().'.',
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
