@extends('layouts.admin')
@section('title', $category->exists ? 'Edit category' : 'Add category')
@section('content')
<form method="post" action="{{ $category->exists ? route('admin.categories.update', $category) : route('admin.categories.store') }}" class="max-w-2xl space-y-4 rounded-xl border bg-white p-6">
    @csrf
    @if($category->exists) @method('PUT') @endif
    <div>
        <label class="text-sm font-medium">Name</label>
        <input name="name" value="{{ old('name', $category->name) }}" required class="mt-1 w-full rounded-lg border px-3 py-2 text-sm">
    </div>
    <div>
        <label class="text-sm font-medium">Slug</label>
        <input name="slug" value="{{ old('slug', $category->slug) }}" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm" placeholder="auto from name">
    </div>
    <div>
        <label class="text-sm font-medium">Parent</label>
        <select name="parent_id" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm">
            <option value="">— None —</option>
            @foreach($parents as $p)
                <option value="{{ $p->id }}" @selected(old('parent_id', $category->parent_id) == $p->id)>{{ $p->name }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="text-sm font-medium">Description</label>
        <textarea name="description" rows="3" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm">{{ old('description', $category->description) }}</textarea>
    </div>
    <div>
        <label class="text-sm font-medium">Attribute schema (JSON array of field keys for this category)</label>
        <textarea name="attribute_schema_json" rows="6" class="mt-1 w-full rounded-lg border px-3 py-2 font-mono text-xs" placeholder='[{"key":"compatible_browsers","label":"Compatible Browsers"}]'>{{ old('attribute_schema_json', $category->attribute_schema ? json_encode($category->attribute_schema, JSON_PRETTY_PRINT) : '') }}</textarea>
    </div>
    <button class="rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white">Save</button>
</form>
@endsection
