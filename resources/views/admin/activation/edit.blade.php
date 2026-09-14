@extends('layouts.admin')
@section('title', 'License activation')
@section('content')
<div class="mx-auto max-w-xl">
  <h1 class="text-xl font-bold">License activation</h1>
  <p class="mt-1 text-sm text-slate-500">
    Enter your <strong>CodeBazaar purchase code</strong> to unlock the full admin panel.
    Until then, only basic settings and blog remain available.
  </p>

  @php
    $active = !empty($status['active']);
    $typeLabel = match ($status['type'] ?? null) {
      'master' => 'Author license',
      'jigsource' => 'JigSource',
      'envato' => 'Envato / CodeCanyon',
      'license_server' => 'Verified',
      default => ($status['type'] ?? 'Active'),
    };
  @endphp

  @if($active)
    <div class="mt-6 rounded-xl border border-emerald-200 bg-emerald-50 p-5 text-sm text-emerald-900">
      <p class="font-semibold">Your license is active</p>
      <ul class="mt-2 space-y-1 text-emerald-800">
        <li>Source: <strong>{{ $typeLabel }}</strong></li>
        @if(!empty($status['domain']))
          <li>Domain: <code>{{ $status['domain'] }}</code></li>
        @endif
        @if(!empty($status['item_name']))
          <li>Product: {{ $status['item_name'] }}</li>
        @endif
        @if(!empty($status['code_hint']) && ($status['code_hint'] ?? '') !== 'master')
          <li>Code: {{ $status['code_hint'] }}</li>
        @endif
        @if(!empty($status['activated_at']))
          <li>Activated: {{ $status['activated_at'] }}</li>
        @endif
      </ul>
      <form method="post" action="{{ route('admin.activation.destroy') }}" class="mt-4" onsubmit="return confirm('Remove activation from this site?')">
        @csrf @method('DELETE')
        <button class="text-xs font-medium text-red-700 underline">Deactivate</button>
      </form>
    </div>
  @else
    <form method="post" action="{{ route('admin.activation.update') }}" class="mt-6 space-y-4 rounded-xl border bg-white p-6 shadow-sm">
      @csrf
      <div>
        <label class="text-sm font-medium">Purchase code</label>
        <input name="purchase_code" value="{{ old('purchase_code') }}" required autocomplete="off"
               class="mt-1 w-full rounded-lg border px-3 py-2.5 font-mono text-sm"
               placeholder="Paste your purchase code">
        <p class="mt-1 text-xs text-slate-500">
          Find it in your JigSource or CodeCanyon order / license certificate.
        </p>
      </div>
      <button class="w-full rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700">
        Activate license
      </button>
    </form>

    <div class="mt-6 rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 text-xs text-slate-600">
      <p class="font-medium text-slate-800">Available while locked:</p>
      <ul class="mt-1 list-inside list-disc">
        <li><a class="text-emerald-700 underline" href="{{ route('admin.settings.general') }}">General settings</a></li>
        <li><a class="text-emerald-700 underline" href="{{ route('admin.blog.index') }}">Blog</a></li>
      </ul>
    </div>
  @endif
</div>
@endsection
