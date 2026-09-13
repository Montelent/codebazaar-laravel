@extends('layouts.admin')
@section('title', 'Orders')
@section('content')
<div class="mb-4 flex flex-wrap items-center justify-between gap-3">
  <div>
    <h1 class="text-xl font-bold tracking-tight">Orders</h1>
    <p class="text-sm text-slate-500">{{ $orders->total() }} order(s)</p>
  </div>
</div>

<form method="get" action="{{ route('admin.orders.index') }}" class="mb-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
  <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
    <div class="sm:col-span-2">
      <label class="text-xs font-medium text-slate-500">Search</label>
      <input type="search" name="q" value="{{ request('q') }}" placeholder="Order ID, email, provider…" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
    </div>
    <div>
      <label class="text-xs font-medium text-slate-500">Status</label>
      <select name="status" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
        <option value="">All</option>
        @foreach(['pending','paid','failed','refunded','cancelled'] as $st)
          <option value="{{ $st }}" @selected(request('status') === $st)>{{ ucfirst($st) }}</option>
        @endforeach
      </select>
    </div>
    <div>
      <label class="text-xs font-medium text-slate-500">Provider</label>
      <select name="provider" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
        <option value="">All</option>
        @foreach(($providers ?? []) as $prov)
          <option value="{{ $prov }}" @selected(request('provider') === $prov)>{{ $prov }}</option>
        @endforeach
      </select>
    </div>
  </div>
  <div class="mt-3 flex flex-wrap gap-2">
    <button class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white">Filter</button>
    <a href="{{ route('admin.orders.index') }}" class="rounded-lg border border-slate-200 px-4 py-2 text-sm text-slate-600 hover:bg-slate-50">Reset</a>
  </div>
</form>

<div class="space-y-3 md:hidden">
  @forelse($orders as $o)
    @php
      $statusCls = match($o->status) {
        'paid' => 'admin-status-ok',
        'pending' => 'admin-status-warn',
        'failed', 'cancelled' => 'admin-status-danger',
        default => 'admin-status-muted',
      };
      $actions = [
        ['label' => 'View details', 'href' => route('admin.orders.show', $o)],
      ];
    @endphp
    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
      <div class="flex items-start justify-between gap-3">
        <div class="min-w-0 flex-1">
          <p class="font-semibold text-slate-900">Order #{{ $o->id }}</p>
          <p class="mt-1 truncate text-sm text-slate-600">{{ $o->email ?: $o->user?->email }}</p>
          <div class="mt-2 flex flex-wrap items-center gap-2">
            <span class="admin-status {{ $statusCls }}">{{ $o->status }}</span>
            <span class="text-sm font-semibold">${{ number_format($o->total, 2) }}</span>
            <span class="text-xs text-slate-400">{{ $o->created_at?->format('Y-m-d H:i') }}</span>
          </div>
        </div>
        <x-admin-kebab :actions="$actions" />
      </div>
    </div>
  @empty
    <p class="rounded-2xl border border-dashed border-slate-200 bg-white p-8 text-center text-sm text-slate-500">No orders match your filters.</p>
  @endforelse
</div>

<div class="hidden overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm md:block">
  <div class="overflow-x-auto">
  <table class="w-full min-w-[640px] text-sm">
    <thead class="border-b border-slate-100 bg-slate-50/80 text-xs uppercase tracking-wide text-slate-500">
      <tr>
        <th class="px-4 py-3 text-left font-medium">#</th>
        <th class="px-3 py-3 text-left font-medium">Email</th>
        <th class="px-3 py-3 text-left font-medium">Status</th>
        <th class="px-3 py-3 text-left font-medium">Total</th>
        <th class="px-3 py-3 text-left font-medium">Date</th>
        <th class="px-4 py-3 text-right font-medium">Actions</th>
      </tr>
    </thead>
    <tbody class="divide-y divide-slate-100">
      @forelse($orders as $o)
        @php
          $statusCls = match($o->status) {
            'paid' => 'admin-status-ok',
            'pending' => 'admin-status-warn',
            'failed', 'cancelled' => 'admin-status-danger',
            default => 'admin-status-muted',
          };
          $actions = [
            ['label' => 'View details', 'href' => route('admin.orders.show', $o)],
          ];
        @endphp
        <tr class="hover:bg-slate-50/60">
          <td class="px-4 py-3 font-medium">#{{ $o->id }}</td>
          <td class="px-3 py-3 text-slate-600">{{ $o->email ?: $o->user?->email }}</td>
          <td class="px-3 py-3"><span class="admin-status {{ $statusCls }}">{{ $o->status }}</span></td>
          <td class="px-3 py-3 whitespace-nowrap font-medium">${{ number_format($o->total, 2) }}</td>
          <td class="px-3 py-3 whitespace-nowrap text-slate-500">{{ $o->created_at?->format('Y-m-d H:i') }}</td>
          <td class="px-4 py-3 text-right"><x-admin-kebab :actions="$actions" /></td>
        </tr>
      @empty
        <tr><td colspan="6" class="px-4 py-12 text-center text-slate-500">No orders match your filters.</td></tr>
      @endforelse
    </tbody>
  </table>
  </div>
</div>

<div class="mt-4">{{ $orders->links() }}</div>
@endsection
