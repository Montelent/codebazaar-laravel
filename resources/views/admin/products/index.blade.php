@extends('layouts.admin')
@section('title', 'Products')
@section('content')
<div class="mb-4 flex flex-wrap items-center justify-between gap-3">
  <h1 class="text-xl font-bold">Products</h1>
  <a href="{{ route('admin.products.create') }}" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white">Add product</a>
</div>
<div class="overflow-x-auto rounded-xl border bg-white">
<table class="w-full text-left text-sm">
<thead class="border-b bg-slate-50 text-slate-500">
<tr><th class="px-4 py-3">Title</th><th>Price</th><th>Category</th><th>Status</th><th>Updated</th><th></th></tr>
</thead>
<tbody>
@foreach($items as $item)
<tr class="border-b">
  <td class="px-4 py-3 font-medium">{{ $item->title }}</td>
  <td>{{ $item->is_free || $item->effectiveRegularPrice()<=0 ? 'Free' : '$'.number_format($item->effectiveRegularPrice(),2) }}</td>
  <td>{{ $item->category?->name ?? '—' }}</td>
  <td>{{ $item->status }}</td>
  <td>{{ $item->updated_at?->format('Y-m-d') }}</td>
  <td class="px-4 py-3 text-right whitespace-nowrap">
    <a href="{{ route('item.show', [$item->slug, $item->id]) }}" class="text-slate-500" target="_blank">View</a>
    <a href="{{ route('admin.products.edit', $item) }}" class="ml-2 text-emerald-700">Edit</a>
  </td>
</tr>
@endforeach
</tbody>
</table>
</div>
{{ $items->links() }}
@endsection
