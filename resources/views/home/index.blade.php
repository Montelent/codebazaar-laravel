@extends('layouts.app')
@section('title', 'CodeBazaar — Digital Marketplace')
@section('content')
<section class="rounded-2xl bg-gradient-to-br from-emerald-600 to-teal-700 px-6 py-14 text-white shadow-lg">
    <h1 class="text-3xl font-bold sm:text-4xl">{{ $hero['title'] ?? 'CodeBazaar' }}</h1>
    <p class="mt-3 max-w-2xl text-emerald-50">{{ $hero['subtitle'] ?? '' }}</p>
    <form action="{{ route('search') }}" method="get" class="mt-6 flex max-w-lg gap-2">
        <input type="search" name="q" placeholder="Search code, themes, plugins…" class="flex-1 rounded-lg border-0 px-4 py-2.5 text-slate-900">
        <button class="rounded-lg bg-slate-900 px-5 py-2.5 font-semibold text-white">Search</button>
    </form>
</section>
@if($categories->count())
<section class="mt-10">
    <h2 class="text-xl font-bold">Browse by category</h2>
    <div class="mt-4 grid gap-3 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5">
        @foreach($categories as $cat)
            <a href="{{ route('category', $cat->slug) }}" class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm hover:border-emerald-300">
                <div class="font-semibold">{{ $cat->name }}</div>
                <div class="text-xs text-slate-500">{{ $cat->items_count }} items</div>
            </a>
        @endforeach
    </div>
</section>
@endif
<section class="mt-10">
    <h2 class="text-xl font-bold">Latest items</h2>
    <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @forelse($latest as $item)
            @include('components.item-card', ['item' => $item])
        @empty
            <p class="text-sm text-slate-500">No products yet. Add some from Admin.</p>
        @endforelse
    </div>
</section>
@endsection
