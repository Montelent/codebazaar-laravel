<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Services\NewsletterService;
use App\Support\Seo;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BlogAdminController extends Controller
{
    public function index(Request $request)
    {
        $q = BlogPost::query();

        if ($request->filled('q')) {
            $term = trim((string) $request->input('q'));
            $q->where(function ($w) use ($term) {
                $w->where('title', 'like', "%{$term}%")
                    ->orWhere('slug', 'like', "%{$term}%")
                    ->orWhere('excerpt', 'like', "%{$term}%");
            });
        }

        if ($request->filled('status')) {
            $q->where('status', $request->input('status'));
        }

        if ($request->filled('category')) {
            $q->where('category', $request->input('category'));
        }

        if ($request->filled('from')) {
            $q->whereDate('created_at', '>=', $request->input('from'));
        }
        if ($request->filled('to')) {
            $q->whereDate('created_at', '<=', $request->input('to'));
        }

        $posts = $q->orderByDesc('created_at')->paginate(30)->withQueryString();

        $categories = BlogPost::query()
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        return view('admin.blog.index', compact('posts', 'categories'));
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
        $post = BlogPost::create($data);

        $msg = 'Post created.';
        $msg .= $this->maybeNotify($request, $post);

        return redirect()->route('admin.blog.index')->with('success', $msg);
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
        $post->refresh();

        $msg = 'Post updated.';
        $msg .= $this->maybeNotify($request, $post);

        return redirect()->route('admin.blog.index')->with('success', $msg);
    }

    public function destroy(BlogPost $post)
    {
        $post->delete();

        return back()->with('success', 'Post deleted.');
    }

    protected function maybeNotify(Request $request, BlogPost $post): string
    {
        if (! $request->boolean('notify_subscribers')) {
            return '';
        }
        if (($post->status ?? '') !== 'published') {
            return ' (notify skipped — post is not published).';
        }

        $audience = $request->input('notify_audience', 'newsletter');
        if (! in_array($audience, ['newsletter', 'all', 'verified', 'first_100_newsletter', 'first_100_all'], true)) {
            $audience = 'newsletter';
        }

        try {
            $result = NewsletterService::notifyPost($post, $audience, $request->user()->id);

            return " Newsletter sent to {$result['sent']} recipient(s)".($result['errors'] ? ", {$result['errors']} failed" : '').'.';
        } catch (\Throwable $e) {
            return ' Newsletter failed: '.$e->getMessage();
        }
    }

    protected function validated(Request $request): array
    {
        return $request->validate(array_merge([
            'title' => 'required|string|max:200',
            'slug' => 'nullable|string|max:200',
            'content' => 'nullable|string',
            'excerpt' => 'nullable|string',
            'cover_url' => 'nullable|string|max:1000',
            'status' => 'required|in:draft,published',
            'category' => 'nullable|string|max:120',
        ], Seo::rules()));
    }
}
