<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Support\Seo;

class BlogController extends Controller
{
    public function index()
    {
        $posts = BlogPost::where('status', 'published')
            ->orderByDesc('published_at')
            ->orderByDesc('created_at')
            ->paginate(12);

        $seo = Seo::make([
            'title' => 'Blog',
            'description' => 'News, guides and updates from '.Seo::siteName().'.',
            'canonical' => route('blog.index'),
        ]);

        return view('blog.index', compact('posts', 'seo'));
    }

    public function show(string $slug)
    {
        $post = BlogPost::where('slug', $slug)->where('status', 'published')->firstOrFail();
        $seo = Seo::make($post->seoPayload());

        return view('blog.show', compact('post', 'seo'));
    }
}
