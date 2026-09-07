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
      <p class="mt-1 text-xs text-slate-500">Create sub-categories under <a class="text-emerald-700" href="{{ route('admin.categories.index') }}">Categories</a> by setting a Parent.</p>
    </div>
  </div>

  {{-- Actual field saved --}}
  <input type="hidden" name="category_id" id="category_id" value="{{ old('category_id', $item->category_id) }}">

  <div>
    <label class="text-sm font-medium">Tags (comma-separated)</label>
    <input name="tags_text" value="{{ $tagsText }}" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm" placeholder="React, Dashboard">
    <p class="mt-1 text-xs text-slate-500">Suggestions: {{ implode(', ', $tagPresets ?? []) }}</p>
  </div>

  <div>
    <label class="text-sm font-medium">Attributes</label>
    <p class="text-xs text-slate-500">Options come from <a class="text-emerald-700" href="{{ route('admin.attributes.index') }}">Attributes</a> for the selected category (or its parent).</p>
    <div id="attr-picker" class="mt-3 space-y-4">
      <p class="text-sm text-slate-400" id="attr-empty">Select a category to load attributes.</p>
    </div>
  </div>

  <details class="rounded-lg border border-slate-200 p-3">
    <summary class="cursor-pointer text-sm font-medium text-slate-600">Advanced: attributes JSON</summary>
    <textarea name="attributes_json" id="attributes_json" rows="6" class="mt-2 w-full rounded-lg border px-3 py-2 font-mono text-xs" placeholder='{"Compatible Browsers":["Chrome","Firefox"]}'>{{ old('attributes_json', !empty($savedAttrs) ? json_encode($savedAttrs, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES) : '') }}</textarea>
  </details>
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
    <p class="text-xs text-slate-500">Created: {{ $item->created_at }} · Updated: {{ $item->updated_at }}</p>
  @endif
</section>

<button class="rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white">Save product</button>
</form>

@push('scripts')
<script>
(function () {
  const childrenMap = @json($childrenMap);
  const initialCategoryId = @json(old('category_id', $item->category_id));
  const initialParentId = @json(old('parent_hint', $selectedParentId));
  const savedAttrs = @json($savedAttrs);
  const attrUrlBase = @json(url('/admin/products/category-attributes'));

  const parentSel = document.getElementById('parent_category');
  const subSel = document.getElementById('sub_category');
  const categoryIdInput = document.getElementById('category_id');
  const picker = document.getElementById('attr-picker');
  const attrEmpty = document.getElementById('attr-empty');
  const jsonTa = document.getElementById('attributes_json');

  function fillSubs(parentId, selectedSubId) {
    subSel.innerHTML = '<option value="">— None (use parent) —</option>';
    const kids = childrenMap[String(parentId)] || [];
    kids.forEach(function (c) {
      const opt = document.createElement('option');
      opt.value = c.id;
      opt.textContent = c.name;
      if (selectedSubId && String(selectedSubId) === String(c.id)) opt.selected = true;
      subSel.appendChild(opt);
    });
  }

  function effectiveCategoryId() {
    if (subSel.value) return subSel.value;
    return parentSel.value || '';
  }

  function syncCategoryId() {
    categoryIdInput.value = effectiveCategoryId();
  }

  function isChecked(label, value) {
    const arr = savedAttrs[label];
    if (!arr) return false;
    if (Array.isArray(arr)) return arr.map(String).includes(String(value));
    return String(arr) === String(value);
  }

  function renderAttributes(attrs) {
    picker.innerHTML = '';
    if (!attrs || !Object.keys(attrs).length) {
      picker.innerHTML = '<p class="text-sm text-slate-400">No attributes defined for this category. Configure them under Admin → Attributes.</p>';
      return;
    }
    Object.keys(attrs).forEach(function (label) {
      const values = Array.isArray(attrs[label]) ? attrs[label] : [attrs[label]];
      const box = document.createElement('div');
      box.className = 'rounded-lg border border-slate-100 bg-slate-50 p-3';
      box.innerHTML = '<p class="text-sm font-semibold text-slate-700">' + label + '</p>';
      const row = document.createElement('div');
      row.className = 'mt-2 flex flex-wrap gap-3';
      values.forEach(function (val) {
        const id = 'attr_' + label.replace(/\W+/g, '_') + '_' + String(val).replace(/\W+/g, '_');
        const lab = document.createElement('label');
        lab.className = 'inline-flex items-center gap-1.5 text-sm text-slate-700';
        const checked = isChecked(label, val) ? ' checked' : '';
        lab.innerHTML = '<input type="checkbox" name="attr[' + label.replace(/"/g, '&quot;') + '][]" value="' +
          String(val).replace(/"/g, '&quot;') + '" class="attr-cb"' + checked + '> ' + val;
        row.appendChild(lab);
      });
      box.appendChild(row);
      picker.appendChild(box);
    });
  }

  function loadAttributes(catId) {
    if (!catId) {
      picker.innerHTML = '<p class="text-sm text-slate-400">Select a category to load attributes.</p>';
      return;
    }
    picker.innerHTML = '<p class="text-sm text-slate-400">Loading attributes…</p>';
    fetch(attrUrlBase + '/' + catId, {
      headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    })
      .then(function (r) { return r.json(); })
      .then(function (data) { renderAttributes(data.attributes || {}); })
      .catch(function () {
        picker.innerHTML = '<p class="text-sm text-red-600">Could not load attributes.</p>';
      });
  }

  parentSel.addEventListener('change', function () {
    fillSubs(parentSel.value, null);
    syncCategoryId();
    loadAttributes(effectiveCategoryId());
  });
  subSel.addEventListener('change', function () {
    syncCategoryId();
    loadAttributes(effectiveCategoryId());
  });

  // Init
  if (initialParentId) {
    parentSel.value = String(initialParentId);
    var subId = null;
    if (initialCategoryId && String(initialCategoryId) !== String(initialParentId)) {
      subId = initialCategoryId;
    }
    fillSubs(initialParentId, subId);
  }
  syncCategoryId();
  if (effectiveCategoryId()) {
    loadAttributes(effectiveCategoryId());
  }
})();
</script>
@endpush
@endsection
