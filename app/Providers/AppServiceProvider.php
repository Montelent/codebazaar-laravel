<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // Shared hosting without working Apache rewrite: all generated links use /index.php/...
        $force = filter_var(env('FORCE_INDEX_PHP', true), FILTER_VALIDATE_BOOLEAN);

        $root = rtrim((string) config('app.url'), '/');
        if ($root === '') {
            $root = rtrim(request()->getSchemeAndHttpHost(), '/');
        }

        // Strip trailing /index.php then re-apply if forced
        $root = preg_replace('#/index\.php$#i', '', $root) ?: $root;

        if ($force) {
            URL::forceRootUrl($root.'/index.php');
        } else {
            URL::forceRootUrl($root);
        }

        if (str_starts_with($root, 'https://') || filter_var(env('FORCE_HTTPS', true), FILTER_VALIDATE_BOOLEAN)) {
            URL::forceScheme('https');
        }
    }
}
