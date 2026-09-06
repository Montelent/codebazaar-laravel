<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\SiteSetting;
use Illuminate\Http\Request;

class AttributeController extends Controller
{
    /** Default CodeCanyon-style presets by category slug. */
    public static function defaults(): array
    {
        return [
            'wordpress' => [
                'Compatible Browsers' => ['Chrome', 'Firefox', 'Safari', 'Edge', 'Opera'],
                'Compatible With' => ['Gutenberg Editor', 'Elementor', 'WPBakery', 'WooCommerce 8.x'],
                'Software Version' => ['WordPress 6.4+', 'WordPress 6.0 - 6.3'],
                'Files Included' => ['PHP Files', 'CSS Files', 'JS Files', 'PSD'],
                'Columns' => ['4+', '3', '2', '1'],
                'Layout' => ['Responsive', 'Fluid', 'Fixed'],
                'Widget Ready' => ['Yes', 'No'],
            ],
            'php-scripts' => [
                'Software Framework' => ['Laravel', 'CodeIgniter', 'Core PHP', 'Symfony'],
                'Software Version' => ['PHP 8.2+', 'PHP 8.1', 'PHP 8.0', 'PHP 7.4'],
                'Database' => ['MySQL 8+', 'MySQL 5.7', 'PostgreSQL', 'SQLite'],
                'Files Included' => ['PHP Files', 'JavaScript JS', 'CSS', 'HTML'],
            ],
            'javascript' => [
                'Compatible Browsers' => ['Chrome', 'Firefox', 'Safari', 'Edge'],
                'Software Framework' => ['React', 'Vue', 'Angular', 'Svelte', 'Next.js', 'Nuxt'],
                'Files Included' => ['JavaScript JS', 'TypeScript', 'CSS', 'JSON'],
                'Uses Libraries' => ['React', 'Vue', 'jQuery', 'Tailwind'],
            ],
            'html' => [
                'Compatible Browsers' => ['Chrome', 'Firefox', 'Safari', 'Edge'],
                'Files Included' => ['HTML', 'CSS', 'JS', 'PSD'],
                'Layout' => ['Responsive', 'Bootstrap 5', 'Tailwind'],
            ],
            'mobile' => [
                'Software Framework' => ['Flutter', 'React Native', 'Ionic', 'Swift', 'Kotlin'],
                'Files Included' => ['Dart', 'JavaScript JS', 'Java', 'Swift'],
            ],
        ];
    }

    public function index(Request $request)
    {
        $categories = Category::orderBy('name')->get();
        $slug = $request->get('category', $categories->first()?->slug ?? 'wordpress');
        $all = SiteSetting::getValue('category_attributes', self::defaults());
        $attrs = $all[$slug] ?? ($all[str_replace('_', '-', $slug)] ?? []);
        if (! is_array($attrs)) {
            $attrs = [];
        }

        return view('admin.attributes.index', [
            'categories' => $categories,
            'categorySlug' => $slug,
            'attrs' => $attrs,
            'defaults' => self::defaults(),
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'category_slug' => 'required|string|max:120',
            'attrs_json' => 'nullable|string',
        ]);

        $decoded = json_decode($data['attrs_json'] ?? '{}', true);
        if (! is_array($decoded)) {
            return back()->with('error', 'Invalid JSON for attributes.');
        }

        // Normalize: label => array of string values
        $clean = [];
        foreach ($decoded as $label => $values) {
            $label = trim((string) $label);
            if ($label === '') {
                continue;
            }
            if (is_array($values)) {
                $clean[$label] = array_values(array_filter(array_map('strval', $values), fn ($v) => trim($v) !== ''));
            } else {
                $clean[$label] = [trim((string) $values)];
            }
        }

        $all = SiteSetting::getValue('category_attributes', self::defaults());
        if (! is_array($all)) {
            $all = self::defaults();
        }
        $all[$data['category_slug']] = $clean;
        SiteSetting::setValue('category_attributes', $all, 'taxonomy');

        return redirect()
            ->route('admin.attributes.index', ['category' => $data['category_slug']])
            ->with('success', 'Attributes saved for category “'.$data['category_slug'].'”.');
    }

    public function reset(Request $request)
    {
        $slug = $request->validate(['category_slug' => 'required|string'])['category_slug'];
        $all = SiteSetting::getValue('category_attributes', self::defaults());
        $defaults = self::defaults();
        $all[$slug] = $defaults[$slug] ?? $defaults['javascript'] ?? [];
        SiteSetting::setValue('category_attributes', $all, 'taxonomy');

        return redirect()
            ->route('admin.attributes.index', ['category' => $slug])
            ->with('success', 'Reset to defaults.');
    }
}
