@extends('layouts.app')
@section('title', 'Cart · CodeBazaar')
@section('content')
<h1 class="text-2xl font-bold">Shopping cart</h1>
@if(empty($cart))
    <p class="mt-6 text-slate-500">Your cart is empty. <a href="{{ route('home') }}" class="text-emerald-600">Continue shopping</a></p>
@else
    <ul class="mt-6 divide-y rounded-xl border bg-white">
        @foreach($cart as $key => $row)
            <li class="flex items-center gap-4 p-4">
                <div class="min-w-0 flex-1">
                    <div class="font-medium">{{ $row['title'] }}</div>
                    <div class="text-xs text-slate-500">{{ ucfirst($row['license_type']) }} · Qty {{ $row['qty'] }}</div>
                </div>
                <div class="font-semibold">${{ number_format($row['price'] * $row['qty'], 2) }}</div>
                <form method="post" action="{{ route('cart.remove', $key) }}">@csrf @method('DELETE')
                    <button class="text-sm text-red-600">Remove</button>
                </form>
            </li>
        @endforeach
    </ul>
    <div class="mt-4 flex items-center justify-between">
        <div class="text-lg font-bold">Total: ${{ number_format($total, 2) }}</div>
        <a href="{{ route('checkout.show') }}" class="rounded-lg bg-emerald-600 px-5 py-2.5 font-semibold text-white">Checkout</a>
    </div>
@endif
@endsection
