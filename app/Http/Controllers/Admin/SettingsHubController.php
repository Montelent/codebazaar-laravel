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
            ['title' => 'File storage', 'desc' => 'Local, S3, Backblaze, iDrive, Drive URLs', 'route' => 'admin.settings.storage'],
            ['title' => 'Navigation', 'desc' => 'Header menu links builder', 'route' => 'admin.settings.navigation'],
            ['title' => 'Header & footer', 'desc' => 'Footer columns, about text, social links', 'route' => 'admin.settings.header_footer'],
            ['title' => 'Schema / JSON-LD', 'desc' => 'Organization & product schema toggles', 'route' => 'admin.settings.schema'],
            ['title' => 'Licenses', 'desc' => 'Regular / extended license copy', 'route' => 'admin.licenses.index'],
        ];

        return view('admin.settings.hub', compact('cards'));
    }
}
