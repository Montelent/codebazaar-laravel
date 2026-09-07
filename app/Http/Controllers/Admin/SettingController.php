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
                'primary' => '#82b440',
                'primary_hover' => '#6f9a36',
                'secondary' => '#1b2838',
                'header_bg' => '#ffffff',
                'footer_bg' => '#1a1a1a',
                'footer_text' => '#b0b0b0',
                'announcement_bg' => '#2c3e50',
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

        $footer = SiteSetting::getValue('footer', []);
        if (! is_array($footer)) {
            $footer = [];
        }
        $footer['about'] = $request->input('footer_about');
        SiteSetting::setValue('footer', $footer, 'footer');

        $primary = $this->sanitizeHex($request->input('color_primary'), '#82b440');
        $primaryHover = $this->sanitizeHex($request->input('color_primary_hover'), $this->darkenHex($primary, 14));

        SiteSetting::setValue('colors', [
            'primary' => $primary,
            'primary_hover' => $primaryHover,
            'secondary' => $this->sanitizeHex($request->input('color_secondary'), '#1b2838'),
            'header_bg' => $this->sanitizeHex($request->input('color_header_bg'), '#ffffff'),
            'footer_bg' => $this->sanitizeHex($request->input('color_footer_bg'), '#1a1a1a'),
            'footer_text' => $this->sanitizeHex($request->input('color_footer_text'), '#b0b0b0'),
            'announcement_bg' => $this->sanitizeHex($request->input('color_announcement_bg'), '#2c3e50'),
        ], 'design');

        SiteSetting::setValue('seo', [
            'title' => $request->input('seo_title'),
            'description' => $request->input('seo_description'),
        ], 'seo');

        return redirect()->route('admin.settings.general')->with('success', 'General settings saved. Colors apply on the storefront immediately.');
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
            return '#6f9a36';
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
