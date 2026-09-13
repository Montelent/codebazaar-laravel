@extends('layouts.admin')
@section('title', 'Blog')
@section('content')
<div class="mb-4 flex flex-wrap items-center justify-between gap-3">
  <div>
    <h1 class="text-xl font-bold tracking-tight">Blog posts</h1>
    <p class="text-sm text-slate-500">{{ $posts->total() }} post(s)</p>
  </div>
  <a href="{{ route('admin.blog.create') }}" class="inline-flex items-center rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700">+ New post</a>
</div>

<form method="get" action="{{ route('admin.blog.index') }}" class="mb-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
  <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
    <div class="sm:col-span-2">
      <label class="text-xs font-medium text-slate-500">Search</label>
      <input type="search" name="q" value="{{ request('q') }}" placeholder="Title, slug, excerpt…" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
    </div>
    <div>
      <label class="text-xs font-medium text-slate-500">Status</label>
      <select name="status" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
        <option value="">All</option>
        <option value="draft" @selected(request('status') === 'draft')>Draft</option>
        <option value="published" @selected(request('status') === 'published')>Published</option>
      </select>
    </div>
    <div>
      <label class="text-xs font-medium text-slate-500">Category</label>
      <select name="category" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
        <option value="">All</option>
        @foreach(($categories ?? []) as $cat)
          <option value="{{ $cat }}" @selected(request('category') === $cat)>{{ $cat }}</option>
        @endforeach
      </select>
    </div>
  </div>
  <div class="mt-3 flex flex-wrap gap-2">
    <button class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white">Filter</button>
    <a href="{{ route('admin.blog.index') }}" class="rounded-lg border border-slate-200 px-4 py-2 text-sm text-slate-600 hover:bg-slate-50">Reset</a>
  </div>
</form>

<div class="space-y-3 md:hidden">
  @forelse($posts as $p)
    @php
      $statusCls = $p->status === 'published' ? 'admin-status-ok' : 'admin-status-muted';
      $actions = [
        ['label' => 'Edit', 'href' => route('admin.blog.edit', $p)],
        ['label' => 'View on site', 'href' => route('blog.show', $p->slug), 'target' => '_blank'],
        ['label' => 'Delete', 'href' => route('admin.blog.destroy', $p), 'method' => 'DELETE', 'danger' => true, 'confirm' => 'Delete this post?'],
      ];
    @endphp
    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
      <div class="flex items-start justify-between gap-3">
        <div class="min-w-0 flex-1">
          <p class="font-semibold text-slate-900 line-clamp-2">{{ $p->title }}</p>
          <div class="mt-2 flex flex-wrap items-center gap-2">
            <span class="admin-status {{ $statusCls }}">{{ $p->status }}</span>
            <span class="text-xs text-slate-500">{{ $p->category ?: '—' }}</span>
          </div>
        </div>
        <x-admin-kebab :actions="$actions" />
      </div>
    </div>
  @empty
    <p class="rounded-2xl border border-dashed border-slate-200 bg-white p-8 text-center text-sm text-slate-500">No posts match your filters.</p>
  @endforelse
</div>

<div class="hidden overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm md:block">
  <div class="overflow-x-auto">
  <table class="w-full min-w-[560px] text-sm">
    <thead class="border-b border-slate-100 bg-slate-50/80 text-xs uppercase tracking-wide text-slate-500">
      <tr>
        <th class="px-4 py-3 text-left font-medium">Title</th>
        <th class="px-3 py-3 text-left font-medium">Status</th>
        <th class="px-3 py-3 text-left font-medium">Category</th>
        <th class="px-4 py-3 text-right font-medium">Actions</th>
      </tr>
    </thead>
    <tbody class="divide-y divide-slate-100">
      @forelse($posts as $p)
        @php
          $statusCls = $p->status === 'published' ? 'admin-status-ok' : 'admin-status-muted';
          $actions = [
            ['label' => 'Edit', 'href' => route('admin.blog.edit', $p)],
            ['label' => 'View on site', 'href' => route('blog.show', $p->slug), 'target' => '_blank'],
            ['label' => 'Delete', 'href' => route('admin.blog.destroy', $p), 'method' => 'DELETE', 'danger' => true, 'confirm' => 'Delete this post?'],
          ];
        @endphp
        <tr class="hover:bg-slate-50/60">
          <td class="px-4 py-3 font-medium text-slate-900">{{ $p->title }}</td>
          <td class="px-3 py-3"><span class="admin-status {{ $statusCls }}">{{ $p->status }}</span></td>
          <td class="px-3 py-3 text-slate-600">{{ $p->category ?: '—' }}</td>
          <td class="px-4 py-3 text-right"><x-admin-kebab :actions="$actions" /></td>
        </tr>
      @empty
        <tr><td colspan="4" class="px-4 py-12 text-center text-slate-500">No posts match your filters.</td></tr>
      @endforelse
    </tbody>
  </table>
  </div>
</div>

<div class="mt-4">{{ $posts->links() }}</div>
@endsection
