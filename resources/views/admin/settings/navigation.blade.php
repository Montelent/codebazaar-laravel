@extends('layouts.admin')
@section('title', 'Navigation')
@section('content')
<a href="{{ route('admin.settings.hub') }}" class="text-sm text-emerald-700">← Settings</a>
<h1 class="mt-2 text-xl font-bold">Header navigation</h1>
<p class="text-sm text-slate-500">Build top menu links shown on the storefront.</p>

<div id="nav-builder" class="mt-6 space-y-3"></div>
<button type="button" id="add-row" class="mt-3 rounded-lg border border-dashed border-slate-300 px-4 py-2 text-sm text-slate-600 hover:border-emerald-400">+ Add link</button>

<form method="post" action="{{ route('admin.settings.navigation.update') }}" class="mt-6">
  @csrf @method('PUT')
  <input type="hidden" name="items_json" id="items_json">
  <button class="rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white">Save navigation</button>
</form>

@push('scripts')
<script>
(function(){
  let items = @json($items);
  function render(){
    const box = document.getElementById('nav-builder');
    box.innerHTML = items.map((it,i) =>
      '<div class="flex flex-wrap items-center gap-2 rounded-xl border bg-white p-3" data-i="'+i+'">'+
      '<input class="lab flex-1 rounded border px-2 py-1.5 text-sm" placeholder="Label" value="'+String(it.label||'').replace(/"/g,'&quot;')+'">'+
      '<input class="url flex-[2] rounded border px-2 py-1.5 text-sm" placeholder="/path or https://" value="'+String(it.url||'').replace(/"/g,'&quot;')+'">'+
      '<label class="text-xs flex items-center gap-1"><input type="checkbox" class="ne" '+(it.open_new?'checked':'')+'> New tab</label>'+
      '<button type="button" class="rm text-red-600 text-sm">Remove</button></div>'
    ).join('');
    box.querySelectorAll('[data-i]').forEach(row => {
      const i = +row.dataset.i;
      row.querySelector('.lab').oninput = e => { items[i].label = e.target.value; sync(); };
      row.querySelector('.url').oninput = e => { items[i].url = e.target.value; sync(); };
      row.querySelector('.ne').onchange = e => { items[i].open_new = e.target.checked; sync(); };
      row.querySelector('.rm').onclick = () => { items.splice(i,1); render(); };
    });
    sync();
  }
  function sync(){ document.getElementById('items_json').value = JSON.stringify(items); }
  document.getElementById('add-row').onclick = () => { items.push({label:'',url:'/',open_new:false}); render(); };
  render();
})();
</script>
@endpush
@endsection
