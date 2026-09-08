@extends('layouts.admin')
@section('title', 'Blog')
@section('content')
<div class="mb-4 flex flex-wrap items-center justify-between gap-3">
  <h1 class="text-xl font-bold">Blog posts</h1>
  <a href="{{ route('admin.blog.create') }}" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white">New post</a>
</div>

<form method="get" action="{{ route('admin.blog.index') }}" class="mb-4 rounded-xl border bg-white p-4 shadow-sm">
  <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
    <div class="sm:col-span-2">
      <label class="text-xs font-medium text-slate-500">Search</label>
      <input type="search" name="q" value="{{ request('q') }}" placeholder="Title, slug, excerpt…" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm">
    </div>
    <div>
      <label class="text-xs font-medium text-slate-500">Status</label>
      <select name="status" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm">
        <option value="">All</option>
        <option value="draft" @selected(request('status') === 'draft')>Draft</option>
        <option value="published" @selected(request('status') === 'published')>Published</option>
      </select>
    </div>
    <div>
      <label class="text-xs font-medium text-slate-500">Category</label>
      <select name="category" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm">
        <option value="">All</option>
        @foreach(($categories ?? []) as $cat)
          <option value="{{ $cat }}" @selected(request('category') === $cat)>{{ $cat }}</option>
        @endforeach
      </select>
    </div>
    <div>
      <label class="text-xs font-medium text-slate-500">From</label>
      <input type="date" name="from" value="{{ request('from') }}" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm">
    </div>
    <div>
      <label class="text-xs font-medium text-slate-500">To</label>
      <input type="date" name="to" value="{{ request('to') }}" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm">
    </div>
  </div>
  <div class="mt-3 flex flex-wrap gap-2">
    <button class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white">Filter</button>
    <a href="{{ route('admin.blog.index') }}" class="rounded-lg border px-4 py-2 text-sm text-slate-600 hover:bg-slate-50">Reset</a>
  </div>
</form>

<div class="space-y-3 md:hidden">
  @forelse($posts as $p)
    <div class="rounded-xl border bg-white p-4 shadow-sm">
      <div class="flex items-start justify-between gap-3">
        <div class="min-w-0">
          <p class="font-semibold text-slate-900 line-clamp-2">{{ $p->title }}</p>
          <p class="mt-1 text-xs text-slate-500">{{ $p->status }} · {{ $p->category ?: '—' }}</p>
        </div>
        <a href="{{ route('admin.blog.edit', $p) }}" class="shrink-0 text-sm font-medium text-emerald-700">Edit</a>
      </div>
    </div>
  @empty
    <p class="rounded-xl border border-dashed bg-white p-6 text-center text-sm text-slate-500">No posts match your filters.</p>
  @endforelse
</div>

<div class="hidden overflow-x-auto rounded-xl border bg-white md:block">
  <table class="w-full min-w-[520px] text-sm">
    <thead class="border-b bg-slate-50">
      <tr>
        <th class="px-4 py-3 text-left">Title</th>
        <th class="px-3 py-3 text-left">Status</th>
        <th class="px-3 py-3 text-left">Category</th>
        <th class="px-4 py-3"></th>
      </tr>
    </thead>
    <tbody>
      @forelse($posts as $p)
        <tr class="border-b last:border-0">
          <td class="px-4 py-3">{{ $p->title }}</td>
          <td class="px-3 py-3">{{ $p->status }}</td>
          <td class="px-3 py-3">{{ $p->category }}</td>
          <td class="px-4 py-3 text-right"><a href="{{ route('admin.blog.edit', $p) }}" class="text-emerald-700">Edit</a></td>
        </tr>
      @empty
        <tr><td colspan="4" class="px-4 py-8 text-center text-slate-500">No posts match your filters.</td></tr>
      @endforelse
    </tbody>
  </table>
</div>

<div class="mt-4">{{ $posts->links() }}</div>
@endsection
