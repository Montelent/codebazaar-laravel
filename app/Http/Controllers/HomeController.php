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
        $featured = Item::approved()->where('is_featured', true)->with('author')->latest()->take(8)->get();
        $latest = Item::approved()->with('author')->latest()->take(12)->get();
        $popular = Item::approved()->with('author')->orderByDesc('sales_count')->take(8)->get();

        $categories = Category::query()
            ->whereNull('parent_id')
            ->withCount(['items' => fn ($q) => $q->where('status', 'approved')])
            ->orderBy('name')
            ->get();

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

        $hero = SiteSetting::getValue('homepage.hero', SiteSetting::getValue('hero', [
            'title' => 'Discover thousands of code scripts & plugins',
            'subtitle' => 'PHP scripts, JavaScript, WordPress, mobile apps and more from independent authors.',
            'cta' => 'Search',
        ]));

        return view('home.index', compact('featured', 'latest', 'popular', 'categories', 'blog', 'hero'));
    }
}
