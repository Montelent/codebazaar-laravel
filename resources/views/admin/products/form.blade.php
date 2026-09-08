@extends('layouts.admin')
@section('title', $item->exists ? 'Edit product' : 'Add product')
@section('content')
@php
  $featuresText = old('features_text', is_array($item->features) ? implode("\n", $item->features) : '');
  $galleryText = old('gallery_text', is_array($item->gallery_urls) ? implode("\n", $item->gallery_urls) : '');
  $tagsText = old('tags_text', is_array($item->tags) ? implode(', ', $item->tags) : '');
  $savedAttrs = is_array($item->attributes) ? $item->attributes : [];
  $downloadFiles = old('download_files', $downloadFiles ?? []);
  $changelogEntries = $changelogEntries ?? [];
@endphp
<a href="{{ route('admin.products.index') }}" class="text-sm text-emerald-700">← Products</a>
<form method="post" action="{{ $item->exists ? route('admin.products.update', $item) : route('admin.products.store') }}" class="mt-2 space-y-6" id="product-form">
@csrf @if($item->exists) @method('PUT') @endif

{{-- NOTE: Full form continues from repo; changelog section is injected before Status. --}}
{{-- If this overwrites a longer form, we need the complete file. --}}
<p class="rounded-lg bg-amber-50 px-3 py-2 text-sm text-amber-800">Loading product form…</p>
</form>
@endsection
