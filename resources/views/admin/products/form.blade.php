@extends('layouts.admin')
@section('title', $item->exists ? 'Edit product' : 'Add product')
@section('content')
@php
  $featuresText = old('features_text', is_array($item->features) ? implode("\n", $item->features) : '');
  $galleryText = old('gallery_text', is_array($item->gallery_urls) ? implode("\n", $item->gallery_urls) : '');
  $tagsText = old('tags_text', is_array($item->tags) ? implode(', ', $item->tags) : '');
  $attrs = old('attributes_json') ? json_decode(old('attributes_json'), true) : ($item->attributes ?? []);
@endphp
<form method="post" action="{{ $item->exists ? route('admin.products.update', $item) : route('admin.products.store') }}" class="space-y-6">
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
    <textarea name="features_text" rows="5" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm" placeholder="Responsive layout&#10;Dark mode">{{ $featuresText }}</textarea></div>
</section>

<section class="rounded-xl border bg-white p-6 shadow-sm space-y-4">
  <h2 class="text-xs font-semibold uppercase tracking-wide text-slate-500">Pricing</h2>
  <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_free" value="1" @checked(old('is_free', $item->is_free))> Free product</label>
  <div class="grid gap-4 sm:grid-cols-2">
    <div><label class="text-sm">Regular license price</label>
      <input type="number" step="0.01" name="regular_price" value="{{ old('regular_price', $item->regular_price ?? 49) }}" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm"></div>
    <div><label class="text-sm">Extended license price</label>
      <input type="number" step="0.01" name="extended_price" value="{{ old('extended_price', $item->extended_price ?? 249) }}" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm"></div>
    <div><label class="text-sm">Sale regular (optional)</label>
      <input type="number" step="0.01" name="sale_price_regular" value="{{ old('sale_price_regular', $item->sale_price_regular) }}" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm"></div>
    <div><label class="text-sm">Sale extended (optional)</label>
      <input type="number" step="0.01" name="sale_price_extended" value="{{ old('sale_price_extended', $item->sale_price_extended) }}" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm"></div>
  </div>
</section>

<section class="rounded-xl border bg-white p-6 shadow-sm space-y-4">
  <h2 class="text-xs font-semibold uppercase tracking-wide text-slate-500">Media & files</h2>
  <div><label class="text-sm">Thumbnail URL</label>
    <input name="thumbnail_url" value="{{ old('thumbnail_url', $item->thumbnail_url) }}" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm" placeholder="https://..."></div>
  <div><label class="text-sm">Screenshots (one image URL per line)</label>
    <textarea name="gallery_text" rows="4" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm">{{ $galleryText }}</textarea></div>
  <div><label class="text-sm">Live demo URL</label>
    <input name="demo_url" value="{{ old('demo_url', $item->demo_url) }}" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm"></div>
  <div><label class="text-sm">Main file download URL (ZIP / Drive / S3)</label>
    <input name="main_file_url" value="{{ old('main_file_url', $item->main_file_url) }}" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm"></div>
</section>

<section class="rounded-xl border bg-white p-6 shadow-sm space-y-4">
  <h2 class="text-xs font-semibold uppercase tracking-wide text-slate-500">Category, tags & attributes</h2>
  <div><label class="text-sm">Category</label>
    <select name="category_id" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm">
      <option value="">—</option>
      @foreach($categories as $c)
        <option value="{{ $c->id }}" @selected(old('category_id', $item->category_id)==$c->id)>{{ $c->name }}</option>
      @endforeach
    </select></div>
  <div><label class="text-sm">Tags (comma-separated)</label>
    <input name="tags_text" value="{{ $tagsText }}" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm" placeholder="React, Dashboard">
    <p class="mt-1 text-xs text-slate-500">Suggestions: {{ implode(', ', $tagPresets ?? []) }}</p>
  </div>
  <div><label class="text-sm">Attributes JSON (CodeCanyon-style key → values)</label>
    <textarea name="attributes_json" rows="8" class="mt-1 w-full rounded-lg border px-3 py-2 font-mono text-xs" placeholder='{"Compatible Browsers":["Chrome","Firefox"],"Files Included":["JavaScript JS","CSS"]}'>{{ old('attributes_json', !empty($attrs) ? json_encode($attrs, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES) : '') }}</textarea>
  </div>
</section>

<section class="rounded-xl border bg-white p-6 shadow-sm space-y-3">
  <h2 class="text-xs font-semibold uppercase tracking-wide text-slate-500">Status</h2>
  <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $item->is_featured))> Featured on homepage</label>
  <select name="status" class="w-full rounded-lg border px-3 py-2 text-sm">
    @foreach(['approved','pending','rejected'] as $s)
      <option value="{{ $s }}" @selected(old('status', $item->status ?: 'approved')===$s)>{{ ucfirst($s) }}</option>
    @endforeach
  </select>
  @if($item->exists)
    <p class="text-xs text-slate-500">Created: {{ $item->created_at }} · Updated: {{ $item->updated_at }} (automatic, not editable)</p>
  @endif
</section>

<button class="rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white">Save product</button>
</form>
@endsection
