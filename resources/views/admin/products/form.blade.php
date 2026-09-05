@extends('layouts.app')
@section('title', ($item->exists ? 'Edit' : 'Add') . ' product · Admin')
@section('content')
<h1 class="text-2xl font-bold">{{ $item->exists ? 'Edit product' : 'Add product' }}</h1>
<form method="post" action="{{ $item->exists ? route('admin.products.update', $item) : route('admin.products.store') }}" class="mt-6 max-w-2xl space-y-4 rounded-xl border bg-white p-6">
    @csrf
    @if($item->exists) @method('PUT') @endif
    <div><label class="text-sm font-medium">Title</label>
        <input name="title" value="{{ old('title', $item->title) }}" required class="mt-1 w-full rounded-lg border px-3 py-2 text-sm"></div>
    <div><label class="text-sm font-medium">Slug</label>
        <input name="slug" value="{{ old('slug', $item->slug) }}" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm"></div>
    <div><label class="text-sm font-medium">Description (HTML allowed)</label>
        <textarea name="description" rows="6" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm">{{ old('description', $item->description) }}</textarea></div>
    <label class="flex items-center gap-2 text-sm">
        <input type="checkbox" name="is_free" value="1" @checked(old('is_free', $item->is_free || $item->regular_price <= 0))> Free product
    </label>
    <div class="grid gap-3 sm:grid-cols-3">
        <div><label class="text-sm font-medium">Regular</label>
            <input type="number" step="0.01" name="regular_price" value="{{ old('regular_price', $item->regular_price) }}" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm"></div>
        <div><label class="text-sm font-medium">Extended</label>
            <input type="number" step="0.01" name="extended_price" value="{{ old('extended_price', $item->extended_price) }}" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm"></div>
        <div><label class="text-sm font-medium">Sale</label>
            <input type="number" step="0.01" name="sale_price_regular" value="{{ old('sale_price_regular', $item->sale_price_regular) }}" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm"></div>
    </div>
    <div><label class="text-sm font-medium">Category</label>
        <select name="category_id" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm">
            <option value="">—</option>
            @foreach($categories as $c)
                <option value="{{ $c->id }}" @selected(old('category_id', $item->category_id) == $c->id)>{{ $c->name }}</option>
            @endforeach
        </select></div>
    <div><label class="text-sm font-medium">Thumbnail URL</label>
        <input name="thumbnail_url" value="{{ old('thumbnail_url', $item->thumbnail_url) }}" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm"></div>
    <div><label class="text-sm font-medium">Demo URL</label>
        <input name="demo_url" value="{{ old('demo_url', $item->demo_url) }}" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm"></div>
    <div><label class="text-sm font-medium">Main download file URL</label>
        <input name="main_file_url" value="{{ old('main_file_url', $item->main_file_url) }}" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm"></div>
    <div><label class="text-sm font-medium">Features (one per line)</label>
        <textarea name="features_text" rows="4" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm">{{ old('features_text', is_array($item->features) ? implode("\n", $item->features) : '') }}</textarea></div>
    <div><label class="text-sm font-medium">Screenshots (URLs, one per line)</label>
        <textarea name="gallery_text" rows="3" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm">{{ old('gallery_text', is_array($item->gallery_urls) ? implode("\n", $item->gallery_urls) : '') }}</textarea></div>
    <label class="flex items-center gap-2 text-sm">
        <input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $item->is_featured))> Featured
    </label>
    <div class="flex gap-3">
        <button class="rounded-lg bg-emerald-600 px-5 py-2 font-semibold text-white">Save</button>
        @if($item->exists)
            <a href="{{ route('item.show', [$item->slug, $item->id]) }}" target="_blank" class="rounded-lg border px-4 py-2 text-sm">View on site</a>
        @endif
    </div>
</form>
@endsection
