@extends('layouts.admin')
@section('title', 'Orders')
@section('content')
<h1 class="mb-4 text-xl font-bold">Orders</h1>

<div class="space-y-3 md:hidden">
  @forelse($orders as $o)
    <div class="rounded-xl border bg-white p-4 shadow-sm">
      <div class="flex items-start justify-between gap-3">
        <div class="min-w-0">
          <p class="font-semibold text-slate-900">Order #{{ $o->id }}</p>
          <p class="mt-1 truncate text-sm text-slate-600">{{ $o->email ?: $o->user?->email }}</p>
          <p class="mt-1 text-sm"><span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs">{{ $o->status }}</span>
            <span class="ml-2 font-medium">${{ number_format($o->total, 2) }}</span></p>
          <p class="mt-1 text-[11px] text-slate-400">{{ $o->created_at?->format('Y-m-d H:i') }}</p>
        </div>
        <a href="{{ route('admin.orders.show', $o) }}" class="shrink-0 text-sm font-medium text-emerald-700">View</a>
      </div>
    </div>
  @empty
    <p class="rounded-xl border border-dashed bg-white p-6 text-center text-sm text-slate-500">No orders yet.</p>
  @endforelse
</div>

<div class="hidden overflow-x-auto rounded-xl border bg-white md:block">
  <table class="w-full min-w-[640px] text-sm">
    <thead class="border-b bg-slate-50">
      <tr>
        <th class="px-4 py-3 text-left">#</th>
        <th class="px-3 py-3 text-left">Email</th>
        <th class="px-3 py-3 text-left">Status</th>
        <th class="px-3 py-3 text-left">Total</th>
        <th class="px-3 py-3 text-left">Date</th>
        <th class="px-4 py-3"></th>
      </tr>
    </thead>
    <tbody>
      @foreach($orders as $o)
        <tr class="border-b last:border-0">
          <td class="px-4 py-3">{{ $o->id }}</td>
          <td class="px-3 py-3">{{ $o->email ?: $o->user?->email }}</td>
          <td class="px-3 py-3">{{ $o->status }}</td>
          <td class="px-3 py-3 whitespace-nowrap">${{ number_format($o->total, 2) }}</td>
          <td class="px-3 py-3 whitespace-nowrap">{{ $o->created_at?->format('Y-m-d H:i') }}</td>
          <td class="px-4 py-3 text-right"><a href="{{ route('admin.orders.show', $o) }}" class="text-emerald-700">View</a></td>
        </tr>
      @endforeach
    </tbody>
  </table>
</div>

<div class="mt-4">{{ $orders->links() }}</div>
@endsection
