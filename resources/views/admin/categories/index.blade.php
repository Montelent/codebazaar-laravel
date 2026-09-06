@extends('layouts.admin')
@section('title', 'Categories')
@section('content')
<div class="mb-4 flex items-center justify-between">
    <h1 class="text-xl font-bold">Categories</h1>
    <a href="{{ route('admin.categories.create') }}" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white">Add category</a>
</div>
<div class="overflow-hidden rounded-xl border bg-white">
<table class="w-full text-left text-sm">
<thead class="border-b bg-slate-50 text-slate-500"><tr><th class="px-4 py-3">Name</th><th>Slug</th><th>Parent</th><th></th></tr></thead>
<tbody>
@foreach($categories as $c)
<tr class="border-b">
    <td class="px-4 py-3 font-medium">{{ $c->name }}</td>
    <td>{{ $c->slug }}</td>
    <td>{{ $c->parent?->name ?? '—' }}</td>
    <td class="px-4 py-3 text-right">
        <a href="{{ route('admin.categories.edit', $c) }}" class="text-emerald-700">Edit</a>
        <form class="inline" method="post" action="{{ route('admin.categories.destroy', $c) }}" onsubmit="return confirm('Delete?')">@csrf @method('DELETE')
            <button class="ml-2 text-red-600">Delete</button>
        </form>
    </td>
</tr>
@endforeach
</tbody>
</table>
</div>
{{ $categories->links() }}
@endsection
