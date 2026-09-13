@extends('layouts.app')
@section('title', 'Awaiting payment · CodeBazaar')
@section('content')
<div class="mx-auto max-w-lg rounded-xl border bg-white p-6">
    <h1 class="text-xl font-bold">Order #{{ $order->id }} — awaiting payment</h1>
    <p class="mt-2 text-sm text-slate-600">Total: <strong>${{ number_format($order->total, 2) }}</strong> via {{ strtoupper($order->payment_provider) }}</p>

    @if($cfg)
        <div class="mt-4 rounded-lg bg-slate-50 p-4 text-sm space-y-1">
            <p class="font-semibold">{{ $cfg['name'] ?? 'Payment' }} instructions</p>
            <p>{{ $cfg['instructions'] ?? '' }}</p>
            @foreach(($cfg['config'] ?? []) as $k => $v)
                @if($v !== '' && $v !== null)
                    <p><span class="text-slate-500">{{ $k }}:</span> <code class="font-mono">{{ $v }}</code></p>
                @endif
            @endforeach
        </div>
    @endif

    <p class="mt-4 text-sm text-slate-500">After paying, keep your order ID handy. Downloads unlock when an admin marks the order paid, or when an automatic gateway confirms payment.</p>
    <a href="{{ route('account.purchases') }}" class="mt-6 inline-block rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white">My purchases</a>
</div>
@endsection
