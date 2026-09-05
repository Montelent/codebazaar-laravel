@extends('layouts.app')
@section('title', 'Purchases · CodeBazaar')
@section('content')
<h1 class="text-2xl font-bold">Purchases</h1>
<ul class="mt-6 space-y-4">
    @forelse($orders as $order)
        <li class="rounded-xl border bg-white p-4">
            <div class="text-sm text-slate-500">Order #{{ $order->id }} · {{ $order->created_at }} · ${{ number_format($order->total, 2) }}</div>
            <ul class="mt-2 text-sm">@foreach($order->items as $oi)<li>{{ $oi->title }} ({{ $oi->license_type }})</li>@endforeach</ul>
        </li>
    @empty
        <p class="text-slate-500">No purchases yet.</p>
    @endforelse
</ul>
@endsection
