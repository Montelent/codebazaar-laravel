@extends('layouts.app')
@php $seo = \App\Support\Seo::make(['title' => 'Cart', 'noindex' => true, 'canonical' => url('/cart')]); @endphp
@section('content')
<h1 class="text-2xl font-bold">Your cart</h1>
@php $cart = session('cart', []); @endphp
@if(empty($cart))
  <p class="mt-4 text-slate-500">Your cart is empty. <a href="{{ route('search') }}" class="text-[var(--cc-green)] underline">Browse items</a></p>
@else
  <div class="mt-6 space-y-3">
    @foreach($cart as $key => $row)
      <div class="flex flex-wrap items-center justify-between gap-3 rounded border bg-white p-4">
        <div>
          <p class="font-semibold">{{ $row['title'] ?? 'Item' }}</p>
          <p class="text-sm text-slate-500">{{ $row['license'] ?? 'regular' }} · ${{ number_format($row['price'] ?? 0, 2) }}</p>
        </div>
        <form method="post" action="{{ route('cart.remove', $key) }}">@csrf @method('DELETE')
          <button class="text-sm text-red-600">Remove</button>
        </form>
      </div>
    @endforeach
  </div>
  <div class="mt-6">
    <a href="{{ route('checkout.show') }}" class="inline-block rounded bg-[var(--cc-green)] px-5 py-2.5 text-sm font-semibold text-white">Checkout</a>
  </div>
@endif
@endsection
