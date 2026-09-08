@extends('layouts.admin')
@section('title', $post->exists ? 'Edit post' : 'New post')
@section('content')
<form method="post" action="{{ $post->exists ? route('admin.blog.update', $post) : route('admin.blog.store') }}" class="mx-auto max-w-3xl space-y-4">
@csrf @if($post->exists) @method('PUT') @endif
<div class="space-y-4 rounded-xl border bg-white p-6">
<div><label class="text-sm font-medium">Title</label><input name="title" value="{{ old('title', $post->title) }}" required class="mt-1 w-full rounded-lg border px-3 py-2 text-sm"></div>
<div><label class="text-sm font-medium">Slug</label><input name="slug" value="{{ old('slug', $post->slug) }}" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm"></div>
<div><label class="text-sm font-medium">Content</label><textarea name="content" class="tinymce mt-1 w-full">{{ old('content', $post->content) }}</textarea></div>
<div><label class="text-sm font-medium">Excerpt</label><textarea name="excerpt" rows="2" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm">{{ old('excerpt', $post->excerpt) }}</textarea></div>
<div class="grid gap-4 sm:grid-cols-2">
<div><label class="text-sm font-medium">Cover URL</label><input name="cover_url" value="{{ old('cover_url', $post->cover_url) }}" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm"></div>
<div><label class="text-sm font-medium">Category</label><input name="category" value="{{ old('category', $post->category) }}" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm"></div>
</div>
<div><label class="text-sm font-medium">Status</label>
<select name="status" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm">
<option value="draft" @selected(old('status', $post->status)==='draft')>Draft</option>
<option value="published" @selected(old('status', $post->status)==='published')>Published</option>
</select></div>
</div>

@include('components.seo-panel', ['model' => $post])

{{-- Notify subscribers --}}
<section class="rounded-xl border border-sky-100 bg-sky-50 p-5 space-y-3">
  <label class="flex items-start gap-2 text-sm font-medium text-sky-900">
    <input type="checkbox" name="notify_subscribers" value="1" class="mt-0.5" @checked(old('notify_subscribers'))>
    <span>Email subscribers / users about this post after save<br>
      <span class="font-normal text-sky-800/80">Only sends when status is <strong>Published</strong>. Uses your SMTP settings.</span>
    </span>
  </label>
  <div>
    <label class="text-xs font-medium text-sky-900">Audience</label>
    <select name="notify_audience" class="mt-1 w-full max-w-md rounded-lg border border-sky-200 bg-white px-3 py-2 text-sm">
      <option value="newsletter" @selected(old('notify_audience', 'newsletter') === 'newsletter')>Newsletter opt-in only</option>
      <option value="verified" @selected(old('notify_audience') === 'verified')>Email-verified users</option>
      <option value="all" @selected(old('notify_audience') === 'all')>All registered users</option>
      <option value="first_100_newsletter" @selected(old('notify_audience') === 'first_100_newsletter')>First 100 newsletter subscribers</option>
      <option value="first_100_all" @selected(old('notify_audience') === 'first_100_all')>First 100 users (most recent)</option>
    </select>
  </div>
</section>

<button class="rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white">Save</button>
</form>
@endsection
