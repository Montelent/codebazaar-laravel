@extends('layouts.app')
@section('title', 'Products · Admin')
@section('content')
<div class="mb-4 flex items-center justify-between">
    <h1 class="text-2xl font-bold">Products</h1>
    <a href="{{ route('admin.products.create') }}" class="rounded-lg bg-emerald-600 px-3 py-2 text-sm font-medium text-white">Add product</a>
</div>
<table class="w-full overflow-hidden rounded-xl border bg-white text-left text-sm">
    <thead class="bg-slate-50 text-xs uppercase text-slate-500">
        <tr><th class="px-3 py-2">Title</th><th class="px-3 py-2">Price</th><th class="px-3 py-2">Status</th><th class="px-3 py-2"></th></tr>
    </thead>
    <tbody>
        @foreach($items as $item)
            <tr class="border-t">
                <td class="px-3 py-2 font-medium">{{ $item->title }}</td>
                <td class="px-3 py-2">{{ $item->is_free || $item->regular_price <= 0 ? 'Free' : '$'.number_format($item->regular_price, 2) }}</td>
                <td class="px-3 py-2">{{ $item->status }}</td>
                <td class="px-3 py-2 text-right">
                    <a href="{{ route('admin.products.edit', $item) }}" class="text-emerald-700 hover:underline">Edit</a>
                    <a href="{{ route('item.show', [$item->slug, $item->id]) }}" class="ml-2 text-slate-500" target="_blank">View</a>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
<div class="mt-4">{{ $items->links() }}</div>
@endsection
