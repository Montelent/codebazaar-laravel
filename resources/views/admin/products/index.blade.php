@extends('layouts.admin')
@section('title', 'Products')
@section('content')
<div class="mb-4 flex flex-wrap items-center justify-between gap-3">
  <div>
    <h1 class="text-xl font-bold tracking-tight">Products</h1>
    <p class="text-sm text-slate-500">{{ $items->total() }} item(s)</p>
  </div>
  <a href="{{ route('admin.products.create') }}" class="inline-flex items-center rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700">+ Add product</a>
</div>

<form method="get" action="{{ route('admin.products.index') }}" class="mb-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
  <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-6">
    <div class="sm:col-span-2">
      <label class="text-xs font-medium text-slate-500">Search</label>
      <input type="search" name="q" value="{{ request('q') }}" placeholder="Title, slug, or ID…" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
    </div>
    <div>
      <label class="text-xs font-medium text-slate-500">Category</label>
      <select name="category_id" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
        <option value="">All</option>
        @foreach($categories as $cat)
          <option value="{{ $cat->id }}" @selected((string) request('category_id') === (string) $cat->id)>{{ $cat->name }}</option>
        @endforeach
      </select>
    </div>
    <div>
      <label class="text-xs font-medium text-slate-500">Status</label>
      <select name="status" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
        <option value="">All</option>
        @foreach(['pending','approved','rejected'] as $st)
          <option value="{{ $st }}" @selected(request('status') === $st)>{{ ucfirst($st) }}</option>
        @endforeach
      </select>
    </div>
    <div>
      <label class="text-xs font-medium text-slate-500">Price</label>
      <select name="price" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
        <option value="">All</option>
        <option value="free" @selected(request('price') === 'free')>Free</option>
        <option value="paid" @selected(request('price') === 'paid')>Paid</option>
      </select>
    </div>
    <div>
      <label class="text-xs font-medium text-slate-500">Featured</label>
      <select name="featured" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
        <option value="">Any</option>
        <option value="1" @selected(request('featured') === '1')>Yes</option>
        <option value="0" @selected(request('featured') === '0')>No</option>
      </select>
    </div>
  </div>
  <div class="mt-3 flex flex-wrap gap-2">
    <button class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white">Filter</button>
    <a href="{{ route('admin.products.index') }}" class="rounded-lg border border-slate-200 px-4 py-2 text-sm text-slate-600 hover:bg-slate-50">Reset</a>
  </div>
</form>

{{-- Mobile cards --}}
<div class="space-y-3 md:hidden">
  @forelse($items as $item)
    @php
      $statusCls = match($item->status) {
        'approved' => 'admin-status-ok',
        'pending' => 'admin-status-warn',
        'rejected' => 'admin-status-danger',
        default => 'admin-status-muted',
      };
      $actions = [
        ['label' => 'Edit', 'href' => route('admin.products.edit', $item)],
        ['label' => 'View on site', 'href' => route('item.show', [$item->slug, $item->id]), 'target' => '_blank'],
        ['label' => 'Delete', 'href' => route('admin.products.destroy', $item), 'method' => 'DELETE', 'danger' => true, 'confirm' => 'Delete this product?'],
      ];
    @endphp
    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
      <div class="flex items-start gap-3">
        <div class="h-14 w-14 shrink-0 overflow-hidden rounded-lg bg-slate-100">
          @if($item->thumbnail_url)
            <img src="{{ $item->thumbnail_url }}" alt="" class="h-full w-full object-cover">
          @endif
        </div>
        <div class="min-w-0 flex-1">
          <p class="font-semibold text-slate-900 line-clamp-2">{{ $item->title }}</p>
          <p class="mt-1 text-xs text-slate-500">{{ $item->category?->name ?? '—' }}</p>
          <div class="mt-2 flex flex-wrap items-center gap-2">
            <span class="admin-status {{ $statusCls }}">{{ $item->status }}</span>
            <span class="text-sm font-semibold text-slate-800">{{ $item->is_free || $item->effectiveRegularPrice()<=0 ? 'Free' : '$'.number_format($item->effectiveRegularPrice(),2) }}</span>
          </div>
        </div>
        <x-admin-kebab :actions="$actions" />
      </div>
    </div>
  @empty
    <p class="rounded-2xl border border-dashed border-slate-200 bg-white p-8 text-center text-sm text-slate-500">No products match your filters.</p>
  @endforelse
</div>

{{-- Desktop table --}}
<div class="hidden overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm md:block">
  <div class="overflow-x-auto">
  <table class="w-full min-w-[720px] text-left text-sm">
    <thead class="border-b border-slate-100 bg-slate-50/80 text-xs uppercase tracking-wide text-slate-500">
      <tr>
        <th class="px-4 py-3 font-medium">Product</th>
        <th class="px-3 py-3 font-medium">Price</th>
        <th class="px-3 py-3 font-medium">Category</th>
        <th class="px-3 py-3 font-medium">Status</th>
        <th class="px-3 py-3 font-medium">Updated</th>
        <th class="px-4 py-3 font-medium text-right">Actions</th>
      </tr>
    </thead>
    <tbody class="divide-y divide-slate-100">
      @forelse($items as $item)
        @php
          $statusCls = match($item->status) {
            'approved' => 'admin-status-ok',
            'pending' => 'admin-status-warn',
            'rejected' => 'admin-status-danger',
            default => 'admin-status-muted',
          };
          $actions = [
            ['label' => 'Edit', 'href' => route('admin.products.edit', $item)],
            ['label' => 'View on site', 'href' => route('item.show', [$item->slug, $item->id]), 'target' => '_blank'],
            ['label' => 'Delete', 'href' => route('admin.products.destroy', $item), 'method' => 'DELETE', 'danger' => true, 'confirm' => 'Delete this product?'],
          ];
        @endphp
        <tr class="hover:bg-slate-50/60">
          <td class="px-4 py-3">
            <div class="flex items-center gap-3">
              <div class="h-10 w-10 shrink-0 overflow-hidden rounded-lg bg-slate-100">
                @if($item->thumbnail_url)
                  <img src="{{ $item->thumbnail_url }}" alt="" class="h-full w-full object-cover">
                @endif
              </div>
              <span class="font-medium text-slate-900 line-clamp-1">{{ $item->title }}</span>
            </div>
          </td>
          <td class="px-3 py-3 whitespace-nowrap font-medium">{{ $item->is_free || $item->effectiveRegularPrice()<=0 ? 'Free' : '$'.number_format($item->effectiveRegularPrice(),2) }}</td>
          <td class="px-3 py-3 text-slate-600">{{ $item->category?->name ?? '—' }}</td>
          <td class="px-3 py-3"><span class="admin-status {{ $statusCls }}">{{ $item->status }}</span></td>
          <td class="px-3 py-3 whitespace-nowrap text-slate-500">{{ $item->updated_at?->format('Y-m-d') }}</td>
          <td class="px-4 py-3 text-right"><x-admin-kebab :actions="$actions" /></td>
        </tr>
      @empty
        <tr><td colspan="6" class="px-4 py-12 text-center text-slate-500">No products match your filters.</td></tr>
      @endforelse
    </tbody>
  </table>
  </div>
</div>

<div class="mt-4">{{ $items->links() }}</div>
@endsection
