@extends('layouts.admin')
@section('title', 'SMTP / Email')
@section('content')
@php
  $providers = $providers ?? \App\Http\Controllers\Admin\SmtpSettingsController::providers();
  $currentMailer = old('mailer', $smtp['mailer'] ?? 'smtp');
@endphp
<a href="{{ route('admin.settings.hub') }}" class="text-sm text-emerald-700">← Settings hub</a>

<div class="mt-2 flex flex-wrap items-start justify-between gap-3">
  <div>
    <h1 class="text-xl font-bold">SMTP / Email</h1>
    <p class="mt-1 text-sm text-slate-500">Configure outgoing mail for order receipts, verification emails, newsletter, and password resets.</p>
  </div>
</div>

<form method="post" action="{{ route('admin.settings.smtp.update') }}" class="mt-6 max-w-2xl space-y-6" id="smtp-form">
  @csrf @method('PUT')

  <section class="rounded-xl border bg-white p-6 space-y-4">
    <label class="flex items-center gap-2 text-sm font-medium">
      <input type="checkbox" name="enabled" value="1" @checked(!empty($smtp['enabled']))>
      Enable custom mail settings (override .env)
    </label>

    <div>
      <label class="text-sm font-medium">Mailer / Provider</label>
      <select name="mailer" id="mailer-select" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm">
        @foreach($providers as $key => $p)
          <option value="{{ $key }}" @selected($currentMailer === $key)>{{ $p['label'] }}</option>
        @endforeach
      </select>
      <p id="mailer-help" class="mt-2 text-xs text-slate-500">{{ $providers[$currentMailer]['help'] ?? '' }}</p>
    </div>

    <div id="smtp-fields" class="grid gap-4 sm:grid-cols-2">
      <div class="sm:col-span-2" data-field="host">
        <label class="text-sm font-medium">SMTP host</label>
        <input name="host" id="field-host" value="{{ old('host', $smtp['host'] ?? '') }}" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm" placeholder="smtp.example.com">
      </div>
      <div data-field="port">
        <label class="text-sm font-medium">Port</label>
        <input type="number" name="port" id="field-port" value="{{ old('port', $smtp['port'] ?? 587) }}" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm">
        <p class="mt-1 text-[11px] text-slate-400">587 (TLS) · 465 (SSL) · 25 (none)</p>
      </div>
      <div data-field="encryption">
        <label class="text-sm font-medium">Encryption</label>
        <select name="encryption" id="field-encryption" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm">
          @php $enc = old('encryption', $smtp['encryption'] ?? 'tls'); if ($enc === '') $enc = 'none'; @endphp
          <option value="tls" @selected($enc === 'tls')>TLS</option>
          <option value="ssl" @selected($enc === 'ssl')>SSL</option>
          <option value="none" @selected($enc === 'none')>None</option>
        </select>
      </div>
      <div data-field="username">
        <label class="text-sm font-medium" id="label-username">Username</label>
        <input name="username" id="field-username" value="{{ old('username', $smtp['username'] ?? '') }}" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm" autocomplete="off">
      </div>
      <div data-field="password">
        <label class="text-sm font-medium" id="label-password">Password</label>
        <input type="password" name="password" id="field-password" value="" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm" autocomplete="new-password" placeholder="{{ !empty($smtp['password_set']) ? '••••••••  (leave blank to keep)' : '' }}">
        @if(!empty($smtp['password_set']))
          <p class="mt-1 text-[11px] text-slate-400">Password is saved. Leave blank to keep the current one.</p>
        @endif
      </div>
    </div>
  </section>

  <section class="rounded-xl border bg-white p-6 space-y-4">
    <h2 class="font-semibold">From address</h2>
    <div class="grid gap-4 sm:grid-cols-2">
      <div>
        <label class="text-sm font-medium">From email</label>
        <input type="email" name="from_address" value="{{ old('from_address', $smtp['from_address'] ?? '') }}" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm" placeholder="noreply@yourdomain.com">
      </div>
      <div>
        <label class="text-sm font-medium">From name</label>
        <input name="from_name" value="{{ old('from_name', $smtp['from_name'] ?? 'CodeBazaar') }}" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm">
      </div>
    </div>
  </section>

  <button class="rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white">Save mail settings</button>
</form>

<form method="post" action="{{ route('admin.settings.smtp.test') }}" class="mt-8 max-w-2xl rounded-xl border border-sky-100 bg-sky-50 p-6">
  @csrf
  <h2 class="font-semibold text-sky-900">Send test email</h2>
  <p class="mt-1 text-sm text-sky-800">Save settings first, then send a test to confirm delivery.</p>
  <div class="mt-3 flex flex-col gap-2 sm:flex-row">
    <input type="email" name="test_email" required value="{{ auth()->user()->email ?? '' }}" class="flex-1 rounded-lg border border-sky-200 px-3 py-2 text-sm" placeholder="you@example.com">
    <button class="rounded-lg bg-sky-700 px-4 py-2 text-sm font-semibold text-white hover:bg-sky-800">Send test</button>
  </div>
</form>

<div class="mt-6 max-w-2xl space-y-3 text-xs text-slate-600">
  <div class="rounded-lg border border-slate-200 bg-white px-4 py-3">
    <p class="font-medium text-slate-800">Gmail</p>
    <ul class="mt-1 list-inside list-disc space-y-0.5">
      <li>Turn on <strong>2-Step Verification</strong></li>
      <li>Create an <strong>App Password</strong> at <a class="text-emerald-700 underline" href="https://myaccount.google.com/apppasswords" target="_blank" rel="noopener">myaccount.google.com/apppasswords</a></li>
      <li>Username = your full Gmail · Password = the 16-character app password</li>
    </ul>
  </div>
  <div class="rounded-lg border border-slate-200 bg-white px-4 py-3">
    <p class="font-medium text-slate-800">Outlook / Microsoft 365</p>
    <ul class="mt-1 list-inside list-disc space-y-0.5">
      <li>Host is set automatically to <code>smtp.office365.com</code></li>
      <li>Port <strong>587</strong> + TLS</li>
      <li>Username = full Microsoft email address</li>
    </ul>
  </div>
  <div class="rounded-lg border border-slate-200 bg-white px-4 py-3">
    <p class="font-medium text-slate-800">Resend</p>
    <ul class="mt-1 list-inside list-disc space-y-0.5">
      <li>Create an API key at <a class="text-emerald-700 underline" href="https://resend.com/api-keys" target="_blank" rel="noopener">resend.com/api-keys</a></li>
      <li>Username is always <code>resend</code> · Password = API key (<code>re_…</code>)</li>
      <li>Verify your sending domain in the Resend dashboard</li>
    </ul>
  </div>
  <div class="rounded-lg border border-slate-200 bg-white px-4 py-3">
    <p class="font-medium text-slate-800">Hostinger</p>
    <ul class="mt-1 list-inside list-disc space-y-0.5">
      <li>Host: <code>smtp.hostinger.com</code> · Port <strong>465</strong> + SSL (or 587 + TLS)</li>
      <li>Username = full mailbox email created under Hostinger → Emails</li>
    </ul>
  </div>
</div>

@push('scripts')
<script>
(function () {
  var providers = @json($providers);
  var select = document.getElementById('mailer-select');
  var help = document.getElementById('mailer-help');
  var host = document.getElementById('field-host');
  var port = document.getElementById('field-port');
  var enc = document.getElementById('field-encryption');
  var user = document.getElementById('field-username');
  var labelUser = document.getElementById('label-username');
  var labelPass = document.getElementById('label-password');
  var smtpFields = document.getElementById('smtp-fields');

  function applyProvider(key, fillDefaults) {
    var p = providers[key] || providers.smtp;
    if (help) help.textContent = p.help || '';

    var needsSmtp = !['sendmail', 'log'].includes(key);
    if (smtpFields) smtpFields.style.display = needsSmtp ? '' : 'none';

    if (labelUser) labelUser.textContent = p.username_hint ? ('Username — ' + p.username_hint) : 'Username';
    if (labelPass) labelPass.textContent = p.password_hint ? ('Password — ' + p.password_hint) : 'Password';

    if (!fillDefaults) return;

    if (p.host) {
      host.value = p.host;
      host.readOnly = true;
      host.classList.add('bg-slate-50');
    } else if (key === 'smtp') {
      host.readOnly = false;
      host.classList.remove('bg-slate-50');
    } else {
      host.readOnly = false;
      host.classList.remove('bg-slate-50');
    }

    if (p.port) port.value = p.port;
    if (p.encryption) enc.value = p.encryption;

    if (key === 'resend' && (!user.value || user.value === '')) {
      user.value = 'resend';
    }
  }

  if (select) {
    select.addEventListener('change', function () {
      applyProvider(select.value, true);
    });
    applyProvider(select.value, false);

    // Lock host for preset providers on load
    var p = providers[select.value];
    if (p && p.host && host) {
      host.readOnly = true;
      host.classList.add('bg-slate-50');
      if (!host.value) host.value = p.host;
    }
  }
})();
</script>
@endpush
@endsection
