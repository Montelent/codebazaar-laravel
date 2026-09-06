@extends('layouts.admin')
@section('title', 'License')
@section('content')
<form method="post" action="{{ $isNew ? route('admin.licenses.store') : route('admin.licenses.update', $license['id']) }}" class="max-w-2xl space-y-4 rounded-xl border bg-white p-6">
@csrf @unless($isNew) @method('PUT') @endunless
<div><label class="text-sm font-medium">ID (slug)</label><input name="id" value="{{ old('id', $license['id'] ?? '') }}" required class="mt-1 w-full rounded-lg border px-3 py-2 text-sm" {{ $isNew ? '' : 'readonly' }}></div>
<div><label class="text-sm font-medium">Name</label><input name="name" value="{{ old('name', $license['name'] ?? '') }}" required class="mt-1 w-full rounded-lg border px-3 py-2 text-sm"></div>
<div><label class="text-sm font-medium">Price label</label><input name="price_label" value="{{ old('price_label', $license['price_label'] ?? '') }}" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm"></div>
<div><label class="text-sm font-medium">Description</label><textarea name="description" class="tinymce">{{ old('description', $license['description'] ?? '') }}</textarea></div>
<button class="rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white">Save</button>
</form>
@endsection
