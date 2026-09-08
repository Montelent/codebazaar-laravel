<?php

namespace App\Support;

use App\Models\SiteSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * Product activation for commercial installs.
 *
 * Supported unlock methods (same input field):
 * 1) Author master passphrase (hash-only in source)
 * 2) Envato / CodeCanyon purchase code
 * 3) JigSource.store license key (API and/or signed key)
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

            return ['ok' => true, 'message' => 'Author master key accepted. Product unlocked permanently on this install.', 'type' => 'master'];
        }

        // 2) JigSource.store (signed key or API)
        if (self::looksLikeJigsourceKey($code)) {
            $js = self::activateJigsource($code);
            if ($js['ok'] || ($js['hard_fail'] ?? false)) {
                return $js;
            }
            // soft fail → try Envato next
        }

        // 3) Envato UUID purchase code
        if (preg_match('/^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/i', $code)) {
            return self::activateEnvato($code);
        }

        // 4) Any other string: try JigSource API first, then friendly error
        $js = self::activateJigsource($code);
        if ($js['ok']) {
            return $js;
        }

        return [
            'ok' => false,
            'message' => $js['message'] ?? 'Invalid license. Use your JigSource license key or Envato purchase code.',
        ];
    }

    protected static function looksLikeJigsourceKey(string $code): bool
    {
        // Signed format: JS1.{base64url_payload}.{signature}
        if (preg_match('/^JS1\.[A-Za-z0-9_-]+\.[A-Za-z0-9_-]+$/', $code)) {
            return true;
        }

        // Prefixed keys from store
        if (preg_match('/^(JS|JIG|JIGSOURCE)[-_]/i', $code)) {
            return true;
        }

        return false;
    }

    /**
     * @return array{ok:bool,message:string,type?:string,hard_fail?:bool}
     */
    protected static function activateJigsource(string $code): array
    {
        $domain = self::currentDomain();

        // A) Local verify of signed keys (no network) — secret shared with jigsource.store
        if (preg_match('/^JS1\.([A-Za-z0-9_-]+)\.([A-Za-z0-9_-]+)$/', $code, $m)) {
            $payloadB64 = $m[1];
            $sig = $m[2];
            $secret = (string) config('services.jigsource.license_secret', env('JIGSOURCE_LICENSE_SECRET', ''));

            if ($secret === '') {
                // Fall through to API if secret not on this install
            } else {
                $expected = self::base64UrlEncode(hash_hmac('sha256', $payloadB64, $secret, true));
                if (! hash_equals($expected, $sig)) {
                    return ['ok' => false, 'message' => 'Invalid JigSource license signature.', 'hard_fail' => true];
                }

                $json = self::base64UrlDecode($payloadB64);
                $payload = json_decode($json ?: 'null', true);
                if (! is_array($payload)) {
                    return ['ok' => false, 'message' => 'Invalid JigSource license payload.', 'hard_fail' => true];
                }

                $itemId = (string) config('services.jigsource.item_id', env('JIGSOURCE_ITEM_ID', ''));
                if ($itemId !== '' && ! empty($payload['item_id']) && (string) $payload['item_id'] !== $itemId) {
                    return ['ok' => false, 'message' => 'This JigSource license is for a different product.', 'hard_fail' => true];
                }

                if (! empty($payload['exp']) && time() > (int) $payload['exp']) {
                    return ['ok' => false, 'message' => 'This JigSource license has expired.', 'hard_fail' => true];
                }

                // Optional domain lock embedded at issue time
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
                ]);

                return [
                    'ok' => true,
                    'message' => 'JigSource license verified and bound to '.$domain.'.',
                    'type' => 'jigsource',
                ];
            }
        }

        // B) Remote API on jigsource.store
        $apiUrl = rtrim((string) config('services.jigsource.verify_url', env('JIGSOURCE_VERIFY_URL', 'https://jigsource.store/api/license/verify')), '/');
        $apiKey = (string) config('services.jigsource.api_key', env('JIGSOURCE_API_KEY', ''));
        $itemId = (string) config('services.jigsource.item_id', env('JIGSOURCE_ITEM_ID', ''));

        if ($apiUrl === '') {
            return ['ok' => false, 'message' => 'JigSource verification is not configured (missing verify URL).'];
        }

        try {
            $request = Http::acceptJson()->timeout(20)->asJson();
            if ($apiKey !== '') {
                $request = $request->withHeaders([
                    'X-Api-Key' => $apiKey,
                    'Authorization' => 'Bearer '.$apiKey,
                ]);
            }

            $response = $request->post($apiUrl, [
                'license_key' => $code,
                'domain' => $domain,
                'item_id' => $itemId ?: null,
                'product' => 'codebazaar',
            ]);
        } catch (\Throwable $e) {
            return ['ok' => false, 'message' => 'Could not reach JigSource license server: '.$e->getMessage()];
        }

        if ($response->status() === 404) {
            return ['ok' => false, 'message' => 'JigSource license not found.'];
        }

        if (! $response->successful()) {
            $msg = data_get($response->json(), 'message')
                ?: data_get($response->json(), 'error')
                ?: ('JigSource API error (HTTP '.$response->status().')');

            return ['ok' => false, 'message' => $msg];
        }

        $body = $response->json() ?: [];
        $valid = (bool) (data_get($body, 'valid') ?? data_get($body, 'success') ?? data_get($body, 'active') ?? false);

        if (! $valid) {
            return [
                'ok' => false,
                'message' => (string) (data_get($body, 'message') ?: 'JigSource rejected this license key.'),
            ];
        }

        self::persist([
            'active' => true,
            'type' => 'jigsource',
            'domain' => $domain,
            'code_hint' => Str::mask($code, '*', 4, max(0, strlen($code) - 8)),
            'code_hash' => hash('sha256', $code),
            'buyer' => data_get($body, 'buyer') ?: data_get($body, 'email'),
            'item_id' => data_get($body, 'item_id') ?: $itemId,
            'source' => 'jigsource_api',
            'activated_at' => now()->toIso8601String(),
        ]);

        return [
            'ok' => true,
            'message' => 'JigSource license verified and bound to '.$domain.'.',
            'type' => 'jigsource',
        ];
    }

    /**
     * @return array{ok:bool,message:string,type?:string}
     */
    protected static function activateEnvato(string $code): array
    {
        $token = (string) config('services.envato.token', env('ENVATO_PERSONAL_TOKEN', ''));
        $itemId = (string) config('services.envato.item_id', env('ENVATO_ITEM_ID', ''));

        if ($token === '') {
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
                'message' => 'Envato-style code saved and bound to '.self::currentDomain().'. (Set ENVATO_PERSONAL_TOKEN for live verification.)',
                'type' => 'envato',
            ];
        }

        try {
            $response = Http::withToken($token)
                ->acceptJson()
                ->timeout(20)
                ->get('https://api.envato.com/v3/market/author/sale', [
                    'code' => $code,
                ]);
        } catch (\Throwable $e) {
            return ['ok' => false, 'message' => 'Could not reach Envato API: '.$e->getMessage()];
        }

        if ($response->status() === 404) {
            return ['ok' => false, 'message' => 'Envato purchase code not found.'];
        }

        if (! $response->successful()) {
            return ['ok' => false, 'message' => 'Envato API error (HTTP '.$response->status().').'];
        }

        $sale = $response->json();
        $saleItemId = (string) data_get($sale, 'item.id', '');

        if ($itemId !== '' && $saleItemId !== '' && $saleItemId !== $itemId) {
            return ['ok' => false, 'message' => 'This Envato code belongs to a different item.'];
        }

        self::persist([
            'active' => true,
            'type' => 'envato',
            'domain' => self::currentDomain(),
            'code_hint' => Str::mask($code, '*', 4, max(0, strlen($code) - 8)),
            'code_hash' => hash('sha256', strtolower($code)),
            'buyer' => data_get($sale, 'buyer') ?: data_get($sale, 'buyer_username'),
            'item_id' => $saleItemId ?: $itemId,
            'item_name' => data_get($sale, 'item.name'),
            'source' => 'envato_api',
            'activated_at' => now()->toIso8601String(),
            'verified_via' => 'envato_api',
        ]);

        return [
            'ok' => true,
            'message' => 'Envato license verified and bound to '.self::currentDomain().'.',
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
     * Helper for jigsource.store: generate a signed license key.
     * Use the same JIGSOURCE_LICENSE_SECRET on both sites.
     *
     * @param  array{item_id?:string|int,email?:string,buyer?:string,domain?:string,exp?:int}  $claims
     */
    public static function issueJigsourceSignedKey(array $claims, ?string $secret = null): string
    {
        $secret = $secret ?: (string) config('services.jigsource.license_secret', env('JIGSOURCE_LICENSE_SECRET', ''));
        $payload = self::base64UrlEncode(json_encode($claims, JSON_UNESCAPED_SLASHES));
        $sig = self::base64UrlEncode(hash_hmac('sha256', $payload, $secret, true));

        return 'JS1.'.$payload.'.'.$sig;
    }
}
