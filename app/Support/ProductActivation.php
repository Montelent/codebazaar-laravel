<?php

namespace App\Support;

use App\Models\SiteSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * Marketplace product activation (CodeCanyon / Envato purchase code).
 *
 * Buyers activate with a valid Envato purchase code (bound to one domain).
 * Author master unlock uses a private passphrase known only offline;
 * only a SHA-256 hash is stored in this file (never the plaintext).
 */
class ProductActivation
{
    public const SETTING_KEY = 'product_activation';

    /**
     * SHA-256 hex of: cbz-master-v1|{author_master_passphrase}
     * To rotate: hash the new string offline and replace this constant.
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

        if ($active && $type === 'envato') {
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
            return ['ok' => false, 'message' => 'Please enter a purchase code or activation key.'];
        }

        // Author master passphrase (compared via hash only)
        $attempt = hash('sha256', 'cbz-master-v1|'.$code);
        if (hash_equals(self::MASTER_KEY_HASH, $attempt)) {
            self::persist([
                'active' => true,
                'type' => 'master',
                'domain' => self::currentDomain(),
                'code_hint' => 'master',
                'buyer' => 'Author',
                'activated_at' => now()->toIso8601String(),
            ]);

            return ['ok' => true, 'message' => 'Author master key accepted. Product unlocked permanently on this install.', 'type' => 'master'];
        }

        if (! preg_match('/^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/i', $code)) {
            return ['ok' => false, 'message' => 'Invalid format. Enter a valid Envato purchase code (or contact the author).'];
        }

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
                'activated_at' => now()->toIso8601String(),
                'verified_via' => 'format_only',
            ]);

            return [
                'ok' => true,
                'message' => 'License saved and bound to '.self::currentDomain().'. (Configure ENVATO_PERSONAL_TOKEN for live Envato verification.)',
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
            return ['ok' => false, 'message' => 'Purchase code not found. Check the code from your Envato Downloads page.'];
        }

        if (! $response->successful()) {
            return ['ok' => false, 'message' => 'Envato API error (HTTP '.$response->status().'). Try again later.'];
        }

        $sale = $response->json();
        $saleItemId = (string) data_get($sale, 'item.id', '');

        if ($itemId !== '' && $saleItemId !== '' && $saleItemId !== $itemId) {
            return ['ok' => false, 'message' => 'This purchase code belongs to a different Envato item.'];
        }

        $buyer = data_get($sale, 'buyer') ?: data_get($sale, 'buyer_username');

        self::persist([
            'active' => true,
            'type' => 'envato',
            'domain' => self::currentDomain(),
            'code_hint' => Str::mask($code, '*', 4, max(0, strlen($code) - 8)),
            'code_hash' => hash('sha256', strtolower($code)),
            'buyer' => $buyer,
            'item_id' => $saleItemId ?: $itemId,
            'item_name' => data_get($sale, 'item.name'),
            'sold_at' => data_get($sale, 'sold_at'),
            'license' => data_get($sale, 'license'),
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
}
