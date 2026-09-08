@extends('layouts.admin')
@section('title', 'License activation')
@section('content')
<div class="mx-auto max-w-xl">
  <h1 class="text-xl font-bold">License activation</h1>
  <p class="mt-1 text-sm text-slate-500">
    Enter your <strong>Envato / CodeCanyon purchase code</strong> to unlock the full admin.
    Until activated, only <strong>Basic settings</strong> and <strong>Blog</strong> remain available.
  </p>

  @php $active = !empty($status['active']); @endphp

  @if($active)
    <div class="mt-6 rounded-xl border border-emerald-200 bg-emerald-50 p-5 text-sm text-emerald-900">
      <p class="font-semibold">Product is activated</p>
      <ul class="mt-2 space-y-1 text-emerald-800">
        <li>Type: <strong>{{ $status['type'] === 'master' ? 'Author master unlock' : 'Envato license' }}</strong></li>
        @if(!empty($status['domain']))
          <li>Domain: <code>{{ $status['domain'] }}</code></li>
        @endif
        @if(!empty($status['buyer']))
          <li>Buyer: {{ $status['buyer'] }}</li>
        @endif
        @if(!empty($status['code_hint']))
          <li>Code: {{ $status['code_hint'] }}</li>
        @endif
        @if(!empty($status['activated_at']))
          <li>Activated: {{ $status['activated_at'] }}</li>
        @endif
      </ul>
      <form method="post" action="{{ route('admin.activation.destroy') }}" class="mt-4" onsubmit="return confirm('Clear activation on this install?')">
        @csrf @method('DELETE')
        <button class="text-xs font-medium text-red-700 underline">Deactivate this install</button>
      </form>
    </div>
  @else
    <form method="post" action="{{ route('admin.activation.update') }}" class="mt-6 space-y-4 rounded-xl border bg-white p-6 shadow-sm">
      @csrf
      <div>
        <label class="text-sm font-medium">Purchase code / activation key</label>
        <input name="purchase_code" value="{{ old('purchase_code') }}" required autocomplete="off"
               class="mt-1 w-full rounded-lg border px-3 py-2.5 font-mono text-sm"
               placeholder="xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx">
        <p class="mt-1 text-xs text-slate-500">
          Find it under Envato → Downloads → License certificate / purchase code.
        </p>
      </div>
      <button class="w-full rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700">
        Activate license
      </button>
    </form>

    <div class="mt-6 rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 text-xs text-slate-600">
      <p class="font-medium text-slate-800">While locked you can still:</p>
      <ul class="mt-1 list-inside list-disc">
        <li><a class="text-emerald-700 underline" href="{{ route('admin.settings.general') }}">Basic / general settings</a></li>
        <li><a class="text-emerald-700 underline" href="{{ route('admin.blog.index') }}">Blog posts</a></li>
        <li>System tools (migrate / cache) if needed for install</li>
      </ul>
    </div>
  @endif
</div>
@endsection
