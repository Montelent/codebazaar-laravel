<?php

namespace App\Providers;

use App\Http\Controllers\Admin\SmtpSettingsController;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        /*
         | Pretty URLs (default): https://yoursite.com/admin
         | Only set FORCE_INDEX_PHP=true in .env if Hostinger rewrite is broken.
         */
        $forceIndexPhp = filter_var(env('FORCE_INDEX_PHP', false), FILTER_VALIDATE_BOOLEAN);

        $root = rtrim((string) config('app.url'), '/');
        if ($root === '') {
            $root = rtrim(request()->getSchemeAndHttpHost(), '/');
        }

        // Always strip /index.php from APP_URL for clean link generation
        $root = preg_replace('#/index\.php$#i', '', $root) ?: $root;

        if ($forceIndexPhp) {
            URL::forceRootUrl($root.'/index.php');
        } else {
            URL::forceRootUrl($root);
        }

        if (str_starts_with($root, 'https://') || filter_var(env('FORCE_HTTPS', true), FILTER_VALIDATE_BOOLEAN)) {
            URL::forceScheme('https');
        }

        // Runtime SMTP from Admin → Settings → SMTP
        try {
            SmtpSettingsController::applyConfig();
        } catch (\Throwable) {
            // DB may not be ready during install / migrate
        }
    }
}
