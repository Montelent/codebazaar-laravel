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

<section class="rounded-xl border bg-white p-6 shadow-sm space-y-6">
  <div class="flex flex-wrap items-center justify-between gap-2">
    <h2 class="text-xs font-semibold uppercase tracking-wide text-slate-500">Media & files</h2>
    <a href="{{ route('admin.settings.storage') }}" class="text-xs text-emerald-700">Storage settings →</a>
  </div>

  {{-- Thumbnail --}}
  <div class="rounded-lg border border-slate-100 p-4" data-media-field="thumbnail">
    <label class="text-sm font-medium">Thumbnail</label>
    <div class="mt-2 flex flex-wrap gap-2 text-xs">
      <button type="button" class="media-tab rounded-full border px-3 py-1" data-tab="url">External / Drive URL</button>
      <button type="button" class="media-tab rounded-full border px-3 py-1" data-tab="upload">Upload</button>
      <button type="button" class="media-tab rounded-full border px-3 py-1" data-tab="library">Library</button>
    </div>
    <div class="media-pane mt-3" data-pane="url">
      <input name="thumbnail_url" id="thumbnail_url" value="{{ old('thumbnail_url', $item->thumbnail_url) }}" class="w-full rounded-lg border px-3 py-2 text-sm" placeholder="https://… or Google Drive share link">
    </div>
    <div class="media-pane mt-3 hidden" data-pane="upload">
      <div class="flex flex-wrap items-end gap-2">
        <div class="flex-1 min-w-[140px]">
          <label class="text-xs">Disk</label>
          <select class="media-disk mt-1 w-full rounded-lg border px-2 py-2 text-sm">
            <option value="local">Local</option>
            <option value="s3">Amazon S3</option>
            <option value="backblaze">Backblaze B2</option>
            <option value="idrive">iDrive e2</option>
          </select>
        </div>
        <div class="flex-[2] min-w-[180px]">
          <label class="text-xs">File</label>
          <input type="file" accept="image/*" class="media-file mt-1 block w-full text-sm">
        </div>
        <button type="button" class="media-upload-btn rounded-lg bg-slate-800 px-3 py-2 text-sm font-medium text-white">Upload</button>
      </div>
      <p class="media-status mt-1 text-xs text-slate-500"></p>
    </div>
    <div class="media-pane mt-3 hidden" data-pane="library">
      <button type="button" class="media-pick-btn rounded-lg border px-3 py-2 text-sm">Pick from media library</button>
    </div>
    <img id="thumbnail_preview" src="{{ old('thumbnail_url', $item->thumbnail_url) }}" alt="" class="mt-3 h-24 rounded-lg border object-cover {{ old('thumbnail_url', $item->thumbnail_url) ? '' : 'hidden' }}">
  </div>

  {{-- Screenshots --}}
  <div class="rounded-lg border border-slate-100 p-4" data-media-field="gallery">
    <label class="text-sm font-medium">Screenshots</label>
    <p class="text-xs text-slate-500">One image URL per line (or upload / pick multiple).</p>
    <div class="mt-2 flex flex-wrap gap-2 text-xs">
      <button type="button" class="media-tab rounded-full border px-3 py-1" data-tab="url">External / Drive URLs</button>
      <button type="button" class="media-tab rounded-full border px-3 py-1" data-tab="upload">Upload</button>
      <button type="button" class="media-tab rounded-full border px-3 py-1" data-tab="library">Library</button>
    </div>
    <div class="media-pane mt-3" data-pane="url">
      <textarea name="gallery_text" id="gallery_text" rows="4" class="w-full rounded-lg border px-3 py-2 text-sm">{{ $galleryText }}</textarea>
    </div>
    <div class="media-pane mt-3 hidden" data-pane="upload">
      <div class="flex flex-wrap items-end gap-2">
        <div class="flex-1 min-w-[140px]">
          <label class="text-xs">Disk</label>
          <select class="media-disk mt-1 w-full rounded-lg border px-2 py-2 text-sm">
            <option value="local">Local</option>
            <option value="s3">Amazon S3</option>
            <option value="backblaze">Backblaze B2</option>
            <option value="idrive">iDrive e2</option>
          </select>
        </div>
        <div class="flex-[2] min-w-[180px]">
          <label class="text-xs">Images</label>
          <input type="file" accept="image/*" multiple class="media-file mt-1 block w-full text-sm">
        </div>
        <button type="button" class="media-upload-btn rounded-lg bg-slate-800 px-3 py-2 text-sm font-medium text-white">Upload</button>
      </div>
      <p class="media-status mt-1 text-xs text-slate-500"></p>
    </div>
    <div class="media-pane mt-3 hidden" data-pane="library">
      <button type="button" class="media-pick-btn rounded-lg border px-3 py-2 text-sm" data-multi="1">Add from media library</button>
    </div>
  </div>

  <div>
    <label class="text-sm">Live demo URL</label>
    <input name="demo_url" value="{{ old('demo_url', $item->demo_url) }}" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm">
  </div>

  {{-- Main file --}}
  <div class="rounded-lg border border-slate-100 p-4" data-media-field="mainfile">
    <label class="text-sm font-medium">Main download file</label>
    <div class="mt-2 flex flex-wrap gap-2 text-xs">
      <button type="button" class="media-tab rounded-full border px-3 py-1" data-tab="url">External / Drive URL</button>
      <button type="button" class="media-tab rounded-full border px-3 py-1" data-tab="upload">Upload</button>
      <button type="button" class="media-tab rounded-full border px-3 py-1" data-tab="library">Library</button>
    </div>
    <div class="media-pane mt-3" data-pane="url">
      <input name="main_file_url" id="main_file_url" value="{{ old('main_file_url', $item->main_file_url) }}" class="w-full rounded-lg border px-3 py-2 text-sm" placeholder="https://… ZIP / Drive / S3 URL">
    </div>
    <div class="media-pane mt-3 hidden" data-pane="upload">
      <div class="flex flex-wrap items-end gap-2">
        <div class="flex-1 min-w-[140px]">
          <label class="text-xs">Disk</label>
          <select class="media-disk mt-1 w-full rounded-lg border px-2 py-2 text-sm">
            <option value="local">Local</option>
            <option value="s3">Amazon S3</option>
            <option value="backblaze">Backblaze B2</option>
            <option value="idrive">iDrive e2</option>
          </select>
        </div>
        <div class="flex-[2] min-w-[180px]">
          <label class="text-xs">File (ZIP etc.)</label>
          <input type="file" class="media-file mt-1 block w-full text-sm">
        </div>
        <button type="button" class="media-upload-btn rounded-lg bg-slate-800 px-3 py-2 text-sm font-medium text-white">Upload</button>
      </div>
      <p class="media-status mt-1 text-xs text-slate-500"></p>
    </div>
    <div class="media-pane mt-3 hidden" data-pane="library">
      <button type="button" class="media-pick-btn rounded-lg border px-3 py-2 text-sm">Pick from media library</button>
    </div>
  </div>
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

{{-- Media library modal --}}
<div id="media-modal" class="fixed inset-0 z-50 hidden">
  <div class="absolute inset-0 bg-black/40" id="media-modal-backdrop"></div>
  <div class="absolute left-1/2 top-1/2 max-h-[80vh] w-[min(640px,94vw)] -translate-x-1/2 -translate-y-1/2 overflow-y-auto rounded-xl bg-white p-4 shadow-xl">
    <div class="mb-3 flex items-center justify-between">
      <h3 class="font-semibold">Media library</h3>
      <button type="button" id="media-modal-close" class="text-slate-500">Close</button>
    </div>
    <div id="media-modal-grid" class="grid grid-cols-3 gap-2 sm:grid-cols-4"></div>
  </div>
</div>

@push('scripts')
<script>
(function () {
  const csrf = @json(csrf_token());
  const uploadUrl = @json(route('admin.media.store'));
  const libraryUrl = @json(route('admin.media.json'));

  // Tabs
  document.querySelectorAll('[data-media-field]').forEach(function (block) {
    const tabs = block.querySelectorAll('.media-tab');
    tabs.forEach(function (tab) {
      tab.addEventListener('click', function () {
        tabs.forEach(function (t) { t.classList.remove('bg-emerald-50', 'border-emerald-500'); });
        tab.classList.add('bg-emerald-50', 'border-emerald-500');
        block.querySelectorAll('.media-pane').forEach(function (p) { p.classList.add('hidden'); });
        const pane = block.querySelector('.media-pane[data-pane="' + tab.dataset.tab + '"]');
        if (pane) pane.classList.remove('hidden');
      });
    });
    if (tabs[0]) tabs[0].classList.add('bg-emerald-50', 'border-emerald-500');

    const uploadBtn = block.querySelector('.media-upload-btn');
    if (uploadBtn) {
      uploadBtn.addEventListener('click', async function () {
        const fileInput = block.querySelector('.media-file');
        const disk = block.querySelector('.media-disk')?.value || 'local';
        const status = block.querySelector('.media-status');
        const files = fileInput?.files;
        if (!files || !files.length) {
          if (status) status.textContent = 'Choose a file first.';
          return;
        }
        if (status) status.textContent = 'Uploading…';
        try {
          for (let i = 0; i < files.length; i++) {
            const fd = new FormData();
            fd.append('file', files[i]);
            fd.append('disk', disk);
            fd.append('_token', csrf);
            const res = await fetch(uploadUrl, {
              method: 'POST',
              headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
              body: fd
            });
            const data = await res.json();
            if (!data.ok) throw new Error(data.message || 'Upload failed');
            applyUrl(block, data.asset.url);
          }
          if (status) status.textContent = 'Uploaded.';
        } catch (e) {
          if (status) status.textContent = e.message || 'Upload failed';
        }
      });
    }

    const pickBtn = block.querySelector('.media-pick-btn');
    if (pickBtn) {
      pickBtn.addEventListener('click', function () {
        openLibrary(block, !!pickBtn.dataset.multi);
      });
    }
  });

  function applyUrl(block, url) {
    const field = block.dataset.mediaField;
    if (field === 'thumbnail') {
      const input = document.getElementById('thumbnail_url');
      input.value = url;
      const img = document.getElementById('thumbnail_preview');
      if (img) { img.src = url; img.classList.remove('hidden'); }
    } else if (field === 'gallery') {
      const ta = document.getElementById('gallery_text');
      const lines = (ta.value || '').split(/\n/).map(function (s) { return s.trim(); }).filter(Boolean);
      if (!lines.includes(url)) lines.push(url);
      ta.value = lines.join('\n');
    } else if (field === 'mainfile') {
      document.getElementById('main_file_url').value = url;
    }
  }

  // Library modal
  const modal = document.getElementById('media-modal');
  const grid = document.getElementById('media-modal-grid');
  let activeBlock = null;
  let multi = false;

  function openLibrary(block, isMulti) {
    activeBlock = block;
    multi = isMulti;
    modal.classList.remove('hidden');
    grid.innerHTML = '<p class="col-span-full text-sm text-slate-500">Loading…</p>';
    fetch(libraryUrl + (block.dataset.mediaField === 'mainfile' ? '' : '?images=1'), {
      headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    })
      .then(function (r) { return r.json(); })
      .then(function (data) {
        grid.innerHTML = '';
        (data.data || []).forEach(function (a) {
          const btn = document.createElement('button');
          btn.type = 'button';
          btn.className = 'rounded-lg border p-1 text-left hover:border-emerald-500';
          const isImg = (a.mime_type || '').indexOf('image/') === 0 || /\.(jpe?g|png|gif|webp)/i.test(a.url || '');
          btn.innerHTML = isImg
            ? '<img src="' + a.url + '" class="h-20 w-full rounded object-cover bg-slate-100" alt="">'
            : '<div class="flex h-20 items-center justify-center bg-slate-100 text-[10px] px-1">' + (a.filename || 'file') + '</div>';
          btn.addEventListener('click', function () {
            applyUrl(activeBlock, a.url);
            if (!multi) closeLibrary();
          });
          grid.appendChild(btn);
        });
        if (!(data.data || []).length) {
          grid.innerHTML = '<p class="col-span-full text-sm text-slate-500">Library empty. Upload files first.</p>';
        }
      });
  }
  function closeLibrary() { modal.classList.add('hidden'); }
  document.getElementById('media-modal-close').addEventListener('click', closeLibrary);
  document.getElementById('media-modal-backdrop').addEventListener('click', closeLibrary);

  // Category / attributes (existing)
  const childrenMap = @json($childrenMap);
  const initialCategoryId = @json(old('category_id', $item->category_id));
  const initialParentId = @json(old('parent_hint', $selectedParentId));
  const savedAttrs = @json($savedAttrs);
  const attrUrlBase = @json(url('/admin/products/category-attributes'));
  const parentSel = document.getElementById('parent_category');
  const subSel = document.getElementById('sub_category');
  const categoryIdInput = document.getElementById('category_id');
  const picker = document.getElementById('attr-picker');

  function fillSubs(parentId, selectedSubId) {
    subSel.innerHTML = '<option value="">— None (use parent) —</option>';
    (childrenMap[String(parentId)] || []).forEach(function (c) {
      const opt = document.createElement('option');
      opt.value = c.id;
      opt.textContent = c.name;
      if (selectedSubId && String(selectedSubId) === String(c.id)) opt.selected = true;
      subSel.appendChild(opt);
    });
  }
  function effectiveCategoryId() { return subSel.value || parentSel.value || ''; }
  function syncCategoryId() { categoryIdInput.value = effectiveCategoryId(); }
  function isChecked(label, value) {
    const arr = savedAttrs[label];
    if (!arr) return false;
    return Array.isArray(arr) ? arr.map(String).includes(String(value)) : String(arr) === String(value);
  }
  function renderAttributes(attrs) {
    picker.innerHTML = '';
    if (!attrs || !Object.keys(attrs).length) {
      picker.innerHTML = '<p class="text-sm text-slate-400">No attributes for this category.</p>';
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
        const lab = document.createElement('label');
        lab.className = 'inline-flex items-center gap-1.5 text-sm';
        lab.innerHTML = '<input type="checkbox" name="attr[' + label.replace(/"/g,'&quot;') + '][]" value="' +
          String(val).replace(/"/g,'&quot;') + '"' + (isChecked(label, val) ? ' checked' : '') + '> ' + val;
        row.appendChild(lab);
      });
      box.appendChild(row);
      picker.appendChild(box);
    });
  }
  function loadAttributes(catId) {
    if (!catId) { picker.innerHTML = '<p class="text-sm text-slate-400">Select a category.</p>'; return; }
    picker.innerHTML = '<p class="text-sm text-slate-400">Loading…</p>';
    fetch(attrUrlBase + '/' + catId, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
      .then(function (r) { return r.json(); })
      .then(function (data) { renderAttributes(data.attributes || {}); });
  }
  parentSel.addEventListener('change', function () {
    fillSubs(parentSel.value, null); syncCategoryId(); loadAttributes(effectiveCategoryId());
  });
  subSel.addEventListener('change', function () {
    syncCategoryId(); loadAttributes(effectiveCategoryId());
  });
  if (initialParentId) {
    parentSel.value = String(initialParentId);
    fillSubs(initialParentId, initialCategoryId && String(initialCategoryId) !== String(initialParentId) ? initialCategoryId : null);
  }
  syncCategoryId();
  if (effectiveCategoryId()) loadAttributes(effectiveCategoryId());
})();
</script>
@endpush
@endsection
