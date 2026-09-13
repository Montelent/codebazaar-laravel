@extends('layouts.app')
@section('title', 'Credits · CodeBazaar')
@section('content')
<h1 class="text-2xl font-bold">Wallet & credits</h1>
<p class="mt-1 text-slate-600">Balance: <strong class="text-emerald-700 text-xl">${{ number_format((float)$user->credit_balance, 2) }}</strong></p>

<div class="mt-6 grid gap-6 lg:grid-cols-2">
    <div class="rounded-xl border bg-white p-6">
        <h2 class="font-semibold">Top up credits</h2>
        <form method="post" action="{{ route('credits.topup') }}" class="mt-4 space-y-3">
            @csrf
            <div class="space-y-2">
                @foreach($packages as $pkg)
                    <label class="flex items-center gap-3 rounded-lg border px-3 py-2 text-sm cursor-pointer">
                        <input type="radio" name="package_id" value="{{ $pkg->id ?? 0 }}" @checked($loop->first)>
                        <span>{{ $pkg->name }} — <strong>{{ number_format($pkg->credits, 0) }}</strong> credits for ${{ number_format($pkg->price, 2) }}</span>
                    </label>
                @endforeach
            </div>
            <div>
                <label class="text-sm font-medium">Or custom amount (USD)</label>
                <input type="number" step="0.01" min="1" name="amount" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm" placeholder="Optional">
            </div>
            <div>
                <label class="text-sm font-medium">Payment method</label>
                <select name="payment_method" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm">
                    <option value="BANK_TRANSFER">Bank transfer</option>
                    <option value="PAYSTACK">Paystack</option>
                    <option value="MONNIFY">Monnify</option>
                    <option value="STRIPE">Stripe</option>
                    <option value="CRYPTO_USDT">USDT</option>
                    <option value="MANUAL">Manual / offline</option>
                </select>
            </div>
            <button class="rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white">Continue top-up</button>
        </form>
    </div>

    <div class="space-y-6">
        <div class="rounded-xl border bg-white p-6">
            <h2 class="font-semibold">Referral program</h2>
            <p class="mt-2 text-sm text-slate-600">Share your code. When someone registers with it, you earn <strong>${{ number_format($referralBonus, 2) }}</strong> credits.</p>
            <p class="mt-3 font-mono text-lg">{{ $user->referral_code }}</p>
            <p class="mt-1 text-xs text-slate-500">Register link: {{ url('/register?ref='.$user->referral_code) }}</p>
        </div>

        <div class="rounded-xl border bg-white p-6">
            <h2 class="font-semibold mb-3">Recent activity</h2>
            <ul class="text-sm space-y-2">
                @forelse($transactions as $t)
                <li class="flex justify-between border-b pb-1">
                    <span>{{ $t->type }} — {{ $t->description }}</span>
                    <span class="{{ $t->amount >= 0 ? 'text-emerald-600' : 'text-red-600' }}">{{ $t->amount >= 0 ? '+' : '' }}{{ number_format($t->amount, 2) }}</span>
                </li>
                @empty
                <li class="text-slate-500">No transactions yet.</li>
                @endforelse
            </ul>
        </div>
    </div>
</div>
@endsection
