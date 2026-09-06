@extends('layouts.admin')
@section('title', 'Media library')
@section('content')
<h1 class="text-xl font-bold">Media library</h1>
<p class="mt-1 text-sm text-slate-500">Upload to local disk or register an external URL. Copy the URL into product thumbnail / screenshots.</p>

<div class="mt-6 grid gap-6 lg:grid-cols-2">
  <form method="post" action="{{ route('admin.media.store') }}" enctype="multipart/form-data" class="rounded-xl border bg-white p-6 space-y-3">
    @csrf
    <h2 class="font-semibold">Upload file</h2>
    <input type="file" name="file" required class="block w-full text-sm">
    <input name="alt" placeholder="Alt text (optional)" class="w-full rounded-lg border px-3 py-2 text-sm">
    <p class="text-xs text-slate-500">Max ~20MB. jpg, png, gif, webp, svg, pdf, zip.</p>
    <button class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white">Upload</button>
  </form>
  <form method="post" action="{{ route('admin.media.store') }}" class="rounded-xl border bg-white p-6 space-y-3">
    @csrf
    <h2 class="font-semibold">External URL</h2>
    <input name="external_url" type="url" required placeholder="https://cdn.example.com/image.jpg" class="w-full rounded-lg border px-3 py-2 text-sm">
    <input name="alt" placeholder="Alt text (optional)" class="w-full rounded-lg border px-3 py-2 text-sm">
    <button class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold">Add URL</button>
  </form>
</div>

<div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
@forelse($assets as $a)
  <div class="rounded-xl border bg-white p-3 text-sm">
    @if(str_starts_with((string)$a->mime_type, 'image/') || preg_match('/\.(jpe?g|png|gif|webp|svg)$/i', $a->url))
      <img src="{{ $a->url }}" alt="{{ $a->alt }}" class="mb-2 h-28 w-full rounded-lg object-cover bg-slate-100">
    @else
      <div class="mb-2 flex h-28 items-center justify-center rounded-lg bg-slate-100 text-xs text-slate-500">{{ $a->filename }}</div>
    @endif
    <input readonly value="{{ $a->url }}" class="w-full rounded border bg-slate-50 px-2 py-1 font-mono text-[10px]" onclick="this.select()">
    <form method="post" action="{{ route('admin.media.destroy', $a) }}" class="mt-2" onsubmit="return confirm('Delete?')">@csrf @method('DELETE')
      <button class="text-xs text-red-600">Delete</button>
    </form>
  </div>
@empty
  <p class="text-slate-500">No media yet. Run migrations if this is empty after upload errors.</p>
@endforelse
</div>
{{ $assets instanceof \Illuminate\Contracts\Pagination\Paginator ? $assets->links() : '' }}
@endsection
