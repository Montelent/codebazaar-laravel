@extends('layouts.admin')
@section('title', 'Pages')
@section('content')
<div class="mb-4 flex flex-wrap items-center justify-between gap-3">
  <h1 class="text-xl font-bold">CMS pages</h1>
  <a href="{{ route('admin.pages.create') }}" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white">Add page</a>
</div>

<div class="space-y-3 md:hidden">
  @forelse($pages as $p)
    <div class="rounded-xl border bg-white p-4 shadow-sm">
      <div class="flex items-start justify-between gap-3">
        <div class="min-w-0">
          <p class="font-semibold text-slate-900">{{ $p->title }}</p>
          <p class="mt-1 text-xs text-slate-500">/{{ $p->slug }} · {{ $p->status }}</p>
        </div>
        <a href="{{ route('admin.pages.edit', $p) }}" class="shrink-0 text-sm font-medium text-emerald-700">Edit</a>
      </div>
    </div>
  @empty
    <p class="rounded-xl border border-dashed bg-white p-6 text-center text-sm text-slate-500">No pages yet.</p>
  @endforelse
</div>

<div class="hidden overflow-x-auto rounded-xl border bg-white md:block">
  <table class="w-full min-w-[520px] text-sm">
    <thead class="border-b bg-slate-50">
      <tr>
        <th class="px-4 py-3 text-left">Title</th>
        <th class="px-3 py-3 text-left">Slug</th>
        <th class="px-3 py-3 text-left">Status</th>
        <th class="px-4 py-3"></th>
      </tr>
    </thead>
    <tbody>
      @foreach($pages as $p)
        <tr class="border-b last:border-0">
          <td class="px-4 py-3">{{ $p->title }}</td>
          <td class="px-3 py-3">{{ $p->slug }}</td>
          <td class="px-3 py-3">{{ $p->status }}</td>
          <td class="px-4 py-3 text-right"><a href="{{ route('admin.pages.edit', $p) }}" class="text-emerald-700">Edit</a></td>
        </tr>
      @endforeach
    </tbody>
  </table>
</div>

<div class="mt-4">{{ $pages->links() }}</div>
@endsection
