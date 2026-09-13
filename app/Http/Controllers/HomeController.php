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

        $browseCategories = SiteSetting::getValue('homepage_categories', self::defaultBrowseCategories());
        if (! is_array($browseCategories)) {
            $browseCategories = self::defaultBrowseCategories();
        }
        // Drop empty rows
        $browseCategories = array_values(array_filter($browseCategories, fn ($c) => ! empty($c['title'])));

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

    public static function defaultBrowseCategories(): array
    {
        return [
            ['icon' => '🟦', 'title' => 'WordPress', 'subtitle' => 'Themes, plugins & WooCommerce', 'url' => '/search?q=wordpress'],
            ['icon' => '🐘', 'title' => 'PHP Scripts', 'subtitle' => 'Laravel, CodeIgniter & scripts', 'url' => '/search?q=php'],
            ['icon' => '📱', 'title' => 'Mobile', 'subtitle' => 'React Native, Flutter & apps', 'url' => '/search?q=mobile'],
            ['icon' => '🌐', 'title' => 'HTML5', 'subtitle' => 'Landing pages & admin templates', 'url' => '/search?q=html'],
            ['icon' => '⚡', 'title' => 'JavaScript', 'subtitle' => 'React, Vue, Node & kits', 'url' => '/search?q=javascript'],
            ['icon' => '🔌', 'title' => 'Plugins', 'subtitle' => 'Browser, IDE & design plugins', 'url' => '/search?q=plugin'],
        ];
    }
}
