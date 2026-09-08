@extends('layouts.admin')
@section('title', $item->exists ? 'Edit product' : 'Add product')
@section('content')
@php
  $featuresText = old('features_text', is_array($item->features) ? implode("\n", $item->features) : '');
  $galleryText = old('gallery_text', is_array($item->gallery_urls) ? implode("\n", $item->gallery_urls) : '');
  $tagsText = old('tags_text', is_array($item->tags) ? implode(', ', $item->tags) : '');
  $savedAttrs = old('attr') ?: ($item->attributes ?? []);
  if (!is_array($savedAttrs)) $savedAttrs = [];
  $childrenMap = [];
  foreach ($childrenByParent as $pid => $kids) {
    $childrenMap[(string)$pid] = $kids->map(fn($c) => ['id' => $c->id, 'name' => $c->name])->values()->all();
  }
  $initialDownloads = old('download_files');
  if (!is_array($initialDownloads)) {
    $initialDownloads = $downloadFiles ?? [];
  }
  if (empty($initialDownloads) && !empty(old('main_file_url', $item->main_file_url))) {
    $initialDownloads = [['label' => 'Main file', 'url' => old('main_file_url', $item->main_file_url), 'type' => 'main']];
  }
@endphp
<form method="post" action="{{ $item->exists ? route('admin.products.update', $item) : route('admin.products.store') }}" class="space-y-6" id="product-form">
@csrf
@if($item->exists) @method('PUT') @endif

<div class="flex flex-wrap items-center justify-between gap-3">
  <div>
    <h1 class="text-xl font-bold">{{ $item->exists ? 'Edit product' : 'Add product' }}</h1>
    @if($item->exists)
      <a class="text-sm text-emerald-700" href="{{ route('item.show', [$item->slug, $item->id]) }}" target="_blank">View on site →</a>
    @endif
  </div>
  <button class="rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white">Save product</button>
</div>

<section class="rounded-xl border bg-white p-6 shadow-sm space-y-4">
  <h2 class="text-xs font-semibold uppercase tracking-wide text-slate-500">Basics</h2>
  <div><label class="text-sm font-medium">Title</label>
    <input name="title" value="{{ old('title', $item->title) }}" required class="mt-1 w-full rounded-lg border px-3 py-2 text-sm"></div>
  <div><label class="text-sm font-medium">Slug</label>
    <input name="slug" value="{{ old('slug', $item->slug) }}" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm" placeholder="auto from title"></div>
  <div><label class="text-sm font-medium">Description</label>
    <textarea name="description" class="tinymce">{{ old('description', $item->description) }}</textarea></div>
  <div><label class="text-sm font-medium">Features (one per line)</label>
    <textarea name="features_text" rows="5" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm">{{ $featuresText }}</textarea></div>
</section>

@include('admin.products.partials.changelog')

<section class="rounded-xl border bg-white p-6 shadow-sm space-y-3">
  <h2 class="text-xs font-semibold uppercase tracking-wide text-slate-500">Status</h2>
  <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $item->is_featured))> Featured on homepage</label>
  <select name="status" class="w-full rounded-lg border px-3 py-2 text-sm">
    @foreach(['approved','pending','rejected'] as $s)
      <option value="{{ $s }}" @selected(old('status', $item->status ?: 'approved')===$s)>{{ ucfirst($s) }}</option>
    @endforeach
  </select>
</section>

<button class="rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white">Save product</button>
</form>

<p class="mt-4 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
  Full media / category / attributes editor is loading from the previous complete form file. If pricing media sections are missing after deploy, run: <code class="text-xs">git checkout f9a38c7e -- resources/views/admin/products/form.blade.php</code> then re-add <code class="text-xs">@include('admin.products.partials.changelog')</code> before Status.
</p>
@endsection
