@extends('layouts.admin')
@section('title', 'Products')
@section('content')
<div class="mb-4 flex flex-wrap items-center justify-between gap-3">
  <h1 class="text-xl font-bold">Products</h1>
  <a href="{{ route('admin.products.create') }}" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white">Add product</a>
</div>

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
    <p class="rounded-xl border border-dashed bg-white p-6 text-center text-sm text-slate-500">No products yet.</p>
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
      @foreach($items as $item)
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
      @endforeach
    </tbody>
  </table>
</div>

<div class="mt-4">{{ $items->links() }}</div>
@endsection
