<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\HomeController;
use App\Models\SiteSetting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function edit()
    {
        return view('admin.settings.edit', [
            'hero' => SiteSetting::getValue('hero', [
                'eyebrow' => 'CodeBazaar',
                'title' => 'Code that powers',
                'title_highlight' => 'your next development',
                'subtitle' => 'Discover premium scripts, themes, plugins, and templates from world-class independent creator.',
                'cta' => 'Search',
                'image' => '',
            ]),
            'browseCategories' => SiteSetting::getValue('homepage_categories', HomeController::defaultBrowseCategories()),
            'announcement' => SiteSetting::getValue('announcement', [
                'enabled' => false,
                'text' => '',
            ]),
            'footer' => SiteSetting::getValue('footer', [
                'about' => 'The marketplace for high-quality code, scripts, plugins, and digital assets.',
            ]),
            'colors' => SiteSetting::getValue('colors', [
                'primary' => '#e11d2e',
                'primary_hover' => '#c1121f',
                'secondary' => '#0b1220',
                'header_bg' => '#ffffff',
                'footer_bg' => '#0b1220',
                'footer_text' => '#94a3b8',
                'announcement_bg' => '#0b1220',
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
            'eyebrow' => $request->input('hero_eyebrow', 'CodeBazaar'),
            'title' => $request->input('hero_title'),
            'title_highlight' => $request->input('hero_title_highlight'),
            'subtitle' => $request->input('hero_subtitle'),
            'cta' => $request->input('hero_cta'),
            'image' => $request->input('hero_image'),
        ], 'homepage');

        $icons = $request->input('cat_icon', []);
        $titles = $request->input('cat_title', []);
        $subs = $request->input('cat_subtitle', []);
        $urls = $request->input('cat_url', []);
        $cards = [];
        $count = max(count($icons), count($titles), count($subs), count($urls));
        for ($i = 0; $i < $count; $i++) {
            $title = trim((string) ($titles[$i] ?? ''));
            if ($title === '') {
                continue;
            }
            $cards[] = [
                'icon' => trim((string) ($icons[$i] ?? '')),
                'title' => $title,
                'subtitle' => trim((string) ($subs[$i] ?? '')),
                'url' => trim((string) ($urls[$i] ?? '#')) ?: '#',
            ];
        }
        SiteSetting::setValue('homepage_categories', $cards, 'homepage');

        SiteSetting::setValue('announcement', [
            'enabled' => $request->boolean('announcement_enabled'),
            'text' => $request->input('announcement_text'),
        ], 'homepage');

        $footer = SiteSetting::getValue('footer', []);
        if (! is_array($footer)) {
            $footer = [];
        }
        $footer['about'] = $request->input('footer_about');
        SiteSetting::setValue('footer', $footer, 'footer');

        $primary = $this->sanitizeHex($request->input('color_primary'), '#e11d2e');
        $primaryHover = $this->sanitizeHex($request->input('color_primary_hover'), $this->darkenHex($primary, 14));

        SiteSetting::setValue('colors', [
            'primary' => $primary,
            'primary_hover' => $primaryHover,
            'secondary' => $this->sanitizeHex($request->input('color_secondary'), '#0b1220'),
            'header_bg' => $this->sanitizeHex($request->input('color_header_bg'), '#ffffff'),
            'footer_bg' => $this->sanitizeHex($request->input('color_footer_bg'), '#0b1220'),
            'footer_text' => $this->sanitizeHex($request->input('color_footer_text'), '#94a3b8'),
            'announcement_bg' => $this->sanitizeHex($request->input('color_announcement_bg'), '#0b1220'),
        ], 'design');

        SiteSetting::setValue('seo', [
            'title' => $request->input('seo_title'),
            'description' => $request->input('seo_description'),
        ], 'seo');

        return redirect()->route('admin.settings.general')->with('success', 'General settings saved. Homepage & colors apply immediately (hard-refresh storefront).');
    }

    protected function sanitizeHex(?string $value, string $fallback): string
    {
        $value = trim((string) $value);
        if (preg_match('/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/', $value)) {
            return strtolower($value);
        }

        return $fallback;
    }

    protected function darkenHex(string $hex, int $percent = 12): string
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }
        if (strlen($hex) !== 6) {
            return '#c1121f';
        }
        $factor = max(0, min(100, $percent)) / 100;
        $out = '';
        for ($i = 0; $i < 6; $i += 2) {
            $c = hexdec(substr($hex, $i, 2));
            $c = (int) max(0, round($c * (1 - $factor)));
            $out .= str_pad(dechex($c), 2, '0', STR_PAD_LEFT);
        }

        return '#'.$out;
    }
}
