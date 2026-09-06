@extends('layouts.admin')
@section('title', 'Order #'.$order->id)
@section('content')
<a href="{{ route('admin.orders.index') }}" class="text-sm text-emerald-700">← Orders</a>
<div class="mt-4 rounded-xl border bg-white p-6">
<p><strong>Status:</strong> {{ $order->status }}</p>
<p><strong>Email:</strong> {{ $order->email ?: $order->user?->email }}</p>
<p><strong>Total:</strong> ${{ number_format($order->total, 2) }} {{ $order->currency }}</p>
<p><strong>Provider:</strong> {{ $order->payment_provider ?: '—' }}</p>
<table class="mt-6 w-full text-sm"><thead><tr class="border-b text-left"><th class="py-2">Item</th><th>License</th><th>Price</th></tr></thead>
<tbody>
@foreach($order->items as $line)
<tr class="border-b"><td class="py-2">{{ $line->title }}</td><td>{{ $line->license_type }}</td><td>${{ number_format($line->price, 2) }}</td></tr>
@endforeach
</tbody></table>
</div>
@endsection
