@extends('layouts.admin')
@section('title', 'Schema / JSON-LD')
@section('content')
<a href="{{ route('admin.settings.hub') }}" class="text-sm text-emerald-700">← Settings</a>
<h1 class="mt-2 text-xl font-bold">Schema & JSON-LD</h1>
<p class="text-sm text-slate-500">Structured data for Google (Organization, WebSite, Product).</p>

<form method="post" action="{{ route('admin.settings.schema.update') }}" class="mt-6 max-w-2xl space-y-6">
  @csrf @method('PUT')

  <section class="rounded-xl border bg-white p-6 space-y-3">
    <label class="flex items-center gap-2 text-sm font-medium"><input type="checkbox" name="organization_enabled" value="1" @checked(!empty($schema['organization_enabled']))> Organization schema</label>
    <input name="organization_name" value="{{ $schema['organization_name'] ?? '' }}" class="w-full rounded-lg border px-3 py-2 text-sm" placeholder="Organization name">
    <input name="organization_url" value="{{ $schema['organization_url'] ?? '' }}" class="w-full rounded-lg border px-3 py-2 text-sm" placeholder="https://yoursite.com">
    <input name="organization_logo" value="{{ $schema['organization_logo'] ?? '' }}" class="w-full rounded-lg border px-3 py-2 text-sm" placeholder="Logo URL">
    <textarea name="organization_same_as" rows="3" class="w-full rounded-lg border px-3 py-2 text-sm" placeholder="Social profile URLs, one per line">{{ implode("\n", $schema['organization_same_as'] ?? []) }}</textarea>
  </section>

  <section class="rounded-xl border bg-white p-6 space-y-3">
    <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="website_enabled" value="1" @checked(!empty($schema['website_enabled']))> WebSite + SearchAction</label>
    <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="product_enabled" value="1" @checked(!empty($schema['product_enabled']))> Product schema on item pages</label>
    <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="breadcrumb_enabled" value="1" @checked(!empty($schema['breadcrumb_enabled']))> BreadcrumbList</label>
  </section>

  <section class="rounded-xl border bg-white p-6">
    <label class="text-sm font-medium">Custom JSON-LD (optional raw script body)</label>
    <textarea name="custom_json_ld" rows="8" class="mt-2 w-full rounded-lg border px-3 py-2 font-mono text-xs" placeholder='{"@context":"https://schema.org",...}'>{{ $schema['custom_json_ld'] ?? '' }}</textarea>
  </section>

  <button class="rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white">Save schema settings</button>
</form>
@endsection
