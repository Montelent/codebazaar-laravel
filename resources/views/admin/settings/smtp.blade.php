@extends('layouts.admin')
@section('title', 'SMTP / Email')
@section('content')
<a href="{{ route('admin.settings.hub') }}" class="text-sm text-emerald-700">← Settings hub</a>

<div class="mt-2 flex flex-wrap items-start justify-between gap-3">
  <div>
    <h1 class="text-xl font-bold">SMTP / Email</h1>
    <p class="mt-1 text-sm text-slate-500">Configure outgoing mail for order receipts, verification emails, newsletter, and password resets.</p>
  </div>
</div>

<form method="post" action="{{ route('admin.settings.smtp.update') }}" class="mt-6 max-w-2xl space-y-6">
  @csrf @method('PUT')

  <section class="rounded-xl border bg-white p-6 space-y-4">
    <label class="flex items-center gap-2 text-sm font-medium">
      <input type="checkbox" name="enabled" value="1" @checked(!empty($smtp['enabled']))>
      Enable custom SMTP (override .env mail settings)
    </label>

    <div>
      <label class="text-sm font-medium">Mailer</label>
      <select name="mailer" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm">
        <option value="smtp" @selected(($smtp['mailer'] ?? 'smtp') === 'smtp')>SMTP</option>
        <option value="sendmail" @selected(($smtp['mailer'] ?? '') === 'sendmail')>Sendmail</option>
        <option value="log" @selected(($smtp['mailer'] ?? '') === 'log')>Log only (dev / debug)</option>
      </select>
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
      <div class="sm:col-span-2">
        <label class="text-sm font-medium">SMTP host</label>
        <input name="host" value="{{ old('host', $smtp['host'] ?? '') }}" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm" placeholder="smtp.gmail.com / smtp.hostinger.com">
      </div>
      <div>
        <label class="text-sm font-medium">Port</label>
        <input type="number" name="port" value="{{ old('port', $smtp['port'] ?? 587) }}" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm">
        <p class="mt-1 text-[11px] text-slate-400">587 (TLS) · 465 (SSL) · 25 (none)</p>
      </div>
      <div>
        <label class="text-sm font-medium">Encryption</label>
        <select name="encryption" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm">
          @php $enc = old('encryption', $smtp['encryption'] ?? 'tls'); if ($enc === '') $enc = 'none'; @endphp
          <option value="tls" @selected($enc === 'tls')>TLS</option>
          <option value="ssl" @selected($enc === 'ssl')>SSL</option>
          <option value="none" @selected($enc === 'none')>None</option>
        </select>
      </div>
      <div>
        <label class="text-sm font-medium">Username</label>
        <input name="username" value="{{ old('username', $smtp['username'] ?? '') }}" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm" autocomplete="off">
      </div>
      <div>
        <label class="text-sm font-medium">Password</label>
        <input type="password" name="password" value="" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm" autocomplete="new-password" placeholder="{{ !empty($smtp['password_set']) ? '••••••••  (leave blank to keep)' : 'SMTP password' }}">
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

  <button class="rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white">Save SMTP settings</button>
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

<div class="mt-6 max-w-2xl rounded-lg border border-slate-200 bg-white px-4 py-3 text-xs text-slate-600">
  <p class="font-medium text-slate-800">Hostinger tips</p>
  <ul class="mt-1 list-inside list-disc space-y-0.5">
    <li>Host: <code>smtp.hostinger.com</code></li>
    <li>Port <strong>465</strong> + SSL, or <strong>587</strong> + TLS</li>
    <li>Username = full mailbox email (e.g. <code>noreply@yourdomain.com</code>)</li>
    <li>Use an email account created under Hostinger → Emails</li>
  </ul>
</div>
@endsection
