@extends('layouts.admin')
@section('title', 'File storage')
@section('content')
<a href="{{ route('admin.settings.hub') }}" class="text-sm text-emerald-700">← Settings</a>
<h1 class="mt-2 text-xl font-bold">File storage</h1>
<p class="text-sm text-slate-500">Default target for uploads (thumbnail, screenshots, main file, media library). External URL and Google Drive links always work without credentials.</p>

<form method="post" action="{{ route('admin.settings.storage.update') }}" class="mt-6 max-w-3xl space-y-6">
  @csrf
  @method('PUT')

  <div class="rounded-xl border bg-white p-5 shadow-sm">
    <label class="text-sm font-medium">Default upload disk</label>
    <select name="default_disk" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm">
      @foreach(['local' => 'Local server', 's3' => 'Amazon S3', 'backblaze' => 'Backblaze B2', 'idrive' => 'iDrive e2'] as $k => $label)
        <option value="{{ $k }}" @selected(($config['default_disk'] ?? 'local') === $k)>{{ $label }}</option>
      @endforeach
    </select>
    <p class="mt-2 text-xs text-slate-500">Product form and Media library use this when you choose “Upload (default disk)”.</p>
  </div>

  @foreach([
    's3' => ['Amazon S3', 'Leave Endpoint empty for standard AWS. Public URL optional (CloudFront).'],
    'backblaze' => ['Backblaze B2', 'Use S3-compatible endpoint, e.g. https://s3.us-west-004.backblazeb2.com'],
    'idrive' => ['iDrive e2', 'Use your e2 endpoint from the iDrive dashboard.'],
  ] as $key => [$title, $hint])
  @php $c = $config[$key] ?? []; @endphp
  <div class="rounded-xl border bg-white p-5 shadow-sm space-y-3">
    <h2 class="font-semibold">{{ $title }}</h2>
    <p class="text-xs text-slate-500">{{ $hint }}</p>
    <div class="grid gap-3 sm:grid-cols-2">
      <div><label class="text-xs font-medium">Access key</label>
        <input name="{{ $key }}[key]" value="{{ $c['key'] ?? '' }}" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm" autocomplete="off"></div>
      <div><label class="text-xs font-medium">Secret key</label>
        <input name="{{ $key }}[secret]" type="password" value="" placeholder="{{ !empty($c['secret']) ? '•••••••• (unchanged)' : '' }}" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm" autocomplete="new-password"></div>
      <div><label class="text-xs font-medium">Region</label>
        <input name="{{ $key }}[region]" value="{{ $c['region'] ?? '' }}" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm"></div>
      <div><label class="text-xs font-medium">Bucket</label>
        <input name="{{ $key }}[bucket]" value="{{ $c['bucket'] ?? '' }}" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm"></div>
      <div class="sm:col-span-2"><label class="text-xs font-medium">Endpoint (S3-compatible)</label>
        <input name="{{ $key }}[endpoint]" value="{{ $c['endpoint'] ?? '' }}" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm" placeholder="https://..."></div>
      <div class="sm:col-span-2"><label class="text-xs font-medium">Public base URL (optional CDN)</label>
        <input name="{{ $key }}[public_url]" value="{{ $c['public_url'] ?? '' }}" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm" placeholder="https://cdn.example.com"></div>
      <label class="flex items-center gap-2 text-sm sm:col-span-2">
        <input type="checkbox" name="{{ $key }}[path_style]" value="1" @checked(!empty($c['path_style']))>
        Path-style URLs (recommended for Backblaze / iDrive)
      </label>
    </div>
  </div>
  @endforeach

  <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-600">
    <strong>Google Drive:</strong> configure nothing here — on products / media, choose “Google Drive (URL)” and paste a share link. Links like
    <code class="text-xs">drive.google.com/file/d/ID/view</code> are normalized when possible. Files must be shared as “Anyone with the link”.
  </div>

  <button class="rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white">Save storage settings</button>
</form>
@endsection
