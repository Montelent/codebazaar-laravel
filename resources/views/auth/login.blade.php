@extends('layouts.app')
@section('title', 'Sign in · CodeBazaar')
@section('content')
<div class="mx-auto max-w-md rounded-xl border bg-white p-6 shadow-sm">
    <h1 class="text-xl font-bold">Sign in</h1>
    <form method="post" action="{{ route('login') }}" class="mt-4 space-y-3">
        @csrf
        <div>
            <label class="text-sm font-medium">Email</label>
            <input type="email" name="email" value="{{ old('email') }}" required class="mt-1 w-full rounded-lg border px-3 py-2 text-sm">
            @error('email') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="text-sm font-medium">Password</label>
            <input type="password" name="password" required class="mt-1 w-full rounded-lg border px-3 py-2 text-sm">
        </div>
        <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="remember"> Remember me</label>
        <button class="w-full rounded-lg bg-emerald-600 py-2.5 font-semibold text-white">Sign in</button>
    </form>
    <p class="mt-4 text-center text-sm text-slate-500">No account? <a href="{{ route('register') }}" class="text-emerald-600">Register</a></p>
</div>
@endsection
