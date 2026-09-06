@extends('layouts.app')
@section('title', 'My account · CodeBazaar')
@section('content')
<h1 class="text-2xl font-bold">Welcome, {{ auth()->user()->name }}</h1>

@if(!auth()->user()->email_verified_at)
<div class="mt-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
  <p>Your email is not verified yet. Check your inbox for the link we sent.</p>
  <form method="post" action="{{ route('verification.send') }}" class="mt-2">@csrf
    <button class="font-medium text-emerald-700 underline">Resend verification email</button>
  </form>
</div>
@endif

<div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
    <a href="{{ route('account.purchases') }}" class="rounded-xl border bg-white p-4 shadow-sm hover:border-emerald-300">Purchases</a>
    <a href="{{ route('account.downloads') }}" class="rounded-xl border bg-white p-4 shadow-sm hover:border-emerald-300">Downloads</a>
    <a href="{{ route('account.wishlist') }}" class="rounded-xl border bg-white p-4 shadow-sm hover:border-emerald-300">Wishlist</a>
    <a href="{{ route('home') }}" class="rounded-xl border bg-white p-4 shadow-sm hover:border-emerald-300">Browse store</a>
</div>
@endsection
