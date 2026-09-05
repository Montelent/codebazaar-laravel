@extends('layouts.app')
@section('title', 'Checkout · CodeBazaar')
@section('content')
<h1 class="text-2xl font-bold">Checkout</h1>
<p class="mt-1 text-sm text-slate-500">Total due: <strong>${{ number_format($total, 2) }}</strong></p>
<form method="post" action="{{ route('checkout.place') }}" class="mt-6 max-w-md space-y-4 rounded-xl border bg-white p-6">
    @csrf
    <div>
        <label class="block text-sm font-medium">Email for receipts & downloads</label>
        <input type="email" name="email" required value="{{ old('email', auth()->user()->email ?? '') }}" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm">
    </div>
    <button class="w-full rounded-lg bg-emerald-600 py-3 font-semibold text-white">{{ $total <= 0 ? 'Complete free order' : 'Pay with Stripe' }}</button>
</form>
@endsection
