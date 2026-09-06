<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BlogAdminController extends Controller
{
    public function index()
    {
        $posts = BlogPost::orderByDesc('created_at')->paginate(30);
        return view('admin.blog.index', compact('posts'));
    }

    public function create()
    {
        return view('admin.blog.form', ['post' => new BlogPost]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['slug'] = $data['slug'] ?: Str::slug($data['title']);
        $data['author_id'] = auth()->id();
        if (($data['status'] ?? '') === 'published' && empty($data['published_at'])) {
            $data['published_at'] = now();
        }
        BlogPost::create($data);
        return redirect()->route('admin.blog.index')->with('success', 'Post created.');
    }

    public function edit(BlogPost $post)
    {
        return view('admin.blog.form', compact('post'));
    }

    public function update(Request $request, BlogPost $post)
    {
        $data = $this->validated($request);
        $data['slug'] = $data['slug'] ?: Str::slug($data['title']);
        if (($data['status'] ?? '') === 'published' && ! $post->published_at) {
            $data['published_at'] = now();
        }
        $post->update($data);
        return redirect()->route('admin.blog.index')->with('success', 'Post updated.');
    }

    public function destroy(BlogPost $post)
    {
        $post->delete();
        return back()->with('success', 'Post deleted.');
    }

    protected function validated(Request $request): array
    {
        return $request->validate([
            'title' => 'required|string|max:200',
            'slug' => 'nullable|string|max:200',
            'content' => 'nullable|string',
            'excerpt' => 'nullable|string',
            'cover_url' => 'nullable|url',
            'status' => 'required|in:draft,published',
            'seo_title' => 'nullable|string|max:200',
            'seo_description' => 'nullable|string|max:300',
            'category' => 'nullable|string|max:120',
        ]);
    }
}
