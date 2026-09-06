@extends('layouts.admin')
@section('title', 'Payments')
@section('content')
<a href="{{ route('admin.settings.hub') }}" class="text-sm text-emerald-700">← Settings</a>
<h1 class="mt-2 text-xl font-bold">Payment methods</h1>
<p class="text-sm text-slate-500">Stripe, PayPal, bank transfer, and manual / offline.</p>

<div class="mt-6 grid gap-6 lg:grid-cols-4" id="pay-app">
  <div class="space-y-2" id="method-list"></div>
  <div class="rounded-xl border bg-white p-6 shadow-sm lg:col-span-3" id="method-editor"></div>
</div>

<form method="post" action="{{ route('admin.settings.payments.update') }}" class="mt-6">
  @csrf
  @method('PUT')
  <input type="hidden" name="methods_json" id="methods_json">
  <button class="rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white">Save payment methods</button>
</form>

@push('scripts')
<script>
(function () {
  let methods = @json($methods);
  let active = 0;

  function field(label, key, path, type) {
    type = type || 'text';
    const m = methods[active];
    let val = '';
    if (path === 'config') val = (m.config && m.config[key]) || '';
    else if (path === 'secrets') val = (m.secrets && m.secrets[key]) || '';
    else if (path === 'root') val = m[key] || '';
    return '<div class="mt-3"><label class="text-sm font-medium">' + label + '</label>' +
      '<input data-path="' + path + '" data-key="' + key + '" type="' + type + '" value="' + String(val).replace(/"/g,'&quot;') + '" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm pay-input"></div>';
  }

  function render() {
    const list = document.getElementById('method-list');
    list.innerHTML = methods.map((m, i) =>
      '<button type="button" data-i="' + i + '" class="flex w-full items-center justify-between rounded-lg border px-3 py-2.5 text-left text-sm ' +
      (i===active ? 'border-emerald-500 bg-emerald-50' : 'border-slate-200 bg-white') + '">' +
      '<span class="font-medium">' + m.name + '</span>' +
      '<span class="rounded-full px-2 py-0.5 text-[10px] font-semibold uppercase ' +
      (m.enabled ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500') + '">' +
      (m.enabled ? 'On' : 'Off') + '</span></button>'
    ).join('');
    list.querySelectorAll('button').forEach(btn => btn.onclick = () => { active = +btn.dataset.i; render(); });

    const m = methods[active];
    let html = '<div class="flex items-center justify-between"><div><h2 class="text-lg font-semibold">' + m.name + '</h2>' +
      '<p class="text-xs text-slate-500">' + (m.is_manual ? 'Manual · admin confirmation' : 'Automatic gateway') + '</p></div>' +
      '<label class="flex items-center gap-2 text-sm"><input type="checkbox" id="en" ' + (m.enabled?'checked':'') + '> Enabled</label></div>';

    if (m.provider === 'STRIPE') {
      html += field('Publishable key', 'publishableKey', 'config');
      html += field('Secret key', 'secretKey', 'secrets', 'password');
      html += field('Webhook secret', 'webhookSecret', 'secrets', 'password');
    } else if (m.provider === 'PAYPAL') {
      html += field('Client ID', 'clientId', 'config');
      html += field('Client secret', 'clientSecret', 'secrets', 'password');
      html += field('Mode (sandbox|live)', 'mode', 'config');
    } else if (m.provider === 'BANK_TRANSFER') {
      html += field('Bank name', 'bankName', 'config');
      html += field('Account name', 'accountName', 'config');
      html += field('Account number', 'accountNumber', 'config');
    }
    html += '<div class="mt-3"><label class="text-sm font-medium">Buyer instructions</label>' +
      '<textarea id="instr" rows="3" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm">' + (m.instructions||'') + '</textarea></div>';

    const ed = document.getElementById('method-editor');
    ed.innerHTML = html;
    document.getElementById('en').onchange = (e) => { methods[active].enabled = e.target.checked; render(); };
    document.getElementById('instr').oninput = (e) => { methods[active].instructions = e.target.value; sync(); };
    ed.querySelectorAll('.pay-input').forEach(inp => {
      inp.oninput = () => {
        const path = inp.dataset.path, key = inp.dataset.key;
        if (path === 'config') { methods[active].config = methods[active].config || {}; methods[active].config[key] = inp.value; }
        if (path === 'secrets') { methods[active].secrets = methods[active].secrets || {}; methods[active].secrets[key] = inp.value; }
        sync();
      };
    });
    sync();
  }
  function sync() {
    document.getElementById('methods_json').value = JSON.stringify(methods);
  }
  render();
})();
</script>
@endpush
@endsection
