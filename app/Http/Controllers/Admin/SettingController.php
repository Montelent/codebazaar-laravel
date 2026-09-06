<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function edit()
    {
        return view('admin.settings.edit', [
            'hero' => SiteSetting::getValue('hero', [
                'title' => 'CodeBazaar',
                'subtitle' => 'Premium code, scripts & digital assets',
                'cta' => 'Browse items',
                'image' => '',
            ]),
            'announcement' => SiteSetting::getValue('announcement', [
                'enabled' => false,
                'text' => '',
            ]),
            'footer' => SiteSetting::getValue('footer', [
                'about' => 'The marketplace for high-quality code, scripts, plugins, and digital assets.',
            ]),
            'colors' => SiteSetting::getValue('colors', [
                'primary' => '#059669',
                'secondary' => '#0f172a',
            ]),
            'seo' => SiteSetting::getValue('seo', [
                'title' => 'CodeBazaar',
                'description' => 'Digital marketplace',
            ]),
        ]);
    }

    public function update(Request $request)
    {
        SiteSetting::setValue('hero', [
            'title' => $request->input('hero_title'),
            'subtitle' => $request->input('hero_subtitle'),
            'cta' => $request->input('hero_cta'),
            'image' => $request->input('hero_image'),
        ], 'homepage');

        SiteSetting::setValue('announcement', [
            'enabled' => $request->boolean('announcement_enabled'),
            'text' => $request->input('announcement_text'),
        ], 'homepage');

        SiteSetting::setValue('footer', [
            'about' => $request->input('footer_about'),
        ], 'footer');

        SiteSetting::setValue('colors', [
            'primary' => $request->input('color_primary', '#059669'),
            'secondary' => $request->input('color_secondary', '#0f172a'),
        ], 'design');

        SiteSetting::setValue('seo', [
            'title' => $request->input('seo_title'),
            'description' => $request->input('seo_description'),
        ], 'seo');

        return back()->with('success', 'Settings saved.');
    }
}
