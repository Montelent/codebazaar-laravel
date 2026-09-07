<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\Request;

class NavigationSettingsController extends Controller
{
    public static function defaults(): array
    {
        return [
            ['label' => 'All items', 'url' => '/search', 'open_new' => false],
            ['label' => 'Blog', 'url' => '/blog', 'open_new' => false],
            ['label' => 'Licenses', 'url' => '/pricing/licenses', 'open_new' => false],
        ];
    }

    public function edit()
    {
        $legacy = SiteSetting::getValue('nav_header', self::defaults());
        if (! is_array($legacy)) {
            $legacy = self::defaults();
        }

        $desktop = SiteSetting::getValue('nav_desktop', $legacy);
        $mobile = SiteSetting::getValue('nav_mobile', $legacy);
        $options = SiteSetting::getValue('nav_options', [
            'desktop_show_categories' => true,
            'mobile_show_categories' => true,
            'mobile_show_account_links' => true,
        ]);

        return view('admin.settings.navigation', [
            'desktop' => is_array($desktop) ? $desktop : self::defaults(),
            'mobile' => is_array($mobile) ? $mobile : self::defaults(),
            'options' => is_array($options) ? $options : [],
        ]);
    }

    public function update(Request $request)
    {
        $desktop = $this->cleanLinks($request->input('desktop_json', '[]'));
        $mobile = $this->cleanLinks($request->input('mobile_json', '[]'));

        if ($desktop === null || $mobile === null) {
            return back()->with('error', 'Invalid menu JSON. Check your links and try again.');
        }

        SiteSetting::setValue('nav_desktop', $desktop, 'navigation');
        SiteSetting::setValue('nav_mobile', $mobile, 'navigation');
        // Keep legacy key in sync so older templates still work
        SiteSetting::setValue('nav_header', $desktop, 'navigation');

        SiteSetting::setValue('nav_options', [
            'desktop_show_categories' => $request->boolean('desktop_show_categories'),
            'mobile_show_categories' => $request->boolean('mobile_show_categories'),
            'mobile_show_account_links' => $request->boolean('mobile_show_account_links'),
        ], 'navigation');

        return back()->with('success', 'Desktop and mobile menus saved. Hard-refresh the storefront to see changes.');
    }

    /** @return array<int, array{label:string,url:string,open_new:bool}>|null */
    protected function cleanLinks(string $json): ?array
    {
        $items = json_decode($json, true);
        if (! is_array($items)) {
            return null;
        }

        $clean = [];
        foreach ($items as $row) {
            if (! is_array($row)) {
                continue;
            }
            $label = trim((string) ($row['label'] ?? ''));
            $url = trim((string) ($row['url'] ?? ''));
            if ($label === '' || $url === '') {
                continue;
            }
            $clean[] = [
                'label' => $label,
                'url' => $url,
                'open_new' => ! empty($row['open_new']),
            ];
        }

        return $clean;
    }
}
