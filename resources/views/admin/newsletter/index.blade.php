@extends('layouts.admin')
@section('title', 'Newsletter')
@section('content')
@php
  $shortcodes = $shortcodes ?? [];
  $defaultBody = $defaultBody ?? '';
@endphp

<div class="mb-4 flex flex-wrap items-start justify-between gap-3">
  <div>
    <h1 class="text-xl font-bold">Newsletter</h1>
    <p class="mt-1 text-sm text-slate-500">
      {{ $subscribers->count() }} newsletter subscribers · {{ $allUsers->count() }} total users.
      Uses Admin → Settings → SMTP when enabled.
    </p>
  </div>
</div>

<form method="post" action="{{ route('admin.newsletter.send') }}" class="max-w-3xl space-y-4 rounded-xl border bg-white p-6" id="newsletter-form">
  @csrf

  <div class="grid gap-4 sm:grid-cols-2">
    <div>
      <label class="text-sm font-medium">Template</label>
      <select name="template" id="nl-template" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm">
        <option value="weekly">Weekly digest (products + posts)</option>
        <option value="custom">Custom message</option>
      </select>
    </div>
    <div>
      <label class="text-sm font-medium">Audience</label>
      <select name="audience" id="nl-audience" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm">
        <option value="newsletter">Newsletter opt-in only</option>
        <option value="verified">Email-verified users</option>
        <option value="all">All registered users</option>
        <option value="first_100_newsletter">First 100 newsletter subscribers (most recent)</option>
        <option value="first_100_all">First 100 users (most recent)</option>
        <option value="selected">Selected users only</option>
      </select>
    </div>
  </div>

  <div id="selected-users-box" class="hidden rounded-lg border border-slate-200 bg-slate-50 p-3">
    <label class="text-sm font-medium">Select users</label>
    <p class="mt-0.5 text-xs text-slate-500">Hold Ctrl/Cmd to multi-select.</p>
    <select name="user_ids[]" multiple size="10" class="mt-2 w-full rounded-lg border bg-white px-2 py-2 text-sm">
      @foreach($allUsers as $u)
        <option value="{{ $u->id }}">
          {{ $u->email }}@if($u->name) — {{ $u->name }}@endif
          [{{ $u->role }}]{{ $u->newsletter ? ' · newsletter' : '' }}
        </option>
      @endforeach
    </select>
  </div>

  <div>
    <label class="text-sm font-medium">Subject</label>
    <input name="subject" required value="{{ old('subject', 'What’s new this week on '.config('app.name', 'CodeBazaar')) }}" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm">
  </div>

  <div>
    <div class="flex flex-wrap items-center justify-between gap-2">
      <label class="text-sm font-medium">Body</label>
      <div class="flex flex-wrap gap-1">
        @foreach(array_keys($shortcodes) as $code)
          <button type="button" data-shortcode="{{ $code }}" class="nl-insert rounded border bg-slate-50 px-2 py-0.5 text-[11px] font-mono text-slate-700 hover:bg-emerald-50 hover:text-emerald-800">{{ $code }}</button>
        @endforeach
      </div>
    </div>
    <textarea name="body" id="nl-body" class="tinymce mt-1" required>{{ old('body', $defaultBody) }}</textarea>
    <details class="mt-2 text-xs text-slate-500">
      <summary class="cursor-pointer font-medium text-slate-600">Shortcode reference</summary>
      <ul class="mt-2 space-y-1">
        @foreach($shortcodes as $code => $desc)
          <li><code class="rounded bg-slate-100 px-1">{{ $code }}</code> — {{ $desc }}</li>
        @endforeach
      </ul>
    </details>
  </div>

  <button class="rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white" onclick="return confirm('Send this newsletter to the selected audience?')">Send newsletter</button>
</form>

<section class="mt-10 max-w-3xl">
  <h2 class="font-semibold">Subscribers (opt-in)</h2>
  <ul class="mt-3 max-h-64 divide-y overflow-y-auto rounded-xl border bg-white text-sm">
    @forelse($subscribers as $s)
      <li class="flex justify-between px-4 py-2">
        <span>{{ $s->email }} @if($s->name)<span class="text-slate-400">· {{ $s->name }}</span>@endif</span>
        <span class="text-xs {{ $s->email_verified_at ? 'text-emerald-600' : 'text-amber-600' }}">{{ $s->email_verified_at ? 'verified' : 'unverified' }}</span>
      </li>
    @empty
      <li class="px-4 py-3 text-slate-500">No newsletter subscribers yet.</li>
    @endforelse
  </ul>
</section>

@if(count($logs))
<section class="mt-10 max-w-3xl">
  <h2 class="font-semibold">Recent sends</h2>
  <ul class="mt-3 space-y-2 text-sm">
    @foreach($logs as $log)
      <li class="rounded-lg border bg-white px-4 py-2">{{ $log->subject }} · {{ $log->recipients }} recipients · {{ $log->created_at }}</li>
    @endforeach
  </ul>
</section>
@endif

@push('scripts')
<script>
(function () {
  var audience = document.getElementById('nl-audience');
  var box = document.getElementById('selected-users-box');
  var template = document.getElementById('nl-template');
  var defaultWeekly = @json($defaultBody);

  function toggleSelected() {
    if (!audience || !box) return;
    box.classList.toggle('hidden', audience.value !== 'selected');
  }
  if (audience) audience.addEventListener('change', toggleSelected);
  toggleSelected();

  if (template) {
    template.addEventListener('change', function () {
      if (template.value === 'weekly' && window.tinymce) {
        var ed = tinymce.get('nl-body');
        if (ed) ed.setContent(defaultWeekly);
      }
    });
  }

  document.querySelectorAll('.nl-insert').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var code = btn.getAttribute('data-shortcode');
      if (window.tinymce && tinymce.get('nl-body')) {
        tinymce.get('nl-body').insertContent(code);
      } else {
        var ta = document.getElementById('nl-body');
        if (ta) ta.value += code;
      }
    });
  });
})();
</script>
@endpush
@endsection
