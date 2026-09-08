<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class SettingsHubController extends Controller
{
    public function index()
    {
        $cards = [
            ['title' => 'General & homepage', 'desc' => 'Hero, announcement, colors, default SEO', 'route' => 'admin.settings.general'],
            ['title' => 'Payments', 'desc' => 'Stripe, Paystack, Monnify, crypto wallets', 'route' => 'admin.settings.payments'],
            ['title' => 'SMTP / Email', 'desc' => 'Outgoing mail, Hostinger SMTP, test email', 'route' => 'admin.settings.smtp'],
            ['title' => 'File storage', 'desc' => 'Local, S3, Backblaze, iDrive, Drive URLs', 'route' => 'admin.settings.storage'],
            ['title' => 'Menus', 'desc' => 'Desktop & mobile navigation links', 'route' => 'admin.settings.navigation'],
            ['title' => 'Header & footer', 'desc' => 'Footer, social, verification & tracking codes', 'route' => 'admin.settings.header_footer'],
            ['title' => 'Ad placements', 'desc' => 'Banners on blog, product, footer, homepage', 'route' => 'admin.settings.ads'],
            ['title' => 'Schema / JSON-LD', 'desc' => 'Organization & product schema toggles', 'route' => 'admin.settings.schema'],
            ['title' => 'Licenses', 'desc' => 'Regular / extended license copy', 'route' => 'admin.licenses.index'],
        ];

        return view('admin.settings.hub', compact('cards'));
    }
}
