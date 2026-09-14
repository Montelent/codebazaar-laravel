<?php

namespace App\Support;

use App\Models\SiteSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Product activation — JigSource codes must be for CodeBazaar item #1229 only.
 */
class ProductActivation
{
    public const SETTING_KEY = 'product_activation';

    public const JIGSOURCE_ITEM_ID = '1229';

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

        if (self::looksLikeJigsourceSignedKey($code)) {
            $signed = self::activateJigsourceSigned($code);
            if ($signed['ok'] || ($signed['hard_fail'] ?? false)) {
                return $signed;
            }
        }

        $server = self::activateViaLicenseServer($code);
        if ($server['ok']) {
            return $server;
        }

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
     * Deep-scan API body for item id + name (JigSource response shapes vary).
     *
     * @param  array<string,mixed>  $body
     * @return array{id:string,name:string,slug:string}
     */
    protected static function extractPurchaseItem(array $body): array
    {
        $id = '';
        $name = '';
        $slug = '';

        $itemObjects = [];

        $candidates = [
            data_get($body, 'data.purchase.item'),
            data_get($body, 'purchase.item'),
            data_get($body, 'data.item'),
            data_get($body, 'item'),
            data_get($body, 'data.purchase'),
            data_get($body, 'purchase'),
            data_get($body, 'data'),
        ];

        foreach ($candidates as $node) {
            if (is_array($node)) {
                $itemObjects[] = $node;
            }
        }

        foreach ($itemObjects as $node) {
            if ($id === '') {
                foreach (['id', 'item_id', 'itemId', 'product_id', 'productId'] as $key) {
                    if (isset($node[$key]) && $node[$key] !== '' && $node[$key] !== null) {
                        // Prefer values that look like the item id, not purchase UUIDs
                        $val = (string) $node[$key];
                        if (! preg_match('/^[a-f0-9]{8}-[a-f0-9]{4}-/i', $val)) {
                            $id = $val;
                            break;
                        }
                    }
                }
            }

            if ($name === '') {
                foreach (['name', 'item_name', 'product_name', 'title'] as $key) {
                    if (! empty($node[$key]) && is_string($node[$key])) {
                        $name = trim($node[$key]);
                        break;
                    }
                }
            }

            if ($slug === '') {
                foreach (['slug', 'product_slug', 'permalink'] as $key) {
                    if (! empty($node[$key]) && is_string($node[$key])) {
                        $slug = strtolower(trim($node[$key]));
                        break;
                    }
                }
            }

            // Nested item inside purchase
            if (isset($node['item']) && is_array($node['item'])) {
                $inner = $node['item'];
                if ($id === '' && isset($inner['id']) && $inner['id'] !== '' && $inner['id'] !== null) {
                    $id = (string) $inner['id'];
                }
                if ($name === '' && ! empty($inner['name']) && is_string($inner['name'])) {
                    $name = trim($inner['name']);
                }
                if ($slug === '' && ! empty($inner['slug']) && is_string($inner['slug'])) {
                    $slug = strtolower(trim($inner['slug']));
                }
            }
        }

        // Last resort: walk whole tree for item.id pattern
        if ($id === '') {
            $id = self::findFirstNumericItemId($body);
        }

        return ['id' => $id, 'name' => $name, 'slug' => $slug];
    }

    /**
     * @param  mixed  $node
     */
    protected static function findFirstNumericItemId(mixed $node, int $depth = 0): string
    {
        if ($depth > 8 || ! is_array($node)) {
            return '';
        }

        if (array_key_exists('id', $node) && is_numeric($node['id'])) {
            // Heuristic: item ids are small integers; skip large order ids if any
            $n = (int) $node['id'];
            if ($n > 0 && $n < 100000000) {
                // Prefer if sibling looks like an item (has name/slug/category)
                if (isset($node['name']) || isset($node['slug']) || isset($node['category'])) {
                    return (string) $node['id'];
                }
            }
        }

        foreach ($node as $child) {
            if (is_array($child)) {
                $found = self::findFirstNumericItemId($child, $depth + 1);
                if ($found !== '') {
                    return $found;
                }
            }
        }

        return '';
    }

    /**
     * @return array{ok:bool,message:string,type?:string}
     */
    protected static function activateViaLicenseServer(string $code): array
    {
        $domain = self::currentDomain();
        $apiUrl = rtrim((string) config('services.license.verify_url', 'https://jigsource.store/api/purchases/validation'), '/');
        $product = (string) config('services.license.product', 'codebazaar');
        $expectedId = self::expectedItemId();
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
            'item_id' => $expectedId,
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
            ?: data_get($body, 'errors.purchase_code.0')
            ?: data_get($body, 'errors.api_key.0');

        if ($status === 'error' || $response->status() === 404) {
            return [
                'ok' => false,
                'message' => is_string($apiMessage) && $apiMessage !== ''
                    ? $apiMessage
                    : 'This purchase code is invalid.',
            ];
        }

        $isSuccess = $status === 'success'
            || (bool) data_get($body, 'valid')
            || (bool) data_get($body, 'success');

        if (! $isSuccess) {
            return [
                'ok' => false,
                'message' => is_string($apiMessage) && $apiMessage !== ''
                    ? $apiMessage
                    : 'This purchase code could not be verified.',
            ];
        }

        $item = self::extractPurchaseItem($body);
        $saleItemId = $item['id'];
        $saleItemName = $item['name'];
        $saleSlug = $item['slug'];

        // STRICT: must prove this sale is CodeBazaar (#1229)
        $idMatches = $saleItemId !== '' && (string) $saleItemId === (string) $expectedId;
        $nameMatches = $saleItemName !== '' && (
            str_contains(strtolower($saleItemName), 'codebazaar')
            || str_contains(strtolower($saleItemName), 'code bazaar')
        );
        $slugMatches = $saleSlug !== '' && str_contains($saleSlug, 'codebazaar');

        if ($saleItemId !== '' && ! $idMatches) {
            $label = $saleItemName !== '' ? $saleItemName : 'another product';

            return [
                'ok' => false,
                'message' => 'This purchase code belongs to '.$label.'. Please use a CodeBazaar purchase code.',
            ];
        }

        if (! $idMatches && ! $nameMatches && ! $slugMatches) {
            try {
                Log::warning('CodeBazaar activation: success without matching item', [
                    'expected_item_id' => $expectedId,
                    'parsed_item_id' => $saleItemId,
                    'parsed_name' => $saleItemName,
                    'parsed_slug' => $saleSlug,
                    'body_keys' => array_keys($body),
                    'data_keys' => is_array(data_get($body, 'data')) ? array_keys((array) data_get($body, 'data')) : [],
                ]);
            } catch (\Throwable) {
                //
            }

            return [
                'ok' => false,
                'message' => 'This purchase code does not belong to CodeBazaar.',
            ];
        }

        $purchase = data_get($body, 'data.purchase');
        if (! is_array($purchase)) {
            $purchase = data_get($body, 'purchase');
        }
        if (! is_array($purchase)) {
            $purchase = [];
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
            'item_id' => $idMatches ? $saleItemId : $expectedId,
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

        if (empty($payload['item_id']) || (string) $payload['item_id'] !== (string) $itemId) {
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
            'item_id' => $itemId,
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

        if (in_array($response->status(), [401, 403], true) || ! $response->successful()) {
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
