@extends('layouts.admin')
@section('title', 'Dashboard')
@section('content')
<div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
    <div class="rounded-xl border bg-white p-5"><p class="text-xs uppercase text-slate-500">Products</p><p class="mt-1 text-3xl font-bold">{{ $products }}</p></div>
    <div class="rounded-xl border bg-white p-5"><p class="text-xs uppercase text-slate-500">Orders</p><p class="mt-1 text-3xl font-bold">{{ $orders }}</p></div>
    <div class="rounded-xl border bg-white p-5"><p class="text-xs uppercase text-slate-500">Users</p><p class="mt-1 text-3xl font-bold">{{ $users }}</p></div>
    <div class="rounded-xl border bg-white p-5"><p class="text-xs uppercase text-slate-500">Paid revenue</p><p class="mt-1 text-3xl font-bold">${{ number_format($revenue, 2) }}</p></div>
    <div class="rounded-xl border bg-white p-5"><p class="text-xs uppercase text-slate-500">Blog posts</p><p class="mt-1 text-3xl font-bold">{{ $posts }}</p></div>
    <div class="rounded-xl border bg-white p-5"><p class="text-xs uppercase text-slate-500">CMS pages</p><p class="mt-1 text-3xl font-bold">{{ $pages }}</p></div>
</div>
<div class="mt-8 rounded-xl border bg-white p-5">
    <h2 class="font-semibold">Recent orders</h2>
    <ul class="mt-3 divide-y text-sm">
        @forelse($recentOrders as $o)
            <li class="flex justify-between py-2">
                <a href="{{ route('admin.orders.show', $o) }}" class="text-emerald-700">#{{ $o->id }} · {{ $o->email }}</a>
                <span>{{ $o->status }} · ${{ number_format($o->total, 2) }}</span>
            </li>
        @empty
            <li class="py-2 text-slate-500">No orders yet.</li>
        @endforelse
    </ul>
</div>
@endsection
