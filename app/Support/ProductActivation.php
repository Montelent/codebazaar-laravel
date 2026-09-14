<?php

namespace App\Support;

use App\Models\SiteSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * Product activation for commercial installs.
 *
 * Buyer installs do NOT contain Envato personal tokens or JigSource API keys.
 * Live verification always goes to the seller license server:
 *   POST {LICENSE_VERIFY_URL}  (default https://jigsource.store/api/purchases/validation)
 *
 * That server holds secrets and may validate Envato and/or JigSource codes.
 *
 * Local author tools (optional, only if set in the server's own .env):
 *   ENVATO_PERSONAL_TOKEN — direct Envato API (author demo / private proxy)
 *   JIGSOURCE_API_KEY     — authenticated JigSource calls from author server
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

        // 1) Author master (hash only — never ship the plaintext passphrase in docs for buyers)
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

        // 2) Optional offline JS1 signed keys (only if author set JIGSOURCE_LICENSE_SECRET on THIS server)
        if (self::looksLikeJigsourceSignedKey($code)) {
            $signed = self::activateJigsourceSigned($code);
            if ($signed['ok'] || ($signed['hard_fail'] ?? false)) {
                return $signed;
            }
        }

        // 3) Seller license server (default path for all sold installs — no secrets in the product)
        $server = self::activateViaLicenseServer($code);
        if ($server['ok']) {
            return $server;
        }

        // 4) Author-only direct Envato API (only when ENVATO_PERSONAL_TOKEN is set on this server)
        //    Not available in the distributed zip — buyers will not have this token.
        if (self::looksLikeUuidPurchaseCode($code) && trim((string) config('services.envato.token', '')) !== '') {
            $envato = self::activateEnvatoDirect($code);
            if ($envato['ok']) {
                return $envato;
            }
        }

        return [
            'ok' => false,
            'message' => $server['message'] ?? 'Invalid or unrecognized purchase code.',
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
     * Call the seller license server. No API secrets are shipped in the product.
     *
     * Expected success:
     *   { "status": "success", "data": { "purchase": { ... } } }
     * Expected error:
     *   { "status": "error", "msg": "Invalid purchase code" }
     *
     * @return array{ok:bool,message:string,type?:string}
     */
    protected static function activateViaLicenseServer(string $code): array
    {
        $domain = self::currentDomain();
        $apiUrl = rtrim((string) config('services.license.verify_url', 'https://jigsource.store/api/purchases/validation'), '/');
        $product = (string) config('services.license.product', 'codebazaar');
        $itemId = trim((string) config('services.license.item_id', ''));
        $clientId = trim((string) config('services.license.client_id', ''));

        // Author server only: if JIGSOURCE_API_KEY is present in THIS environment, send it.
        // Distributed buyer installs will have an empty key.
        $authorApiKey = trim((string) config('services.jigsource.api_key', ''));

        if ($apiUrl === '') {
            return ['ok' => false, 'message' => 'License server URL is not configured.'];
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
        if ($clientId !== '') {
            $payload['client_id'] = $clientId;
        }

        try {
            $request = Http::acceptJson()
                ->timeout(25)
                ->asJson()
                ->withHeaders([
                    'User-Agent' => 'CodeBazaar-License/1.0',
                ]);

            // Never hardcode a secret. Only attach if this install's .env provides one (author proxy).
            if ($authorApiKey !== '') {
                $request = $request->withHeaders([
                    'X-Api-Key' => $authorApiKey,
                    'Authorization' => 'Bearer '.$authorApiKey,
                ]);
            }

            $response = $request->post($apiUrl, $payload);
        } catch (\Throwable $e) {
            return ['ok' => false, 'message' => 'Could not reach license server: '.$e->getMessage()];
        }

        $body = $response->json();
        if (! is_array($body)) {
            $body = [];
        }

        $status = strtolower((string) (data_get($body, 'status') ?? ''));

        if ($status === 'error' || $response->status() === 404) {
            $msg = data_get($body, 'msg')
                ?: data_get($body, 'message')
                ?: data_get($body, 'error')
                ?: 'Invalid purchase code';

            return ['ok' => false, 'message' => is_string($msg) ? $msg : 'Invalid purchase code'];
        }

        $isSuccess = $status === 'success'
            || (bool) data_get($body, 'valid')
            || (bool) data_get($body, 'success');

        if (! $isSuccess) {
            if (! $response->successful()) {
                $msg = data_get($body, 'msg')
                    ?: data_get($body, 'message')
                    ?: ('License server error (HTTP '.$response->status().')');

                return ['ok' => false, 'message' => is_string($msg) ? $msg : 'License server rejected this code.'];
            }

            return [
                'ok' => false,
                'message' => (string) (
                    data_get($body, 'msg')
                    ?: data_get($body, 'message')
                    ?: 'License server rejected this code.'
                ),
            ];
        }

        $purchase = data_get($body, 'data.purchase') ?: data_get($body, 'purchase') ?: [];
        $item = data_get($purchase, 'item') ?: [];

        $saleItemId = (string) (data_get($item, 'id') ?: data_get($purchase, 'item_id') ?: '');
        $saleItemName = (string) (data_get($item, 'name') ?: '');

        if ($itemId !== '' && $saleItemId !== '' && $saleItemId !== $itemId) {
            return [
                'ok' => false,
                'message' => 'This purchase code belongs to a different product'
                    .($saleItemName !== '' ? ' ('.$saleItemName.')' : '').'.',
            ];
        }

        // Detect source label when the license server reports it
        $sourceHint = strtolower((string) (
            data_get($body, 'source')
            ?: data_get($body, 'data.source')
            ?: data_get($purchase, 'source')
            ?: data_get($purchase, 'marketplace')
            ?: 'license_server'
        ));
        $type = str_contains($sourceHint, 'envato') || str_contains($sourceHint, 'codecanyon')
            ? 'envato'
            : (str_contains($sourceHint, 'jig') ? 'jigsource' : 'license_server');

        self::persist([
            'active' => true,
            'type' => $type,
            'domain' => $domain,
            'code_hint' => Str::mask($code, '*', 4, max(0, strlen($code) - 8)),
            'code_hash' => hash('sha256', strtolower($code)),
            'buyer' => data_get($purchase, 'buyer')
                ?: data_get($purchase, 'email')
                ?: data_get($body, 'data.buyer'),
            'item_id' => $saleItemId ?: $itemId,
            'item_name' => $saleItemName ?: null,
            'license' => data_get($purchase, 'license_type') ?: data_get($purchase, 'license'),
            'supported_until' => data_get($purchase, 'supported_until'),
            'purchased_at' => data_get($purchase, 'purchased_at'),
            'amount' => data_get($purchase, 'price') ?: data_get($purchase, 'amount'),
            'currency' => data_get($purchase, 'currency'),
            'source' => 'license_server',
            'activated_at' => now()->toIso8601String(),
            'verified_via' => 'license_server',
        ]);

        $label = $saleItemName !== '' ? $saleItemName : 'Purchase';

        return [
            'ok' => true,
            'message' => $label.' verified and bound to '.$domain.'.',
            'type' => $type,
        ];
    }

    /**
     * Offline JS1 keys — only when JIGSOURCE_LICENSE_SECRET exists on this server.
     *
     * @return array{ok:bool,message:string,type?:string,hard_fail?:bool}
     */
    protected static function activateJigsourceSigned(string $code): array
    {
        if (! preg_match('/^JS1\.([A-Za-z0-9_-]+)\.([A-Za-z0-9_-]+)$/', $code, $m)) {
            return ['ok' => false, 'message' => 'Not a signed JigSource key.'];
        }

        $secret = (string) config('services.jigsource.license_secret', '');
        if ($secret === '') {
            return ['ok' => false, 'message' => 'Signed key support is not configured on this install.'];
        }

        $payloadB64 = $m[1];
        $sig = $m[2];
        $expected = self::base64UrlEncode(hash_hmac('sha256', $payloadB64, $secret, true));
        if (! hash_equals($expected, $sig)) {
            return ['ok' => false, 'message' => 'Invalid JigSource license signature.', 'hard_fail' => true];
        }

        $json = self::base64UrlDecode($payloadB64);
        $payload = json_decode($json ?: 'null', true);
        if (! is_array($payload)) {
            return ['ok' => false, 'message' => 'Invalid JigSource license payload.', 'hard_fail' => true];
        }

        $domain = self::currentDomain();
        $itemId = (string) config('services.jigsource.item_id', config('services.license.item_id', ''));

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

    /**
     * Direct Envato API — author/demo servers only (token from .env, never shipped).
     *
     * @return array{ok:bool,message:string,type?:string}
     */
    protected static function activateEnvatoDirect(string $code): array
    {
        $token = trim((string) config('services.envato.token', ''));
        $itemId = trim((string) config('services.envato.item_id', ''));

        if ($token === '') {
            return ['ok' => false, 'message' => 'Envato token not configured on this server.'];
        }

        try {
            $response = Http::withToken($token)
                ->withHeaders(['User-Agent' => 'CodeBazaar-License/1.0'])
                ->acceptJson()
                ->timeout(25)
                ->get('https://api.envato.com/v3/market/author/sale', ['code' => $code]);
        } catch (\Throwable $e) {
            return ['ok' => false, 'message' => 'Could not reach Envato API: '.$e->getMessage()];
        }

        if ($response->status() === 404) {
            return ['ok' => false, 'message' => 'Envato purchase code not found or invalid.'];
        }

        if (in_array($response->status(), [401, 403], true)) {
            return ['ok' => false, 'message' => 'Envato API authentication failed on this server.'];
        }

        if (! $response->successful()) {
            return ['ok' => false, 'message' => 'Envato API error (HTTP '.$response->status().').'];
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
