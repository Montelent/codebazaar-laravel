@extends('layouts.admin')
@section('title', 'Categories')
@section('content')
<div class="mb-4 flex flex-wrap items-center justify-between gap-3">
    <div>
        <h1 class="text-xl font-bold">Categories</h1>
        <p class="text-sm text-slate-500">Top-level categories and sub-categories (set Parent when adding a sub-category).</p>
    </div>
    <a href="{{ route('admin.categories.create') }}" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white">Add category / sub-category</a>
</div>

<div class="space-y-3 md:hidden">
  @forelse($categories as $c)
    <div class="rounded-xl border bg-white p-4 shadow-sm">
      <div class="flex items-start justify-between gap-3">
        <div class="min-w-0">
          <p class="font-semibold text-slate-900">
            @if($c->parent_id)<span class="mr-1 text-slate-400">↳</span>@endif
            {{ $c->name }}
          </p>
          <p class="mt-1 text-xs text-slate-500">
            @if($c->parent_id)
              <span class="rounded-full bg-sky-50 px-2 py-0.5 text-xs font-medium text-sky-700">Sub</span>
              · parent: {{ $c->parent?->name ?? '—' }}
            @else
              <span class="rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-medium text-emerald-700">Parent</span>
            @endif
            · {{ $c->slug }}
          </p>
        </div>
        <div class="flex shrink-0 flex-col items-end gap-2 text-sm">
          <a href="{{ route('admin.categories.edit', $c) }}" class="font-medium text-emerald-700">Edit</a>
          <form method="post" action="{{ route('admin.categories.destroy', $c) }}" onsubmit="return confirm('Delete?')">@csrf @method('DELETE')
            <button class="text-red-600">Delete</button>
          </form>
        </div>
      </div>
    </div>
  @empty
    <p class="rounded-xl border border-dashed bg-white p-6 text-center text-sm text-slate-500">No categories yet.</p>
  @endforelse
</div>

<div class="hidden overflow-x-auto rounded-xl border bg-white md:block">
<table class="w-full min-w-[640px] text-left text-sm">
<thead class="border-b bg-slate-50 text-slate-500">
<tr>
  <th class="px-4 py-3">Name</th>
  <th class="px-3 py-3">Slug</th>
  <th class="px-3 py-3">Type</th>
  <th class="px-3 py-3">Parent</th>
  <th class="px-4 py-3"></th>
</tr>
</thead>
<tbody>
@foreach($categories as $c)
<tr class="border-b last:border-0">
    <td class="px-4 py-3 font-medium {{ $c->parent_id ? 'pl-8' : '' }}">
      @if($c->parent_id)<span class="mr-1 text-slate-400">↳</span>@endif
      {{ $c->name }}
    </td>
    <td class="px-3 py-3">{{ $c->slug }}</td>
    <td class="px-3 py-3">
      @if($c->parent_id)
        <span class="rounded-full bg-sky-50 px-2 py-0.5 text-xs font-medium text-sky-700">Sub-category</span>
      @else
        <span class="rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-medium text-emerald-700">Parent</span>
      @endif
    </td>
    <td class="px-3 py-3">{{ $c->parent?->name ?? '—' }}</td>
    <td class="px-4 py-3 text-right whitespace-nowrap">
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

<div class="mt-4">{{ $categories->links() }}</div>
@endsection
