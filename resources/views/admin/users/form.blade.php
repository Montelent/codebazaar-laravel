@extends('layouts.admin')
@section('title', $user->exists ? 'Edit user' : 'Add user')
@section('content')
<div class="grid gap-6 lg:grid-cols-2">
<form method="post" action="{{ $user->exists ? route('admin.users.update', $user) : route('admin.users.store') }}" class="space-y-4 rounded-xl border bg-white p-6">
@csrf @if($user->exists) @method('PUT') @endif
<div><label class="text-sm font-medium">Name</label><input name="name" value="{{ old('name', $user->name) }}" required class="mt-1 w-full rounded-lg border px-3 py-2 text-sm"></div>
<div><label class="text-sm font-medium">Email</label><input type="email" name="email" value="{{ old('email', $user->email) }}" required class="mt-1 w-full rounded-lg border px-3 py-2 text-sm"></div>
<div><label class="text-sm font-medium">Username</label><input name="username" value="{{ old('username', $user->username) }}" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm"></div>
<div><label class="text-sm font-medium">Password {{ $user->exists ? '(leave blank to keep)' : '' }}</label><input type="password" name="password" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm" {{ $user->exists ? '' : 'required' }}></div>
<div><label class="text-sm font-medium">Role</label>
<select name="role" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm">
@foreach(['buyer','author','admin'] as $r)
<option value="{{ $r }}" @selected(old('role', $user->role ?: 'buyer')===$r)>{{ $r }}</option>
@endforeach
</select></div>
<label class="flex items-center gap-2 text-sm"><input type="checkbox" name="newsletter" value="1" @checked(old('newsletter', $user->newsletter))> Newsletter</label>
@if($user->exists)
<p class="text-sm text-slate-600">Credit balance: <strong>${{ number_format((float) $user->credit_balance, 2) }}</strong>
@if($user->referral_code) · Referral: <code>{{ $user->referral_code }}</code>@endif
</p>
@endif
<button class="rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white">Save</button>
</form>

@if($user->exists)
<div class="space-y-6">
    <form method="post" action="{{ route('admin.users.funds', $user) }}" class="space-y-3 rounded-xl border bg-white p-6">
        @csrf
        <h2 class="font-semibold">Add / remove funds</h2>
        <p class="text-xs text-slate-500">Positive amount credits the wallet; negative deducts.</p>
        <div><label class="text-sm font-medium">Amount</label><input type="number" step="0.01" name="amount" required class="mt-1 w-full rounded-lg border px-3 py-2 text-sm" placeholder="e.g. 25 or -10"></div>
        <div><label class="text-sm font-medium">Note</label><input name="note" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm" placeholder="Optional reason"></div>
        <button class="rounded-lg bg-slate-800 px-4 py-2 text-sm font-semibold text-white">Update wallet</button>
    </form>

    <form method="post" action="{{ route('admin.users.email', $user) }}" class="space-y-3 rounded-xl border bg-white p-6">
        @csrf
        <h2 class="font-semibold">Send email</h2>
        <div><label class="text-sm font-medium">Subject</label><input name="subject" required class="mt-1 w-full rounded-lg border px-3 py-2 text-sm"></div>
        <div><label class="text-sm font-medium">Message</label><textarea name="body" rows="5" required class="mt-1 w-full rounded-lg border px-3 py-2 text-sm"></textarea></div>
        <button class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white">Send email</button>
    </form>

    @if(!empty($transactions) && $transactions->count())
    <div class="rounded-xl border bg-white p-6">
        <h2 class="font-semibold mb-3">Recent credit activity</h2>
        <ul class="text-sm space-y-2">
            @foreach($transactions as $t)
            <li class="flex justify-between border-b pb-1">
                <span>{{ $t->type }} — {{ $t->description }}</span>
                <span class="{{ $t->amount >= 0 ? 'text-emerald-600' : 'text-red-600' }}">{{ $t->amount >= 0 ? '+' : '' }}{{ number_format($t->amount, 2) }}</span>
            </li>
            @endforeach
        </ul>
    </div>
    @endif
</div>
@endif
</div>
@endsection
