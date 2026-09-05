@extends('layouts.app')
@section('title', 'Admin · CodeBazaar')
@section('content')
<div class="mb-6 flex flex-wrap items-center justify-between gap-3">
    <h1 class="text-2xl font-bold">Admin dashboard</h1>
    <div class="flex gap-2 text-sm">
        <a href="{{ route('admin.products.index') }}" class="rounded-lg border px-3 py-1.5">Products</a>
        <a href="{{ route('admin.products.create') }}" class="rounded-lg bg-emerald-600 px-3 py-1.5 text-white">Add product</a>
        <a href="{{ route('admin.settings.edit') }}" class="rounded-lg border px-3 py-1.5">Settings</a>
    </div>
</div>
<div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
    @foreach($stats as $label => $value)
        <div class="rounded-xl border bg-white p-4 shadow-sm">
            <div class="text-2xl font-bold">{{ is_numeric($value) && $label === 'revenue' ? '$'.number_format($value, 2) : $value }}</div>
            <div class="text-xs uppercase text-slate-500">{{ $label }}</div>
        </div>
    @endforeach
</div>
@endsection
