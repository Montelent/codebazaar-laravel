@extends('layouts.app')
@section('title', 'CodeBazaar — Digital Marketplace')
@section('content')
<section class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
    <div class="bg-gradient-to-br from-slate-900 via-slate-800 to-emerald-900 px-5 py-12 text-white sm:px-8 sm:py-16 lg:px-12">
        <h1 class="text-2xl font-extrabold tracking-tight sm:text-3xl lg:text-4xl">{{ $hero['title'] ?? 'CodeBazaar' }}</h1>
        <p class="mt-3 max-w-2xl text-sm text-slate-200 sm:text-base">{{ $hero['subtitle'] ?? '' }}</p>
        <form action="{{ route('search') }}" method="get" class="mt-6 flex max-w-xl flex-col gap-2 sm:flex-row">
            <input type="search" name="q" placeholder="Search code, themes, plugins…" class="cc-search flex-1 !h-11">
            <button class="cc-btn-primary !py-2.5 sm:shrink-0">{{ $hero['cta'] ?? 'Search' }}</button>
        </form>
    </div>
</section>

@if($categories->count())
<section class="mt-8 sm:mt-10">
    <h2 class="text-lg font-bold sm:text-xl">Browse by category</h2>
    <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5">
        @foreach($categories as $cat)
            <a href="{{ route('category', $cat->slug) }}" class="rounded border border-slate-200 bg-white p-3 shadow-sm transition hover:border-[var(--cc-green)] hover:shadow sm:p-4">
                <div class="text-sm font-semibold sm:text-base">{{ $cat->name }}</div>
                <div class="text-[11px] text-slate-500 sm:text-xs">{{ $cat->items_count }} items</div>
            </a>
        @endforeach
    </div>
</section>
@endif

<section class="mt-8 sm:mt-10">
    <div class="flex items-end justify-between gap-3">
        <h2 class="text-lg font-bold sm:text-xl">Latest items</h2>
        <a href="{{ route('search') }}" class="text-sm font-medium text-[var(--cc-green)] hover:underline">View all</a>
    </div>
    <div class="cc-grid mt-4">
        @forelse($latest as $item)
            @include('components.item-card', ['item' => $item])
        @empty
            <p class="text-sm text-slate-500">No products yet. Add some from Admin.</p>
        @endforelse
    </div>
</section>
@endsection
