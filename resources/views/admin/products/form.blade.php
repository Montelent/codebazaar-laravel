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

{{-- Media section restored from complete form via partial includes to keep this file manageable --}}
@include('admin.products.partials.media')

<section class="rounded-xl border bg-white p-6 shadow-sm space-y-4">
  <h2 class="text-xs font-semibold uppercase tracking-wide text-slate-500">Category, tags & attributes</h2>
  <div class="grid gap-4 sm:grid-cols-2">
    <div>
      <label class="text-sm font-medium">Parent category</label>
      <select id="parent_category" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm">
        <option value="">— Select —</option>
        @foreach($parents as $p)
          <option value="{{ $p->id }}" @selected((string)old('parent_hint', $selectedParentId) === (string)$p->id)>{{ $p->name }}</option>
        @endforeach
      </select>
    </div>
    <div>
      <label class="text-sm font-medium">Sub-category <span class="font-normal text-slate-400">(optional)</span></label>
      <select id="sub_category" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm">
        <option value="">— None (use parent) —</option>
      </select>
    </div>
  </div>
  <input type="hidden" name="category_id" id="category_id" value="{{ old('category_id', $item->category_id) }}">
  <div>
    <label class="text-sm font-medium">Tags (comma-separated)</label>
    <input name="tags_text" value="{{ $tagsText }}" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm">
  </div>
  <div>
    <label class="text-sm font-medium">Attributes</label>
    <div id="attr-picker" class="mt-3 space-y-4">
      <p class="text-sm text-slate-400">Select a category to load attributes.</p>
    </div>
  </div>
  <details class="rounded-lg border border-slate-200 p-3">
    <summary class="cursor-pointer text-sm font-medium text-slate-600">Advanced: attributes JSON</summary>
    <textarea name="attributes_json" id="attributes_json" rows="6" class="mt-2 w-full rounded-lg border px-3 py-2 font-mono text-xs">{{ old('attributes_json', !empty($savedAttrs) ? json_encode($savedAttrs, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES) : '') }}</textarea>
  </details>
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

@include('admin.products.partials.form-scripts')
@endsection
