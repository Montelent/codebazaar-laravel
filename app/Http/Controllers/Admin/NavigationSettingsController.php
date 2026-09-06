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
            ['label' => 'Home', 'url' => '/', 'open_new' => false],
            ['label' => 'Browse', 'url' => '/search', 'open_new' => false],
            ['label' => 'Blog', 'url' => '/blog', 'open_new' => false],
            ['label' => 'Licenses', 'url' => '/pricing/licenses', 'open_new' => false],
        ];
    }

    public function edit()
    {
        $items = SiteSetting::getValue('nav_header', self::defaults());

        return view('admin.settings.navigation', compact('items'));
    }

    public function update(Request $request)
    {
        $items = json_decode($request->input('items_json', '[]'), true);
        if (! is_array($items)) {
            return back()->with('error', 'Invalid navigation JSON.');
        }
        $clean = [];
        foreach ($items as $row) {
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
        SiteSetting::setValue('nav_header', $clean, 'navigation');

        return back()->with('success', 'Header navigation saved.');
    }
}
