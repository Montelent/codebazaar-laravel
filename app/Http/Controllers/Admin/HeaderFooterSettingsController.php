<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\Request;

class HeaderFooterSettingsController extends Controller
{
    public function edit()
    {
        return view('admin.settings.header_footer', [
            'footer' => SiteSetting::getValue('footer', [
                'about' => 'The marketplace for high-quality code, scripts, plugins, and digital assets.',
                'columns' => [
                    ['title' => 'Explore', 'links' => [
                        ['label' => 'All items', 'url' => '/search'],
                        ['label' => 'Blog', 'url' => '/blog'],
                    ]],
                    ['title' => 'Support', 'links' => [
                        ['label' => 'Licenses', 'url' => '/pricing/licenses'],
                    ]],
                ],
                'social' => [
                    'twitter' => '',
                    'facebook' => '',
                    'github' => '',
                ],
            ]),
            'header' => SiteSetting::getValue('header', [
                'show_search' => true,
                'cta_label' => 'Join free',
                'cta_url' => '/register',
            ]),
            'codes' => SiteSetting::getValue('site_codes', [
                'head' => '',
                'body_start' => '',
                'body_end' => '',
            ]),
        ]);
    }

    public function update(Request $request)
    {
        $footer = json_decode($request->input('footer_json', '{}'), true);
        $header = json_decode($request->input('header_json', '{}'), true);
        if (! is_array($footer) || ! is_array($header)) {
            return back()->with('error', 'Invalid JSON.');
        }

        SiteSetting::setValue('footer', $footer, 'footer');
        SiteSetting::setValue('header', $header, 'header');

        // Verification / analytics / ad verification snippets
        SiteSetting::setValue('site_codes', [
            'head' => (string) $request->input('code_head', ''),
            'body_start' => (string) $request->input('code_body_start', ''),
            'body_end' => (string) $request->input('code_body_end', ''),
        ], 'codes');

        return back()->with('success', 'Header, footer & verification codes saved.');
    }
}
