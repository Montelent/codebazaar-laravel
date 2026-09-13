@extends('layouts.admin')
@section('title', 'Pages')
@section('content')
<div class="mb-4 flex flex-wrap items-center justify-between gap-3">
  <div>
    <h1 class="text-xl font-bold tracking-tight">CMS pages</h1>
    <p class="text-sm text-slate-500">{{ $pages->total() }} page(s)</p>
  </div>
  <a href="{{ route('admin.pages.create') }}" class="inline-flex items-center rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700">+ Add page</a>
</div>

<div class="space-y-3 md:hidden">
  @forelse($pages as $p)
    @php
      $statusCls = $p->status === 'published' ? 'admin-status-ok' : 'admin-status-muted';
      $actions = [
        ['label' => 'Edit', 'href' => route('admin.pages.edit', $p)],
        ['label' => 'View on site', 'href' => route('page.show', $p->slug), 'target' => '_blank'],
        ['label' => 'Delete', 'href' => route('admin.pages.destroy', $p), 'method' => 'DELETE', 'danger' => true, 'confirm' => 'Delete this page?'],
      ];
    @endphp
    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
      <div class="flex items-start justify-between gap-3">
        <div class="min-w-0 flex-1">
          <p class="font-semibold text-slate-900">{{ $p->title }}</p>
          <div class="mt-2 flex flex-wrap items-center gap-2">
            <span class="admin-status {{ $statusCls }}">{{ $p->status }}</span>
            <span class="text-xs text-slate-400">/{{ $p->slug }}</span>
          </div>
        </div>
        <x-admin-kebab :actions="$actions" />
      </div>
    </div>
  @empty
    <p class="rounded-2xl border border-dashed border-slate-200 bg-white p-8 text-center text-sm text-slate-500">No pages yet.</p>
  @endforelse
</div>

<div class="hidden overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm md:block">
  <div class="overflow-x-auto">
  <table class="w-full min-w-[520px] text-sm">
    <thead class="border-b border-slate-100 bg-slate-50/80 text-xs uppercase tracking-wide text-slate-500">
      <tr>
        <th class="px-4 py-3 text-left font-medium">Title</th>
        <th class="px-3 py-3 text-left font-medium">Slug</th>
        <th class="px-3 py-3 text-left font-medium">Status</th>
        <th class="px-4 py-3 text-right font-medium">Actions</th>
      </tr>
    </thead>
    <tbody class="divide-y divide-slate-100">
      @forelse($pages as $p)
        @php
          $statusCls = $p->status === 'published' ? 'admin-status-ok' : 'admin-status-muted';
          $actions = [
            ['label' => 'Edit', 'href' => route('admin.pages.edit', $p)],
            ['label' => 'View on site', 'href' => route('page.show', $p->slug), 'target' => '_blank'],
            ['label' => 'Delete', 'href' => route('admin.pages.destroy', $p), 'method' => 'DELETE', 'danger' => true, 'confirm' => 'Delete this page?'],
          ];
        @endphp
        <tr class="hover:bg-slate-50/60">
          <td class="px-4 py-3 font-medium text-slate-900">{{ $p->title }}</td>
          <td class="px-3 py-3 text-slate-600">{{ $p->slug }}</td>
          <td class="px-3 py-3"><span class="admin-status {{ $statusCls }}">{{ $p->status }}</span></td>
          <td class="px-4 py-3 text-right"><x-admin-kebab :actions="$actions" /></td>
        </tr>
      @empty
        <tr><td colspan="4" class="px-4 py-12 text-center text-slate-500">No pages yet.</td></tr>
      @endforelse
    </tbody>
  </table>
  </div>
</div>

<div class="mt-4">{{ $pages->links() }}</div>
@endsection
