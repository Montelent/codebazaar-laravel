@extends('layouts.admin')
@section('title', 'Products')
@section('content')
<div class="mb-4 flex flex-wrap items-center justify-between gap-3">
  <h1 class="text-xl font-bold">Products</h1>
  <a href="{{ route('admin.products.create') }}" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white">Add product</a>
</div>

<form method="get" action="{{ route('admin.products.index') }}" class="mb-4 rounded-xl border bg-white p-4 shadow-sm">
  <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-6">
    <div class="sm:col-span-2">
      <label class="text-xs font-medium text-slate-500">Search</label>
      <input type="search" name="q" value="{{ request('q') }}" placeholder="Title, slug, or ID…" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm">
    </div>
    <div>
      <label class="text-xs font-medium text-slate-500">Category</label>
      <select name="category_id" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm">
        <option value="">All</option>
        @foreach($categories as $cat)
          <option value="{{ $cat->id }}" @selected((string) request('category_id') === (string) $cat->id)>{{ $cat->name }}</option>
        @endforeach
      </select>
    </div>
    <div>
      <label class="text-xs font-medium text-slate-500">Status</label>
      <select name="status" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm">
        <option value="">All</option>
        @foreach(['pending','approved','rejected'] as $st)
          <option value="{{ $st }}" @selected(request('status') === $st)>{{ ucfirst($st) }}</option>
        @endforeach
      </select>
    </div>
    <div>
      <label class="text-xs font-medium text-slate-500">Price</label>
      <select name="price" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm">
        <option value="">All</option>
        <option value="free" @selected(request('price') === 'free')>Free</option>
        <option value="paid" @selected(request('price') === 'paid')>Paid</option>
      </select>
    </div>
    <div>
      <label class="text-xs font-medium text-slate-500">Featured</label>
      <select name="featured" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm">
        <option value="">Any</option>
        <option value="1" @selected(request('featured') === '1')>Yes</option>
        <option value="0" @selected(request('featured') === '0')>No</option>
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
    <a href="{{ route('admin.products.index') }}" class="rounded-lg border px-4 py-2 text-sm text-slate-600 hover:bg-slate-50">Reset</a>
  </div>
</form>

{{-- Mobile cards --}}
<div class="space-y-3 md:hidden">
  @forelse($items as $item)
    <div class="rounded-xl border bg-white p-4 shadow-sm">
      <div class="flex items-start justify-between gap-3">
        <div class="min-w-0">
          <p class="font-semibold text-slate-900 line-clamp-2">{{ $item->title }}</p>
          <p class="mt-1 text-xs text-slate-500">{{ $item->category?->name ?? '—' }} · {{ $item->status }}</p>
          <p class="mt-1 text-sm font-medium text-slate-800">
            {{ $item->is_free || $item->effectiveRegularPrice()<=0 ? 'Free' : '$'.number_format($item->effectiveRegularPrice(),2) }}
          </p>
          <p class="mt-0.5 text-[11px] text-slate-400">Updated {{ $item->updated_at?->format('Y-m-d') }}</p>
        </div>
        <div class="flex shrink-0 flex-col items-end gap-2 text-sm">
          <a href="{{ route('admin.products.edit', $item) }}" class="font-medium text-emerald-700">Edit</a>
          <a href="{{ route('item.show', [$item->slug, $item->id]) }}" class="text-slate-500" target="_blank">View</a>
        </div>
      </div>
    </div>
  @empty
    <p class="rounded-xl border border-dashed bg-white p-6 text-center text-sm text-slate-500">No products match your filters.</p>
  @endforelse
</div>

{{-- Desktop table --}}
<div class="hidden overflow-x-auto rounded-xl border bg-white md:block">
  <table class="w-full min-w-[640px] text-left text-sm">
    <thead class="border-b bg-slate-50 text-slate-500">
      <tr>
        <th class="px-4 py-3">Title</th>
        <th class="px-3 py-3">Price</th>
        <th class="px-3 py-3">Category</th>
        <th class="px-3 py-3">Status</th>
        <th class="px-3 py-3">Updated</th>
        <th class="px-4 py-3"></th>
      </tr>
    </thead>
    <tbody>
      @forelse($items as $item)
        <tr class="border-b last:border-0">
          <td class="max-w-xs px-4 py-3 font-medium">{{ $item->title }}</td>
          <td class="px-3 py-3 whitespace-nowrap">{{ $item->is_free || $item->effectiveRegularPrice()<=0 ? 'Free' : '$'.number_format($item->effectiveRegularPrice(),2) }}</td>
          <td class="px-3 py-3">{{ $item->category?->name ?? '—' }}</td>
          <td class="px-3 py-3">{{ $item->status }}</td>
          <td class="px-3 py-3 whitespace-nowrap">{{ $item->updated_at?->format('Y-m-d') }}</td>
          <td class="px-4 py-3 text-right whitespace-nowrap">
            <a href="{{ route('item.show', [$item->slug, $item->id]) }}" class="text-slate-500" target="_blank">View</a>
            <a href="{{ route('admin.products.edit', $item) }}" class="ml-2 text-emerald-700">Edit</a>
          </td>
        </tr>
      @empty
        <tr><td colspan="6" class="px-4 py-8 text-center text-slate-500">No products match your filters.</td></tr>
      @endforelse
    </tbody>
  </table>
</div>

<div class="mt-4">{{ $items->links() }}</div>
@endsection
