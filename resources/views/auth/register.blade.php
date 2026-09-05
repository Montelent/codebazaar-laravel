@extends('layouts.app')
@section('title', 'Register · CodeBazaar')
@section('content')
<div class="mx-auto max-w-md rounded-xl border bg-white p-6 shadow-sm">
    <h1 class="text-xl font-bold">Create account</h1>
    <form method="post" action="{{ route('register') }}" class="mt-4 space-y-3">
        @csrf
        <div><label class="text-sm font-medium">Name</label>
            <input name="name" value="{{ old('name') }}" required class="mt-1 w-full rounded-lg border px-3 py-2 text-sm"></div>
        <div><label class="text-sm font-medium">Username</label>
            <input name="username" value="{{ old('username') }}" required class="mt-1 w-full rounded-lg border px-3 py-2 text-sm"></div>
        <div><label class="text-sm font-medium">Email</label>
            <input type="email" name="email" value="{{ old('email') }}" required class="mt-1 w-full rounded-lg border px-3 py-2 text-sm"></div>
        <div><label class="text-sm font-medium">Password</label>
            <input type="password" name="password" required class="mt-1 w-full rounded-lg border px-3 py-2 text-sm"></div>
        <div><label class="text-sm font-medium">Confirm password</label>
            <input type="password" name="password_confirmation" required class="mt-1 w-full rounded-lg border px-3 py-2 text-sm"></div>
        <button class="w-full rounded-lg bg-emerald-600 py-2.5 font-semibold text-white">Register</button>
    </form>
</div>
@endsection
