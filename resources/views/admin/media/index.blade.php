@extends('layouts.admin')
@section('title', 'Media library')
@section('content')
<div class="mb-2 flex flex-wrap items-center justify-between gap-3">
  <div>
    <h1 class="text-xl font-bold">Media library</h1>
    <p class="mt-1 text-sm text-slate-500">Upload to local / S3 / Backblaze / iDrive, or register external &amp; Google Drive URLs. Default disk: <strong>{{ $config['default_disk'] ?? 'local' }}</strong> — change in <a href="{{ route('admin.settings.storage') }}" class="text-emerald-700">File storage settings</a>.</p>
  </div>
</div>

<div class="mt-6 grid gap-6 lg:grid-cols-2">
  <form method="post" action="{{ route('admin.media.store') }}" enctype="multipart/form-data" class="rounded-xl border bg-white p-6 space-y-3">
    @csrf
    <h2 class="font-semibold">Upload file</h2>
    <input type="file" name="file" required class="block w-full text-sm">
    <div>
      <label class="text-xs font-medium">Storage</label>
      <select name="disk" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm">
        <option value="local">Local server</option>
        <option value="s3">Amazon S3</option>
        <option value="backblaze">Backblaze B2</option>
        <option value="idrive">iDrive e2</option>
      </select>
    </div>
    <input name="alt" placeholder="Alt text (optional)" class="w-full rounded-lg border px-3 py-2 text-sm">
    <p class="text-xs text-slate-500">Images, ZIP, PDF, etc. Max ~50MB (server limits may be lower).</p>
    <button class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white">Upload</button>
  </form>

  <form method="post" action="{{ route('admin.media.store') }}" class="rounded-xl border bg-white p-6 space-y-3">
    @csrf
    <h2 class="font-semibold">External / Google Drive URL</h2>
    <input name="external_url" type="url" required placeholder="https://… or Drive share link" class="w-full rounded-lg border px-3 py-2 text-sm">
    <select name="disk" class="w-full rounded-lg border px-3 py-2 text-sm">
      <option value="external">External URL</option>
      <option value="drive">Google Drive (URL)</option>
    </select>
    <input name="alt" placeholder="Alt text (optional)" class="w-full rounded-lg border px-3 py-2 text-sm">
    <button class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold">Add URL</button>
  </form>
</div>

<div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
@forelse($assets as $a)
  <div class="rounded-xl border bg-white p-3 text-sm">
    @if(str_starts_with((string)$a->mime_type, 'image/') || preg_match('/\.(jpe?g|png|gif|webp|svg)(\?|$)/i', $a->url))
      <img src="{{ $a->url }}" alt="{{ $a->alt }}" class="mb-2 h-28 w-full rounded-lg object-cover bg-slate-100">
    @else
      <div class="mb-2 flex h-28 items-center justify-center rounded-lg bg-slate-100 text-xs text-slate-500">{{ $a->filename }}</div>
    @endif
    <p class="mb-1 text-[10px] uppercase tracking-wide text-slate-400">{{ $a->disk }}</p>
    <input readonly value="{{ $a->url }}" class="w-full rounded border bg-slate-50 px-2 py-1 font-mono text-[10px]" onclick="this.select()">
    <form method="post" action="{{ route('admin.media.destroy', $a) }}" class="mt-2" onsubmit="return confirm('Delete?')">@csrf @method('DELETE')
      <button class="text-xs text-red-600">Delete</button>
    </form>
  </div>
@empty
  <p class="text-slate-500">No media yet.</p>
@endforelse
</div>
{{ $assets instanceof \Illuminate\Contracts\Pagination\Paginator ? $assets->links() : '' }}
@endsection
