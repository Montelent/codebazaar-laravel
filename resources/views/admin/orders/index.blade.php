@extends('layouts.admin')
@section('title', 'Orders')
@section('content')
<h1 class="mb-4 text-xl font-bold">Orders</h1>
<div class="overflow-hidden rounded-xl border bg-white">
<table class="w-full text-sm"><thead class="border-b bg-slate-50"><tr><th class="px-4 py-3">#</th><th>Email</th><th>Status</th><th>Total</th><th>Date</th><th></th></tr></thead>
<tbody>
@foreach($orders as $o)
<tr class="border-b">
<td class="px-4 py-3">{{ $o->id }}</td>
<td>{{ $o->email ?: $o->user?->email }}</td>
<td>{{ $o->status }}</td>
<td>${{ number_format($o->total, 2) }}</td>
<td>{{ $o->created_at?->format('Y-m-d H:i') }}</td>
<td class="px-4"><a href="{{ route('admin.orders.show', $o) }}" class="text-emerald-700">View</a></td>
</tr>
@endforeach
</tbody></table></div>
{{ $orders->links() }}
@endsection
