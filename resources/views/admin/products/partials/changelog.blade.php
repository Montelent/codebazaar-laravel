<section class="rounded-xl border bg-white p-6 shadow-sm space-y-4" id="changelog-section">
  <div class="flex flex-wrap items-start justify-between gap-2">
    <div>
      <h2 class="text-xs font-semibold uppercase tracking-wide text-slate-500">Version & changelog</h2>
      <p class="mt-1 text-xs text-slate-500">Like CodeCanyon — list version updates buyers can review on the product page.</p>
    </div>
    <button type="button" id="add-changelog-row" class="rounded-lg border border-emerald-600 px-3 py-1.5 text-xs font-semibold text-emerald-700 hover:bg-emerald-50">+ Add entry</button>
  </div>
  <div>
    <label class="text-sm font-medium">Current version</label>
    <input name="version" value="{{ old('version', $item->version) }}" class="mt-1 w-full max-w-xs rounded-lg border px-3 py-2 text-sm" placeholder="e.g. 1.2.0">
  </div>
  <div id="changelog-rows" class="space-y-3">
    @php $entries = old('changelog', $changelogEntries ?? []); @endphp
    @forelse($entries as $i => $entry)
      <div class="changelog-row grid gap-2 rounded-lg border border-slate-100 bg-slate-50 p-3 sm:grid-cols-6" data-changelog-row>
        <div class="sm:col-span-1">
          <label class="text-xs text-slate-500">Version</label>
          <input name="changelog[{{ $i }}][version]" value="{{ is_array($entry) ? ($entry['version'] ?? '') : '' }}" class="mt-1 w-full rounded border px-2 py-1.5 text-sm" placeholder="1.1.0">
        </div>
        <div class="sm:col-span-1">
          <label class="text-xs text-slate-500">Date</label>
          <input type="date" name="changelog[{{ $i }}][date]" value="{{ is_array($entry) ? ($entry['date'] ?? '') : '' }}" class="mt-1 w-full rounded border px-2 py-1.5 text-sm">
        </div>
        <div class="sm:col-span-3">
          <label class="text-xs text-slate-500">Changes</label>
          <textarea name="changelog[{{ $i }}][changes]" rows="2" class="mt-1 w-full rounded border px-2 py-1.5 text-sm" placeholder="Fixed X, added Y…">{{ is_array($entry) ? ($entry['changes'] ?? '') : '' }}</textarea>
        </div>
        <div class="flex items-end sm:col-span-1">
          <button type="button" class="w-full rounded border border-red-200 px-2 py-1.5 text-sm text-red-600 hover:bg-red-50" data-remove-changelog>Remove</button>
        </div>
      </div>
    @empty
    @endforelse
  </div>
  <p class="text-[11px] text-slate-400">Newest entries first is recommended. Empty rows are ignored on save.</p>
</section>

@once
@push('scripts')
<script>
(function () {
  var wrap = document.getElementById('changelog-rows');
  var addBtn = document.getElementById('add-changelog-row');
  if (!wrap || !addBtn) return;
  var idx = wrap.querySelectorAll('[data-changelog-row]').length;
  function bindRemove(row) {
    var btn = row.querySelector('[data-remove-changelog]');
    if (btn) btn.addEventListener('click', function () { row.remove(); });
  }
  wrap.querySelectorAll('[data-changelog-row]').forEach(bindRemove);
  addBtn.addEventListener('click', function () {
    var row = document.createElement('div');
    row.className = 'changelog-row grid gap-2 rounded-lg border border-slate-100 bg-slate-50 p-3 sm:grid-cols-6';
    row.setAttribute('data-changelog-row', '');
    row.innerHTML =
      '<div class="sm:col-span-1"><label class="text-xs text-slate-500">Version</label>' +
      '<input name="changelog[' + idx + '][version]" class="mt-1 w-full rounded border px-2 py-1.5 text-sm" placeholder="1.1.0"></div>' +
      '<div class="sm:col-span-1"><label class="text-xs text-slate-500">Date</label>' +
      '<input type="date" name="changelog[' + idx + '][date]" class="mt-1 w-full rounded border px-2 py-1.5 text-sm"></div>' +
      '<div class="sm:col-span-3"><label class="text-xs text-slate-500">Changes</label>' +
      '<textarea name="changelog[' + idx + '][changes]" rows="2" class="mt-1 w-full rounded border px-2 py-1.5 text-sm" placeholder="Fixed X, added Y…"></textarea></div>' +
      '<div class="flex items-end sm:col-span-1"><button type="button" class="w-full rounded border border-red-200 px-2 py-1.5 text-sm text-red-600 hover:bg-red-50" data-remove-changelog>Remove</button></div>';
    wrap.insertBefore(row, wrap.firstChild);
    bindRemove(row);
    idx++;
  });
})();
</script>
@endpush
@endonce
