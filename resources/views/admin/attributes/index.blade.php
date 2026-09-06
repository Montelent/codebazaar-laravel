@extends('layouts.admin')
@section('title', 'Attributes')
@section('content')
<div class="mb-4">
  <h1 class="text-xl font-bold">Category attributes</h1>
  <p class="text-sm text-slate-500">CodeCanyon-style presets per category (browsers, frameworks, files included, …).</p>
</div>

<form method="get" action="{{ route('admin.attributes.index') }}" class="mb-6 flex flex-wrap items-end gap-3">
  <div>
    <label class="text-xs font-medium text-slate-500">Category</label>
    <select name="category" onchange="this.form.submit()" class="mt-1 rounded-lg border px-3 py-2 text-sm">
      @foreach($categories as $c)
        <option value="{{ $c->slug }}" @selected($categorySlug===$c->slug)>{{ $c->name }} ({{ $c->slug }})</option>
      @endforeach
      @if($categories->isEmpty())
        <option value="wordpress">wordpress</option>
        <option value="javascript">javascript</option>
        <option value="php-scripts">php-scripts</option>
      @endif
    </select>
  </div>
</form>

<div class="grid gap-6 lg:grid-cols-3">
  <div class="rounded-xl border bg-white p-4 lg:col-span-1">
    <h2 class="text-sm font-semibold">Attribute labels</h2>
    <ul id="attr-labels" class="mt-3 max-h-80 space-y-1 overflow-y-auto text-sm">
      @foreach($attrs as $label => $values)
        <li>
          <button type="button" data-label="{{ $label }}" class="attr-label-btn w-full rounded-lg px-3 py-2 text-left hover:bg-slate-50 {{ $loop->first ? 'bg-emerald-50 font-medium text-emerald-800' : '' }}">
            {{ $label }}
            <span class="block text-xs text-slate-400">{{ is_array($values) ? count($values) : 0 }} values</span>
          </button>
        </li>
      @endforeach
    </ul>
    <div class="mt-4 flex gap-2">
      <input id="new-label" type="text" placeholder="New attribute label" class="flex-1 rounded-lg border px-2 py-1.5 text-sm">
      <button type="button" id="add-label" class="rounded-lg bg-slate-900 px-3 py-1.5 text-sm text-white">Add</button>
    </div>
  </div>

  <div class="rounded-xl border bg-white p-4 lg:col-span-2">
    <div class="flex items-center justify-between gap-2">
      <h2 class="text-sm font-semibold">Values for <span id="current-label">{{ array_key_first($attrs) ?: '—' }}</span></h2>
      <button type="button" id="remove-label" class="text-xs text-red-600">Remove attribute</button>
    </div>
    <div id="values-list" class="mt-3 flex flex-wrap gap-2"></div>
    <div class="mt-4 flex gap-2">
      <input id="new-value" type="text" placeholder="Add value" class="flex-1 rounded-lg border px-2 py-1.5 text-sm">
      <button type="button" id="add-value" class="rounded-lg bg-emerald-600 px-3 py-1.5 text-sm text-white">Add value</button>
    </div>
    <p class="mt-2 text-xs text-slate-500">Click a chip to remove a value.</p>
  </div>
</div>

<form method="post" action="{{ route('admin.attributes.update') }}" class="mt-6 flex flex-wrap gap-3" id="save-form">
  @csrf
  @method('PUT')
  <input type="hidden" name="category_slug" value="{{ $categorySlug }}">
  <input type="hidden" name="attrs_json" id="attrs_json" value="{{ json_encode($attrs) }}">
  <button class="rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white">Save attributes</button>
</form>
<form method="post" action="{{ route('admin.attributes.reset') }}" class="mt-2" onsubmit="return confirm('Reset this category to defaults?')">
  @csrf
  <input type="hidden" name="category_slug" value="{{ $categorySlug }}">
  <button class="text-sm text-slate-500 hover:text-slate-800">Reset to CodeCanyon defaults</button>
</form>

@push('scripts')
<script>
(function () {
  let data = @json($attrs);
  let current = Object.keys(data)[0] || '';

  function syncHidden() {
    document.getElementById('attrs_json').value = JSON.stringify(data);
  }
  function renderLabels() {
    const ul = document.getElementById('attr-labels');
    ul.innerHTML = '';
    Object.keys(data).forEach((label) => {
      const li = document.createElement('li');
      const btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'attr-label-btn w-full rounded-lg px-3 py-2 text-left hover:bg-slate-50' + (label === current ? ' bg-emerald-50 font-medium text-emerald-800' : '');
      btn.innerHTML = label + '<span class="block text-xs text-slate-400">' + (data[label]||[]).length + ' values</span>';
      btn.onclick = () => { current = label; renderLabels(); renderValues(); };
      li.appendChild(btn);
      ul.appendChild(li);
    });
    document.getElementById('current-label').textContent = current || '—';
    syncHidden();
  }
  function renderValues() {
    const box = document.getElementById('values-list');
    box.innerHTML = '';
    (data[current] || []).forEach((v, i) => {
      const chip = document.createElement('button');
      chip.type = 'button';
      chip.className = 'rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs hover:border-red-300 hover:bg-red-50';
      chip.textContent = v + ' ×';
      chip.onclick = () => {
        data[current] = data[current].filter((_, idx) => idx !== i);
        renderValues(); renderLabels();
      };
      box.appendChild(chip);
    });
    syncHidden();
  }
  document.getElementById('add-label').onclick = () => {
    const label = document.getElementById('new-label').value.trim();
    if (!label) return;
    if (!data[label]) data[label] = [];
    current = label;
    document.getElementById('new-label').value = '';
    renderLabels(); renderValues();
  };
  document.getElementById('add-value').onclick = () => {
    if (!current) return alert('Add or select an attribute label first');
    const v = document.getElementById('new-value').value.trim();
    if (!v) return;
    data[current] = data[current] || [];
    data[current].push(v);
    document.getElementById('new-value').value = '';
    renderValues(); renderLabels();
  };
  document.getElementById('remove-label').onclick = () => {
    if (!current || !confirm('Remove attribute “' + current + '”?')) return;
    delete data[current];
    current = Object.keys(data)[0] || '';
    renderLabels(); renderValues();
  };
  renderLabels(); renderValues();
})();
</script>
@endpush
@endsection
