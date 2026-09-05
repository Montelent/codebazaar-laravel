<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Models\Category;
use App\Models\Item;
use App\Models\SiteSetting;

class HomeController extends Controller
{
    public function index()
    {
        $featured = Item::approved()->where('is_featured', true)->latest()->take(8)->get();
        $latest = Item::approved()->latest()->take(12)->get();
        $categories = Category::query()->whereNull('parent_id')->withCount('items')->orderBy('name')->get();
        $blog = BlogPost::query()->where('status', 'published')->latest('published_at')->take(6)->get();
        $hero = SiteSetting::get('homepage.hero', [
            'title' => 'The marketplace for high-quality code',
            'subtitle' => 'Scripts, plugins, themes, and digital assets.',
        ]);

        return view('home.index', compact('featured', 'latest', 'categories', 'blog', 'hero'));
    }
}
