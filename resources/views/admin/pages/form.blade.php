@extends('layouts.admin')
@section('title', $page->exists ? 'Edit page' : 'Add page')
@section('content')
<form method="post" action="{{ $page->exists ? route('admin.pages.update', $page) : route('admin.pages.store') }}" class="mx-auto max-w-3xl space-y-4">
@csrf @if($page->exists) @method('PUT') @endif
<div class="space-y-4 rounded-xl border bg-white p-6">
<div><label class="text-sm font-medium">Title</label><input name="title" value="{{ old('title', $page->title) }}" required class="mt-1 w-full rounded-lg border px-3 py-2 text-sm"></div>
<div><label class="text-sm font-medium">Slug</label><input name="slug" value="{{ old('slug', $page->slug) }}" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm"></div>
<div><label class="text-sm font-medium">Content</label><textarea name="content" class="tinymce">{{ old('content', $page->content) }}</textarea></div>
<div><label class="text-sm font-medium">Status</label>
<select name="status" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm">
<option value="draft" @selected(old('status', $page->status)==='draft')>Draft</option>
<option value="published" @selected(old('status', $page->status)==='published')>Published</option>
</select></div>
</div>

@include('components.seo-panel', ['model' => $page])

<button class="rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white">Save</button>
</form>
@endsection
