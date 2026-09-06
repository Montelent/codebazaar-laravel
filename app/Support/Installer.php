<?php

namespace App\Support;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class Installer
{
    public static function lockPath(): string
    {
        return storage_path('installed');
    }

    public static function isInstalled(): bool
    {
        return is_file(self::lockPath());
    }

    public static function markInstalled(): void
    {
        $dir = dirname(self::lockPath());
        if (! is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        file_put_contents(self::lockPath(), date('c') . "\n");
    }

    public static function requirements(): array
    {
        $checks = [];
        $checks[] = [
            'label' => 'PHP version >= 8.2',
            'ok' => version_compare(PHP_VERSION, '8.2.0', '>='),
            'current' => PHP_VERSION,
        ];
        foreach (['pdo', 'mbstring', 'openssl', 'tokenizer', 'json', 'curl', 'fileinfo', 'ctype', 'bcmath'] as $ext) {
            $checks[] = [
                'label' => "PHP extension: {$ext}",
                'ok' => extension_loaded($ext),
                'current' => extension_loaded($ext) ? 'installed' : 'missing',
            ];
        }
        $checks[] = [
            'label' => 'PHP extension: pdo_mysql OR pdo_pgsql OR pdo_sqlite',
            'ok' => extension_loaded('pdo_mysql') || extension_loaded('pdo_pgsql') || extension_loaded('pdo_sqlite'),
            'current' => 'need at least one PDO driver',
        ];
        $checks[] = [
            'label' => 'PHP extension: pdo_pgsql (optional)',
            'ok' => true,
            'current' => extension_loaded('pdo_pgsql') ? 'installed' : 'optional',
        ];

        $writable = [
            'storage' => storage_path(),
            'storage/framework' => storage_path('framework'),
            'storage/logs' => storage_path('logs'),
            'bootstrap/cache' => base_path('bootstrap/cache'),
            'project root (for .env)' => base_path(),
        ];
        foreach ($writable as $label => $path) {
            if (! is_dir($path)) {
                @mkdir($path, 0755, true);
            }
            $checks[] = [
                'label' => "Writable: {$label}",
                'ok' => is_dir($path) && is_writable($path),
                'current' => is_writable($path) ? 'writable' : 'not writable',
            ];
        }

        $checks[] = [
            'label' => 'Composer vendor/ present',
            'ok' => is_file(base_path('vendor/autoload.php')),
            'current' => is_file(base_path('vendor/autoload.php')) ? 'found' : 'missing — include vendor in ZIP',
        ];

        return $checks;
    }

    public static function allRequirementsPassed(array $checks): bool
    {
        foreach ($checks as $c) {
            if (empty($c['ok'])) {
                return false;
            }
        }

        return true;
    }

    public static function testDatabase(array $db): array
    {
        try {
            $driver = $db['connection'] ?? 'mysql';
            if ($driver === 'sqlite') {
                $path = $db['database'] ?: database_path('database.sqlite');
                if (! str_starts_with($path, '/') && ! preg_match('#^[A-Za-z]:#', $path)) {
                    $path = database_path(basename($path));
                }
                if (! is_file($path)) {
                    touch($path);
                }
                new \PDO('sqlite:' . $path);

                return ['ok' => true, 'message' => 'SQLite OK'];
            }
            $host = $db['host'] ?? '127.0.0.1';
            $port = $db['port'] ?? ($driver === 'pgsql' ? '5432' : '3306');
            $name = $db['database'] ?? '';
            $user = $db['username'] ?? '';
            $pass = $db['password'] ?? '';
            $dsn = $driver === 'pgsql'
                ? "pgsql:host={$host};port={$port};dbname={$name}"
                : "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";
            $pdo = new \PDO($dsn, $user, $pass, [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]);
            $pdo->query('SELECT 1');

            return ['ok' => true, 'message' => 'Connection successful'];
        } catch (\Throwable $e) {
            return ['ok' => false, 'message' => $e->getMessage()];
        }
    }

    public static function normalizeAppUrl(string $url): string
    {
        $url = rtrim(trim($url), '/');

        return preg_replace('#/index\.php$#i', '', $url) ?: $url;
    }

    public static function writeEnv(array $data): void
    {
        $key = $data['app_key'] ?? ('base64:' . base64_encode(random_bytes(32)));
        $conn = $data['db_connection'] ?? 'mysql';
        $pass = str_replace(['\\', '"'], ['\\\\', '\\"'], $data['db_password'] ?? '');
        $adminPass = str_replace(['\\', '"'], ['\\\\', '\\"'], $data['admin_password'] ?? '');

        $baseUrl = self::normalizeAppUrl($data['app_url'] ?? '');

        $lines = [
            'APP_NAME="' . ($data['app_name'] ?? 'CodeBazaar') . '"',
            'APP_ENV=production',
            'APP_KEY=' . $key,
            'APP_DEBUG=false',
            'APP_URL=' . $baseUrl,
            'ASSET_URL=' . $baseUrl,
            'FORCE_INDEX_PHP=false',
            'FORCE_HTTPS=true',
            '',
            'LOG_CHANNEL=stack',
            'LOG_LEVEL=error',
            '',
            'DB_CONNECTION=' . $conn,
            'DB_HOST=' . ($data['db_host'] ?? '127.0.0.1'),
            'DB_PORT=' . ($data['db_port'] ?? '3306'),
            'DB_DATABASE=' . ($data['db_database'] ?? ''),
            'DB_USERNAME=' . ($data['db_username'] ?? ''),
            'DB_PASSWORD="' . $pass . '"',
            '',
            'SESSION_DRIVER=file',
            'SESSION_LIFETIME=120',
            'CACHE_STORE=file',
            'QUEUE_CONNECTION=sync',
            'FILESYSTEM_DISK=public',
            '',
            'ADMIN_EMAIL=' . ($data['admin_email'] ?? ''),
            'ADMIN_PASSWORD="' . $adminPass . '"',
            'ADMIN_NAME="' . ($data['admin_name'] ?? 'Admin') . '"',
            '',
            'STRIPE_KEY=',
            'STRIPE_SECRET=',
        ];
        file_put_contents(base_path('.env'), implode("\n", $lines) . "\n");
    }

    /** Runs every migration file — full schema in one install step. */
    public static function runMigrations(): void
    {
        Artisan::call('migrate', ['--force' => true]);
    }

    public static function seedAdmin(string $name, string $email, string $password): void
    {
        $exists = DB::table('users')->where('email', $email)->first();
        $payload = [
            'name' => $name,
            'username' => 'admin',
            'email' => $email,
            'password' => Hash::make($password),
            'role' => 'admin',
            'email_verified_at' => now(),
            'updated_at' => now(),
        ];
        if ($exists) {
            DB::table('users')->where('email', $email)->update($payload);
        } else {
            $payload['created_at'] = now();
            DB::table('users')->insert($payload);
        }

        if (DB::table('categories')->count() === 0) {
            foreach ([
                ['name' => 'JavaScript', 'slug' => 'javascript'],
                ['name' => 'PHP Scripts', 'slug' => 'php-scripts'],
                ['name' => 'WordPress', 'slug' => 'wordpress'],
                ['name' => 'HTML', 'slug' => 'html'],
                ['name' => 'Mobile', 'slug' => 'mobile'],
            ] as $c) {
                DB::table('categories')->insert(array_merge($c, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
        }

        // Default settings so the site works out of the box
        self::seedSetting('tags', [
            'React', 'Laravel', 'WordPress', 'Vue', 'PHP', 'HTML', 'SaaS', 'Dashboard', 'Next.js', 'Tailwind',
        ], 'taxonomy');

        self::seedSetting('nav_header', [
            ['label' => 'Browse', 'url' => '/search', 'open_new' => false],
            ['label' => 'Blog', 'url' => '/blog', 'open_new' => false],
            ['label' => 'Licenses', 'url' => '/pricing/licenses', 'open_new' => false],
        ], 'navigation');

        self::seedSetting('hero', [
            'title' => 'CodeBazaar',
            'subtitle' => 'Premium code, scripts & digital assets',
            'cta' => 'Search',
            'image' => '',
        ], 'homepage');

        self::seedSetting('licenses', [
            [
                'id' => 'regular',
                'name' => 'Regular License',
                'description' => '<p>Use in a single end product sold to one client.</p>',
                'price_label' => 'Included with item',
            ],
            [
                'id' => 'extended',
                'name' => 'Extended License',
                'description' => '<p>Use in an end product charged to end users (SaaS, etc.).</p>',
                'price_label' => 'Item extended price',
            ],
        ], 'commerce');
    }

    protected static function seedSetting(string $key, mixed $value, ?string $group = null): void
    {
        if (! DB::getSchemaBuilder()->hasTable('site_settings')) {
            return;
        }
        $exists = DB::table('site_settings')->where('key', $key)->exists();
        if ($exists) {
            return;
        }
        DB::table('site_settings')->insert([
            'key' => $key,
            'value' => json_encode($value),
            'group' => $group,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
