@extends('layouts.app')
@section('title', 'My account · CodeBazaar')
@section('content')
<h1 class="text-2xl font-bold">Welcome, {{ auth()->user()->name }}</h1>
<div class="mt-6 grid gap-4 sm:grid-cols-3">
    <a href="{{ route('account.purchases') }}" class="rounded-xl border bg-white p-4 shadow-sm hover:border-emerald-300">Purchases</a>
    <a href="{{ route('account.downloads') }}" class="rounded-xl border bg-white p-4 shadow-sm hover:border-emerald-300">Downloads</a>
    <a href="{{ route('home') }}" class="rounded-xl border bg-white p-4 shadow-sm hover:border-emerald-300">Browse store</a>
</div>
@endsection
