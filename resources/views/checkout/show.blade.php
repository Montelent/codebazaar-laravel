@extends('layouts.app')
@section('title', 'Checkout · CodeBazaar')
@section('content')
<h1 class="text-2xl font-bold">Checkout</h1>
<p class="mt-1 text-sm text-slate-500">Total due: <strong>${{ number_format($total, 2) }}</strong>
@auth · Wallet: <strong>${{ number_format($creditBalance ?? 0, 2) }}</strong> credits @endauth
</p>

<form method="post" action="{{ route('checkout.place') }}" class="mt-6 max-w-lg space-y-4 rounded-xl border bg-white p-6">
    @csrf
    <div>
        <label class="block text-sm font-medium">Email for receipts & downloads</label>
        <input type="email" name="email" required value="{{ old('email', auth()->user()->email ?? '') }}" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm">
    </div>

    @if($total > 0)
    <div>
        <label class="block text-sm font-medium mb-2">Payment method</label>
        <div class="space-y-2">
            @forelse($methods as $m)
                @php $p = strtoupper($m['provider'] ?? ''); @endphp
                <label class="flex items-start gap-3 rounded-lg border px-3 py-2 text-sm cursor-pointer hover:border-emerald-400">
                    <input type="radio" name="payment_method" value="{{ $p }}" required
                        @checked(old('payment_method', $p === 'CREDITS' ? 'CREDITS' : ($loop->first ? $p : '')) === $p)
                        class="mt-1">
                    <span>
                        <span class="font-medium">{{ $m['name'] ?? $p }}</span>
                        @if($p === 'CREDITS')
                            <span class="block text-xs text-slate-500">Balance: ${{ number_format($creditBalance ?? 0, 2) }}</span>
                        @elseif(!empty($m['instructions']))
                            <span class="block text-xs text-slate-500">{{ $m['instructions'] }}</span>
                        @endif
                    </span>
                </label>
            @empty
                <p class="text-sm text-amber-700">No payment methods enabled. Enable them in Admin → Settings → Payments.</p>
            @endforelse
        </div>
    </div>
    @else
        <input type="hidden" name="payment_method" value="FREE">
    @endif

    <button class="w-full rounded-lg bg-emerald-600 py-3 font-semibold text-white">
        {{ $total <= 0 ? 'Complete free order' : 'Place order' }}
    </button>
    @auth
        <p class="text-center text-xs text-slate-500"><a href="{{ route('credits.index') }}" class="text-emerald-700 underline">Top up credits</a></p>
    @endauth
</form>
@endsection
