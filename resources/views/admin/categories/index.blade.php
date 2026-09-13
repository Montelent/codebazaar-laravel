@extends('layouts.admin')
@section('title', 'Categories')
@section('content')
<div class="mb-4 flex flex-wrap items-center justify-between gap-3">
  <div>
    <h1 class="text-xl font-bold tracking-tight">Categories</h1>
    <p class="text-sm text-slate-500">Top-level categories and sub-categories</p>
  </div>
  <a href="{{ route('admin.categories.create') }}" class="inline-flex items-center rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700">+ Add category</a>
</div>

<div class="space-y-3 md:hidden">
  @forelse($categories as $c)
    @php
      $actions = [
        ['label' => 'Edit', 'href' => route('admin.categories.edit', $c)],
        ['label' => 'Delete', 'href' => route('admin.categories.destroy', $c), 'method' => 'DELETE', 'danger' => true, 'confirm' => 'Delete this category?'],
      ];
    @endphp
    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
      <div class="flex items-start justify-between gap-3">
        <div class="min-w-0 flex-1">
          <p class="font-semibold text-slate-900">
            @if($c->parent_id)<span class="mr-1 text-slate-400">↳</span>@endif
            {{ $c->name }}
          </p>
          <div class="mt-2 flex flex-wrap items-center gap-2">
            @if($c->parent_id)
              <span class="admin-status" style="background:#f0f9ff;color:#0369a1">Sub</span>
              <span class="text-xs text-slate-500">parent: {{ $c->parent?->name ?? '—' }}</span>
            @else
              <span class="admin-status admin-status-ok">Parent</span>
            @endif
            <span class="text-xs text-slate-400">{{ $c->slug }}</span>
          </div>
        </div>
        <x-admin-kebab :actions="$actions" />
      </div>
    </div>
  @empty
    <p class="rounded-2xl border border-dashed border-slate-200 bg-white p-8 text-center text-sm text-slate-500">No categories yet.</p>
  @endforelse
</div>

<div class="hidden overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm md:block">
  <div class="overflow-x-auto">
  <table class="w-full min-w-[640px] text-left text-sm">
    <thead class="border-b border-slate-100 bg-slate-50/80 text-xs uppercase tracking-wide text-slate-500">
      <tr>
        <th class="px-4 py-3 font-medium">Name</th>
        <th class="px-3 py-3 font-medium">Slug</th>
        <th class="px-3 py-3 font-medium">Type</th>
        <th class="px-3 py-3 font-medium">Parent</th>
        <th class="px-4 py-3 text-right font-medium">Actions</th>
      </tr>
    </thead>
    <tbody class="divide-y divide-slate-100">
      @foreach($categories as $c)
        @php
          $actions = [
            ['label' => 'Edit', 'href' => route('admin.categories.edit', $c)],
            ['label' => 'Delete', 'href' => route('admin.categories.destroy', $c), 'method' => 'DELETE', 'danger' => true, 'confirm' => 'Delete this category?'],
          ];
        @endphp
        <tr class="hover:bg-slate-50/60">
          <td class="px-4 py-3 font-medium text-slate-900 {{ $c->parent_id ? 'pl-8' : '' }}">
            @if($c->parent_id)<span class="mr-1 text-slate-400">↳</span>@endif
            {{ $c->name }}
          </td>
          <td class="px-3 py-3 text-slate-600">{{ $c->slug }}</td>
          <td class="px-3 py-3">
            @if($c->parent_id)
              <span class="admin-status" style="background:#f0f9ff;color:#0369a1">Sub-category</span>
            @else
              <span class="admin-status admin-status-ok">Parent</span>
            @endif
          </td>
          <td class="px-3 py-3 text-slate-600">{{ $c->parent?->name ?? '—' }}</td>
          <td class="px-4 py-3 text-right"><x-admin-kebab :actions="$actions" /></td>
        </tr>
      @endforeach
    </tbody>
  </table>
  </div>
</div>

<div class="mt-4">{{ $categories->links() }}</div>
@endsection
