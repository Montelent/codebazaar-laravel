@extends('layouts.admin')
@section('title', $post->exists ? 'Edit post' : 'New post')
@section('content')
<form method="post" action="{{ $post->exists ? route('admin.blog.update', $post) : route('admin.blog.store') }}" class="mx-auto max-w-3xl space-y-4 rounded-xl border bg-white p-6">
@csrf @if($post->exists) @method('PUT') @endif
<div><label class="text-sm font-medium">Title</label><input name="title" value="{{ old('title', $post->title) }}" required class="mt-1 w-full rounded-lg border px-3 py-2 text-sm"></div>
<div><label class="text-sm font-medium">Slug</label><input name="slug" value="{{ old('slug', $post->slug) }}" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm"></div>
<div><label class="text-sm font-medium">Content</label><textarea name="content" class="tinymce mt-1 w-full">{{ old('content', $post->content) }}</textarea></div>
<div><label class="text-sm font-medium">Excerpt</label><textarea name="excerpt" rows="2" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm">{{ old('excerpt', $post->excerpt) }}</textarea></div>
<div class="grid gap-4 sm:grid-cols-2">
<div><label class="text-sm font-medium">Cover URL</label><input name="cover_url" value="{{ old('cover_url', $post->cover_url) }}" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm"></div>
<div><label class="text-sm font-medium">Category</label><input name="category" value="{{ old('category', $post->category) }}" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm"></div>
</div>
<div class="grid gap-4 sm:grid-cols-2">
<div><label class="text-sm font-medium">SEO title</label><input name="seo_title" value="{{ old('seo_title', $post->seo_title) }}" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm"></div>
<div><label class="text-sm font-medium">SEO description</label><input name="seo_description" value="{{ old('seo_description', $post->seo_description) }}" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm"></div>
</div>
<div><label class="text-sm font-medium">Status</label>
<select name="status" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm">
<option value="draft" @selected(old('status', $post->status)==='draft')>Draft</option>
<option value="published" @selected(old('status', $post->status)==='published')>Published</option>
</select></div>
<button class="rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white">Save</button>
</form>
@endsection
