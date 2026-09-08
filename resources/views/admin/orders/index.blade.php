@extends('layouts.admin')
@section('title', 'Orders')
@section('content')
<h1 class="mb-4 text-xl font-bold">Orders</h1>

<form method="get" action="{{ route('admin.orders.index') }}" class="mb-4 rounded-xl border bg-white p-4 shadow-sm">
  <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
    <div class="sm:col-span-2">
      <label class="text-xs font-medium text-slate-500">Search</label>
      <input type="search" name="q" value="{{ request('q') }}" placeholder="Order ID, email, provider…" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm">
    </div>
    <div>
      <label class="text-xs font-medium text-slate-500">Status</label>
      <select name="status" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm">
        <option value="">All</option>
        @foreach(['pending','paid','failed','refunded','cancelled'] as $st)
          <option value="{{ $st }}" @selected(request('status') === $st)>{{ ucfirst($st) }}</option>
        @endforeach
      </select>
    </div>
    <div>
      <label class="text-xs font-medium text-slate-500">Provider</label>
      <select name="provider" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm">
        <option value="">All</option>
        @foreach(($providers ?? []) as $prov)
          <option value="{{ $prov }}" @selected(request('provider') === $prov)>{{ $prov }}</option>
        @endforeach
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
    <a href="{{ route('admin.orders.index') }}" class="rounded-lg border px-4 py-2 text-sm text-slate-600 hover:bg-slate-50">Reset</a>
  </div>
</form>

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
    <p class="rounded-xl border border-dashed bg-white p-6 text-center text-sm text-slate-500">No orders match your filters.</p>
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
      @forelse($orders as $o)
        <tr class="border-b last:border-0">
          <td class="px-4 py-3">{{ $o->id }}</td>
          <td class="px-3 py-3">{{ $o->email ?: $o->user?->email }}</td>
          <td class="px-3 py-3">{{ $o->status }}</td>
          <td class="px-3 py-3 whitespace-nowrap">${{ number_format($o->total, 2) }}</td>
          <td class="px-3 py-3 whitespace-nowrap">{{ $o->created_at?->format('Y-m-d H:i') }}</td>
          <td class="px-4 py-3 text-right"><a href="{{ route('admin.orders.show', $o) }}" class="text-emerald-700">View</a></td>
        </tr>
      @empty
        <tr><td colspan="6" class="px-4 py-8 text-center text-slate-500">No orders match your filters.</td></tr>
      @endforelse
    </tbody>
  </table>
</div>

<div class="mt-4">{{ $orders->links() }}</div>
@endsection
