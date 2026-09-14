<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Models\Category;
use App\Models\Item;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\Schema;

class HomeController extends Controller
{
    public function index()
    {
        $featured = Item::approved()->where('is_featured', true)->with(['author', 'category'])->latest()->take(8)->get();
        $latest = Item::approved()->with(['author', 'category'])->latest()->take(12)->get();
        $popular = Item::approved()->with(['author', 'category'])->orderByDesc('sales_count')->take(8)->get();

        $categories = Category::query()
            ->whereNull('parent_id')
            ->withCount(['items' => fn ($q) => $q->where('status', 'approved')])
            ->orderBy('name')
            ->get();

        $browseCategories = SiteSetting::getValue('homepage_categories', null);
        if (! is_array($browseCategories) || count($browseCategories) === 0) {
            $browseCategories = self::defaultBrowseCategories();
        }
        // Drop empty rows
        $browseCategories = array_values(array_filter($browseCategories, fn ($c) => ! empty($c['title'])));

        // Migrate legacy search URLs → category routes when slug matches a category
        $slugMap = $categories->keyBy(fn ($c) => strtolower($c->slug));
        $browseCategories = array_map(function ($card) use ($slugMap) {
            $url = (string) ($card['url'] ?? '');
            if (preg_match('#/search\?q=([^&]+)#i', $url, $m)) {
                $q = strtolower(urldecode($m[1]));
                if ($slugMap->has($q)) {
                    $card['url'] = route('category', $slugMap[$q]->slug);
                } elseif ($slugMap->has(str_replace(' ', '-', $q))) {
                    $card['url'] = route('category', $slugMap[str_replace(' ', '-', $q)]->slug);
                }
            }

            return $card;
        }, $browseCategories);

        $blog = collect();
        try {
            if (Schema::hasTable('blog_posts')) {
                $blog = BlogPost::query()
                    ->where('status', 'published')
                    ->orderByDesc('published_at')
                    ->orderByDesc('id')
                    ->take(6)
                    ->get();
            }
        } catch (\Throwable $e) {
            $blog = collect();
        }

        $hero = SiteSetting::getValue('hero', [
            'eyebrow' => 'CodeBazaar',
            'title' => 'Code that powers',
            'title_highlight' => 'your next development',
            'subtitle' => 'Discover premium scripts, themes, plugins, and templates from world-class independent creator.',
            'cta' => 'Search',
        ]);

        return view('home.index', compact(
            'featured',
            'latest',
            'popular',
            'categories',
            'browseCategories',
            'blog',
            'hero'
        ));
    }

    /**
     * Prefer real categories from DB; fall back to placeholders with category-style URLs.
     */
    public static function defaultBrowseCategories(): array
    {
        try {
            $cats = Category::query()
                ->whereNull('parent_id')
                ->orderBy('name')
                ->take(6)
                ->get();

            if ($cats->count() > 0) {
                $icons = ['🟦', '🐘', '📱', '🌐', '⚡', '🔌', '🎨', '📦'];
                $out = [];
                foreach ($cats as $i => $cat) {
                    $out[] = [
                        'icon' => $icons[$i % count($icons)],
                        'title' => $cat->name,
                        'subtitle' => $cat->description
                            ? \Illuminate\Support\Str::limit(strip_tags((string) $cat->description), 60)
                            : 'Browse '.$cat->name,
                        'url' => route('category', $cat->slug),
                    ];
                }

                return $out;
            }
        } catch (\Throwable $e) {
            // installer / missing table
        }

        return [
            ['icon' => '🟦', 'title' => 'WordPress', 'subtitle' => 'Themes, plugins & WooCommerce', 'url' => '/category/wordpress'],
            ['icon' => '🐘', 'title' => 'PHP Scripts', 'subtitle' => 'Laravel, CodeIgniter & scripts', 'url' => '/category/php-scripts'],
            ['icon' => '📱', 'title' => 'Mobile', 'subtitle' => 'React Native, Flutter & apps', 'url' => '/category/mobile'],
            ['icon' => '🌐', 'title' => 'HTML5', 'subtitle' => 'Landing pages & admin templates', 'url' => '/category/html5'],
            ['icon' => '⚡', 'title' => 'JavaScript', 'subtitle' => 'React, Vue, Node & kits', 'url' => '/category/javascript'],
            ['icon' => '🔌', 'title' => 'Plugins', 'subtitle' => 'Browser, IDE & design plugins', 'url' => '/category/plugins'],
        ];
    }
}
