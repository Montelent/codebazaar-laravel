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
        <p class="mt-1 text-xs text-slate-500">Used to match Attributes presets (e.g. <code>wordpress</code>, <code>php-scripts</code>).</p>
    </div>
    <div>
        <label class="text-sm font-medium">Parent category</label>
        <select name="parent_id" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm">
            <option value="">— None (this is a top-level / parent category) —</option>
            @foreach($parents as $p)
                <option value="{{ $p->id }}" @selected(old('parent_id', $category->parent_id) == $p->id)>{{ $p->name }}</option>
            @endforeach
        </select>
        <p class="mt-1 text-xs text-slate-500">Choose a parent to create a <strong>sub-category</strong>. Leave empty for a main category.</p>
    </div>
    <div>
        <label class="text-sm font-medium">Description</label>
        <textarea name="description" rows="3" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm">{{ old('description', $category->description) }}</textarea>
    </div>
    <div class="rounded-lg border border-slate-100 bg-slate-50 p-3 text-sm text-slate-600">
        <strong>Attributes:</strong> configure selectable options under
        <a href="{{ route('admin.attributes.index') }}" class="text-emerald-700">Admin → Attributes</a>
        using this category’s slug. Sub-categories inherit the parent’s attribute set when they have none of their own.
    </div>
    <button class="rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white">Save</button>
</form>
@endsection
