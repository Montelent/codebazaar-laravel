@extends('layouts.app')
@section('title', 'CodeBazaar — Code Scripts & Plugins Marketplace')
@section('content')

{{-- Hero / search (CodeCanyon-style) --}}
<section class="relative overflow-hidden rounded border border-slate-200 bg-[#1e2a3a] text-white shadow-sm">
    <div class="px-5 py-12 sm:px-10 sm:py-16 lg:px-14">
        <p class="text-xs font-semibold uppercase tracking-[0.15em] text-[#82b440]">Digital marketplace</p>
        <h1 class="mt-2 max-w-2xl text-2xl font-extrabold tracking-tight sm:text-3xl lg:text-4xl">
            {{ $hero['title'] ?? 'Discover high-quality code, scripts & plugins' }}
        </h1>
        <p class="mt-3 max-w-xl text-sm text-slate-300 sm:text-base">{{ $hero['subtitle'] ?? 'Themes, PHP scripts, JavaScript, mobile apps and more from independent authors.' }}</p>
        <form action="{{ route('search') }}" method="get" class="mt-6 flex max-w-xl flex-col gap-2 sm:flex-row">
            <input type="search" name="q" placeholder="e.g. admin dashboard, WooCommerce, Laravel…" class="cc-search flex-1 !h-11 !rounded !border-0 !text-slate-900">
            <button type="submit" class="cc-btn-primary !rounded !py-2.5 sm:shrink-0">{{ $hero['cta'] ?? 'Search' }}</button>
        </form>
    </div>
</section>

{{-- Categories --}}
@if($categories->count())
<section class="mt-8">
    <div class="mb-3 flex items-center justify-between">
        <h2 class="text-base font-bold text-slate-900 sm:text-lg">Browse categories</h2>
    </div>
    <div class="flex flex-wrap gap-2">
        @foreach($categories as $cat)
            <a href="{{ route('category', $cat->slug) }}"
               class="inline-flex items-center gap-2 rounded border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-700 shadow-sm transition hover:border-[var(--cc-green)] hover:text-[var(--cc-green)]">
                {{ $cat->name }}
                <span class="text-xs font-normal text-slate-400">{{ $cat->items_count }}</span>
            </a>
        @endforeach
    </div>
</section>
@endif

{{-- Featured --}}
@if(isset($featured) && $featured->count())
<section class="mt-10">
    <div class="mb-4 flex items-end justify-between gap-3">
        <h2 class="text-base font-bold text-slate-900 sm:text-lg">Featured items</h2>
        <a href="{{ route('search') }}" class="text-sm font-medium text-[var(--cc-green)] hover:underline">View all</a>
    </div>
    <div class="cc-grid">
        @foreach($featured as $item)
            @include('components.item-card', ['item' => $item])
        @endforeach
    </div>
</section>
@endif

{{-- Latest --}}
<section class="mt-10">
    <div class="mb-4 flex items-end justify-between gap-3">
        <h2 class="text-base font-bold text-slate-900 sm:text-lg">Newest items</h2>
        <a href="{{ route('search') }}" class="text-sm font-medium text-[var(--cc-green)] hover:underline">View all</a>
    </div>
    <div class="cc-grid">
        @forelse($latest as $item)
            @include('components.item-card', ['item' => $item])
        @empty
            <p class="col-span-full text-sm text-slate-500">No products yet. Add some from Admin.</p>
        @endforelse
    </div>
</section>

{{-- Blog grid --}}
@if(isset($blog) && $blog->count())
<section class="mt-12">
    <div class="mb-4 flex items-end justify-between gap-3">
        <h2 class="text-base font-bold text-slate-900 sm:text-lg">From the blog</h2>
        <a href="{{ route('blog.index') }}" class="text-sm font-medium text-[var(--cc-green)] hover:underline">All posts</a>
    </div>
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @foreach($blog as $post)
            <article class="overflow-hidden rounded border border-slate-200 bg-white shadow-sm transition hover:border-[var(--cc-green)] hover:shadow-md">
                <a href="{{ route('blog.show', $post->slug) }}" class="block">
                    @if($post->cover_url)
                        <div class="aspect-[16/9] overflow-hidden bg-slate-100">
                            <img src="{{ $post->cover_url }}" alt="" class="h-full w-full object-cover">
                        </div>
                    @else
                        <div class="flex aspect-[16/9] items-center justify-center bg-slate-100 text-xs text-slate-400">Blog</div>
                    @endif
                    <div class="p-4">
                        <h3 class="line-clamp-2 text-sm font-semibold text-slate-900 hover:text-[var(--cc-green)]">{{ $post->title }}</h3>
                        @if($post->excerpt)
                            <p class="mt-1 line-clamp-2 text-xs text-slate-500">{{ $post->excerpt }}</p>
                        @endif
                        <p class="mt-2 text-[11px] text-slate-400">
                            {{ optional($post->published_at)->format('M j, Y') ?: $post->created_at?->format('M j, Y') }}
                        </p>
                    </div>
                </a>
            </article>
        @endforeach
    </div>
</section>
@endif

@endsection
