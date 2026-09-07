@extends('layouts.admin')
@section('title', 'Menus')
@section('content')
<a href="{{ route('admin.settings.hub') }}" class="text-sm text-emerald-700">← Settings hub</a>

<div class="mt-2 flex flex-wrap items-start justify-between gap-3">
  <div>
    <h1 class="text-xl font-bold">Menus</h1>
    <p class="mt-1 text-sm text-slate-500">Add, edit, reorder, or remove links for the <strong>desktop</strong> sub-nav and the <strong>mobile</strong> hamburger menu.</p>
  </div>
</div>

<form method="post" action="{{ route('admin.settings.navigation.update') }}" id="menu-form" class="mt-6 space-y-8">
  @csrf @method('PUT')
  <input type="hidden" name="desktop_json" id="desktop_json">
  <input type="hidden" name="mobile_json" id="mobile_json">

  <section class="rounded-xl border bg-white p-4 sm:p-6">
    <div class="flex flex-wrap items-center justify-between gap-2">
      <div>
        <h2 class="font-semibold text-slate-900">Desktop menu</h2>
        <p class="text-xs text-slate-500">Shown in the horizontal bar under the header on large screens.</p>
      </div>
      <label class="flex items-center gap-2 text-sm text-slate-700">
        <input type="checkbox" name="desktop_show_categories" value="1" @checked(!empty($options['desktop_show_categories']))>
        Also show auto categories
      </label>
    </div>
    <div id="desktop-builder" class="mt-4 space-y-2"></div>
    <button type="button" data-add="desktop" class="mt-3 rounded-lg border border-dashed border-slate-300 px-4 py-2 text-sm text-slate-600 hover:border-emerald-400">+ Add desktop link</button>
  </section>

  <section class="rounded-xl border bg-white p-4 sm:p-6">
    <div class="flex flex-wrap items-center justify-between gap-2">
      <div>
        <h2 class="font-semibold text-slate-900">Mobile menu</h2>
        <p class="text-xs text-slate-500">Shown in the hamburger drawer on phones and tablets.</p>
      </div>
      <div class="flex flex-col gap-2 text-sm text-slate-700 sm:items-end">
        <label class="flex items-center gap-2">
          <input type="checkbox" name="mobile_show_categories" value="1" @checked(!empty($options['mobile_show_categories']))>
          Also show auto categories
        </label>
        <label class="flex items-center gap-2">
          <input type="checkbox" name="mobile_show_account_links" value="1" @checked(!isset($options['mobile_show_account_links']) || !empty($options['mobile_show_account_links']))>
          Show Cart / Sign in / Account links
        </label>
      </div>
    </div>
    <div id="mobile-builder" class="mt-4 space-y-2"></div>
    <button type="button" data-add="mobile" class="mt-3 rounded-lg border border-dashed border-slate-300 px-4 py-2 text-sm text-slate-600 hover:border-emerald-400">+ Add mobile link</button>
  </section>

  <div class="rounded-lg border border-sky-100 bg-sky-50 px-4 py-3 text-sm text-sky-900">
    <p class="font-medium">Tips</p>
    <ul class="mt-1 list-inside list-disc text-xs text-sky-800">
      <li>Use relative paths like <code class="rounded bg-white px-1">/search</code>, <code class="rounded bg-white px-1">/blog</code>, or full URLs <code class="rounded bg-white px-1">https://…</code></li>
      <li>Use ↑ / ↓ to change order. Empty label or URL rows are dropped on save.</li>
      <li>Footer columns are edited under <a href="{{ route('admin.settings.header_footer') }}" class="font-medium underline">Header / footer</a>.</li>
    </ul>
  </div>

  <button type="submit" class="rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white">Save menus</button>
</form>

@push('scripts')
<script>
(function () {
  const menus = {
    desktop: @json($desktop),
    mobile: @json($mobile)
  };

  function esc(s) {
    return String(s || '').replace(/&/g,'&').replace(/"/g,'"').replace(/</g,'<');
  }

  function render(which) {
    const box = document.getElementById(which + '-builder');
    const items = menus[which];
    if (!items.length) {
      box.innerHTML = '<p class="text-sm text-slate-400">No links yet. Click “Add” below.</p>';
      sync();
      return;
    }
    box.innerHTML = items.map((it, i) =>
      '<div class="flex flex-wrap items-center gap-2 rounded-xl border border-slate-200 bg-slate-50/50 p-3" data-which="'+which+'" data-i="'+i+'">'+
        '<div class="flex gap-1">'+
          '<button type="button" class="up rounded border bg-white px-2 py-1 text-xs" title="Move up">↑</button>'+
          '<button type="button" class="dn rounded border bg-white px-2 py-1 text-xs" title="Move down">↓</button>'+
        '</div>'+
        '<input class="lab min-w-[120px] flex-1 rounded-lg border px-3 py-2 text-sm" placeholder="Label" value="'+esc(it.label)+'">'+
        '<input class="url min-w-[160px] flex-[2] rounded-lg border px-3 py-2 text-sm" placeholder="/path or https://…" value="'+esc(it.url)+'">'+
        '<label class="flex items-center gap-1 text-xs text-slate-600 whitespace-nowrap"><input type="checkbox" class="ne" '+(it.open_new?'checked':'')+'> New tab</label>'+
        '<button type="button" class="rm text-sm font-medium text-red-600">Remove</button>'+
      '</div>'
    ).join('');

    box.querySelectorAll('[data-i]').forEach(row => {
      const which = row.dataset.which;
      const i = +row.dataset.i;
      row.querySelector('.lab').addEventListener('input', e => { menus[which][i].label = e.target.value; sync(); });
      row.querySelector('.url').addEventListener('input', e => { menus[which][i].url = e.target.value; sync(); });
      row.querySelector('.ne').addEventListener('change', e => { menus[which][i].open_new = e.target.checked; sync(); });
      row.querySelector('.rm').addEventListener('click', () => { menus[which].splice(i, 1); render(which); });
      row.querySelector('.up').addEventListener('click', () => {
        if (i === 0) return;
        const t = menus[which][i-1]; menus[which][i-1] = menus[which][i]; menus[which][i] = t;
        render(which);
      });
      row.querySelector('.dn').addEventListener('click', () => {
        if (i >= menus[which].length - 1) return;
        const t = menus[which][i+1]; menus[which][i+1] = menus[which][i]; menus[which][i] = t;
        render(which);
      });
    });
    sync();
  }

  function sync() {
    document.getElementById('desktop_json').value = JSON.stringify(menus.desktop);
    document.getElementById('mobile_json').value = JSON.stringify(menus.mobile);
  }

  document.querySelectorAll('[data-add]').forEach(btn => {
    btn.addEventListener('click', () => {
      const which = btn.getAttribute('data-add');
      menus[which].push({ label: '', url: '/', open_new: false });
      render(which);
    });
  });

  document.getElementById('menu-form').addEventListener('submit', sync);

  render('desktop');
  render('mobile');
})();
</script>
@endpush
@endsection
