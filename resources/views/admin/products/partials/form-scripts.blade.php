@push('scripts')
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
<script>
(function () {
  const csrf = @json(csrf_token());
  const initialDownloads = @json($initialDownloads);
  let downloadIndex = 0;

  function addDownloadRow(data) {
    data = data || {};
    const wrap = document.getElementById('download-rows');
    if (!wrap) return;
    const i = downloadIndex++;
    const row = document.createElement('div');
    row.className = 'download-row grid gap-2 rounded-lg border border-slate-200 bg-white p-3 sm:grid-cols-12';
    row.innerHTML =
      '<div class="sm:col-span-3"><label class="text-xs text-slate-500">Label</label>' +
      '<input name="download_files[' + i + '][label]" value="' + String(data.label || '').replace(/"/g,'"') + '" class="mt-1 w-full rounded border px-2 py-1.5 text-sm" placeholder="Main ZIP / Addon pack"></div>' +
      '<div class="sm:col-span-5"><label class="text-xs text-slate-500">URL</label>' +
      '<input name="download_files[' + i + '][url]" value="' + String(data.url || '').replace(/"/g,'"') + '" class="mt-1 w-full rounded border px-2 py-1.5 text-sm" placeholder="https://…"></div>' +
      '<div class="sm:col-span-2"><label class="text-xs text-slate-500">Type</label>' +
      '<select name="download_files[' + i + '][type]" class="mt-1 w-full rounded border px-2 py-1.5 text-sm">' +
      '<option value="main"' + ((data.type||'main')==='main'?' selected':'') + '>Main</option>' +
      '<option value="addon"' + (data.type==='addon'?' selected':'') + '>Addon</option>' +
      '<option value="extra"' + (data.type==='extra'?' selected':'') + '>Extra</option></select></div>' +
      '<div class="flex items-end sm:col-span-2"><button type="button" class="w-full rounded border border-red-200 px-2 py-1.5 text-sm text-red-600 hover:bg-red-50" data-remove>Remove</button></div>';
    row.querySelector('[data-remove]').addEventListener('click', function () { row.remove(); });
    wrap.appendChild(row);
  }
  window.addDownloadRow = addDownloadRow;

  document.getElementById('add-download-row')?.addEventListener('click', function () {
    addDownloadRow({ label: '', url: '', type: 'addon' });
  });
  if (Array.isArray(initialDownloads) && initialDownloads.length) {
    initialDownloads.forEach(function (d) { addDownloadRow(d); });
  } else {
    addDownloadRow({ label: 'Main file', url: '', type: 'main' });
  }

  const uploadUrl = @json(route('admin.media.store'));
  const libraryUrl = @json(route('admin.media.json'));

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
          for (const file of files) {
            const fd = new FormData();
            fd.append('file', file);
            fd.append('disk', disk);
            fd.append('_token', csrf);
            const res = await fetch(uploadUrl, { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            const data = await res.json();
            if (!res.ok) throw new Error(data.message || 'Upload failed');
            const url = data.url || data.path || '';
            if (block.dataset.mediaField === 'thumbnail') {
              document.getElementById('thumbnail_url').value = url;
              const prev = document.getElementById('thumbnail_preview');
              if (prev) { prev.src = url; prev.classList.remove('hidden'); }
            } else if (block.dataset.mediaField === 'gallery') {
              const ta = document.getElementById('gallery_text');
              ta.value = (ta.value ? ta.value.trim() + '\n' : '') + url;
            } else if (block.dataset.mediaField === 'mainfile') {
              const type = document.getElementById('bundle-upload-type')?.value || 'main';
              addDownloadRow({ label: file.name || type, url: url, type: type });
            }
          }
          if (status) status.textContent = 'Uploaded.';
        } catch (e) {
          if (status) status.textContent = e.message || 'Upload failed';
        }
      });
    }

    const pickBtn = block.querySelector('.media-pick-btn');
    if (pickBtn) {
      pickBtn.addEventListener('click', async function () {
        const modal = document.getElementById('media-modal');
        const grid = document.getElementById('media-modal-grid');
        grid.innerHTML = '<p class="col-span-full text-sm text-slate-500">Loading…</p>';
        modal.classList.remove('hidden');
        try {
          const res = await fetch(libraryUrl, { headers: { 'Accept': 'application/json' } });
          const data = await res.json();
          const items = data.data || data || [];
          grid.innerHTML = '';
          items.forEach(function (m) {
            const url = m.url || m.path || '';
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'rounded border p-1 hover:border-emerald-500';
            btn.innerHTML = '<img src="' + url + '" class="h-20 w-full object-cover rounded" alt="">';
            btn.addEventListener('click', function () {
              if (block.dataset.mediaField === 'thumbnail') {
                document.getElementById('thumbnail_url').value = url;
                const prev = document.getElementById('thumbnail_preview');
                if (prev) { prev.src = url; prev.classList.remove('hidden'); }
              } else if (block.dataset.mediaField === 'gallery') {
                const ta = document.getElementById('gallery_text');
                ta.value = (ta.value ? ta.value.trim() + '\n' : '') + url;
              } else if (block.dataset.mediaField === 'mainfile') {
                addDownloadRow({ label: 'Library file', url: url, type: document.getElementById('bundle-upload-type')?.value || 'main' });
              }
              modal.classList.add('hidden');
            });
            grid.appendChild(btn);
          });
        } catch (e) {
          grid.innerHTML = '<p class="col-span-full text-sm text-red-600">Failed to load library.</p>';
        }
      });
    }
  });

  document.getElementById('media-modal-close')?.addEventListener('click', function () {
    document.getElementById('media-modal').classList.add('hidden');
  });
  document.getElementById('media-modal-backdrop')?.addEventListener('click', function () {
    document.getElementById('media-modal').classList.add('hidden');
  });

  // Category / attributes
  const childrenMap = @json($childrenMap);
  const parentSel = document.getElementById('parent_category');
  const subSel = document.getElementById('sub_category');
  const catIdInput = document.getElementById('category_id');
  const picker = document.getElementById('attr-picker');
  const attrUrlBase = @json(url('/admin/products/category-attributes'));
  const initialParentId = @json($selectedParentId);
  const initialCategoryId = @json(old('category_id', $item->category_id));
  const savedAttrs = @json($savedAttrs);

  function fillSubs(parentId, selectedSub) {
    subSel.innerHTML = '<option value="">— None (use parent) —</option>';
    const kids = childrenMap[String(parentId)] || [];
    kids.forEach(function (c) {
      const opt = document.createElement('option');
      opt.value = c.id;
      opt.textContent = c.name;
      if (selectedSub && String(selectedSub) === String(c.id)) opt.selected = true;
      subSel.appendChild(opt);
    });
  }
  function effectiveCategoryId() {
    return subSel.value || parentSel.value || '';
  }
  function syncCategoryId() {
    catIdInput.value = effectiveCategoryId();
  }
  function isChecked(label, val) {
    const arr = savedAttrs[label];
    if (!arr) return false;
    return Array.isArray(arr) ? arr.map(String).includes(String(val)) : String(arr) === String(val);
  }
  function renderAttributes(attrs) {
    picker.innerHTML = '';
    const keys = Object.keys(attrs || {});
    if (!keys.length) {
      picker.innerHTML = '<p class="text-sm text-slate-400">No attributes for this category.</p>';
      return;
    }
    keys.forEach(function (label) {
      const values = Array.isArray(attrs[label]) ? attrs[label] : [attrs[label]];
      const box = document.createElement('div');
      box.className = 'rounded-lg border border-slate-100 bg-slate-50 p-3';
      box.innerHTML = '<p class="text-sm font-semibold text-slate-700">' + label + '</p>';
      const row = document.createElement('div');
      row.className = 'mt-2 flex flex-wrap gap-3';
      values.forEach(function (val) {
        const lab = document.createElement('label');
        lab.className = 'inline-flex items-center gap-1.5 text-sm';
        lab.innerHTML = '<input type="checkbox" name="attr[' + label.replace(/"/g,'"') + '][]" value="' +
          String(val).replace(/"/g,'"') + '"' + (isChecked(label, val) ? ' checked' : '') + '> ' + val;
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
