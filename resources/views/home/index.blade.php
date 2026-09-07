@extends('layouts.app')
@section('title', 'CodeBazaar — Code Scripts & Plugins Marketplace')
@section('content')

<section class="relative overflow-hidden rounded border border-slate-200 bg-[#1b2838] text-white shadow-sm">
    <div class="relative z-[1] px-5 py-11 sm:px-10 sm:py-14 lg:px-14">
        <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-[#82b440]">Code marketplace</p>
        <h1 class="mt-2 max-w-2xl text-[1.65rem] font-extrabold leading-tight tracking-tight sm:text-3xl lg:text-[2.15rem]">
            {{ $hero['title'] ?? 'Discover thousands of code scripts & plugins' }}
        </h1>
        <p class="mt-3 max-w-xl text-sm leading-relaxed text-slate-300 sm:text-[15px]">
            {{ $hero['subtitle'] ?? 'PHP scripts, JavaScript, WordPress, mobile apps and more from independent authors.' }}
        </p>
        <form action="{{ route('search') }}" method="get" class="mt-6 flex max-w-xl flex-col gap-2 sm:flex-row">
            <input type="search" name="q" placeholder="e.g. admin dashboard, Laravel, WooCommerce…"
                   class="h-11 flex-1 rounded border-0 px-4 text-[15px] text-slate-900 shadow-sm outline-none ring-0 focus:ring-2 focus:ring-[#82b440]"
                   value="{{ request('q') }}">
            <button type="submit" class="h-11 shrink-0 rounded bg-[#82b440] px-6 text-sm font-bold text-white hover:bg-[#6f9a36]">
                {{ $hero['cta'] ?? 'Search' }}
            </button>
        </form>
    </div>
</section>

{!! \App\Support\AdSlots::render('homepage_after_hero') !!}

@if($categories->count())
<section class="mt-7">
    <div class="mb-3 flex items-center justify-between">
        <h2 class="text-[15px] font-bold text-slate-900 sm:text-base">Browse categories</h2>
        <a href="{{ route('search') }}" class="text-sm font-medium text-[#82b440] hover:underline">All items</a>
    </div>
    <div class="flex flex-wrap gap-2">
        @foreach($categories as $cat)
            <a href="{{ route('category', $cat->slug) }}"
               class="inline-flex items-center gap-2 rounded border border-slate-200 bg-white px-3 py-2 text-[13px] font-medium text-slate-700 shadow-sm transition hover:border-[#82b440] hover:text-[#82b440]">
                {{ $cat->name }}
                <span class="text-[11px] font-normal text-slate-400">{{ $cat->items_count }}</span>
            </a>
        @endforeach
    </div>
</section>
@endif

@if($featured->count())
<section class="mt-10">
    <div class="mb-4 flex items-end justify-between gap-3">
        <h2 class="text-[15px] font-bold text-slate-900 sm:text-base">Featured items</h2>
        <a href="{{ route('search') }}" class="text-sm font-medium text-[#82b440] hover:underline">View all</a>
    </div>
    <div class="cc-grid">
        @foreach($featured as $item)
            @include('components.item-card', ['item' => $item])
        @endforeach
    </div>
</section>
@endif

@if($popular->count())
<section class="mt-10">
    <div class="mb-4 flex items-end justify-between gap-3">
        <h2 class="text-[15px] font-bold text-slate-900 sm:text-base">Popular items</h2>
        <a href="{{ route('search') }}" class="text-sm font-medium text-[#82b440] hover:underline">View all</a>
    </div>
    <div class="cc-grid">
        @foreach($popular as $item)
            @include('components.item-card', ['item' => $item])
        @endforeach
    </div>
</section>
@endif

<section class="mt-10">
    <div class="mb-4 flex items-end justify-between gap-3">
        <h2 class="text-[15px] font-bold text-slate-900 sm:text-base">Newest items</h2>
        <a href="{{ route('search') }}" class="text-sm font-medium text-[#82b440] hover:underline">View all</a>
    </div>
    <div class="cc-grid">
        @forelse($latest as $item)
            @include('components.item-card', ['item' => $item])
        @empty
            <p class="col-span-full text-sm text-slate-500">No products yet. Add some from Admin.</p>
        @endforelse
    </div>
</section>

{!! \App\Support\AdSlots::render('homepage_before_blog') !!}

<section class="mt-12 border-t border-slate-200 pt-10">
    <div class="mb-4 flex items-end justify-between gap-3">
        <h2 class="text-[15px] font-bold text-slate-900 sm:text-base">From the blog</h2>
        <a href="{{ route('blog.index') }}" class="text-sm font-medium text-[#82b440] hover:underline">All posts</a>
    </div>
    @if($blog->count())
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($blog as $post)
                <article class="overflow-hidden rounded border border-slate-200 bg-white shadow-sm transition hover:border-[#82b440] hover:shadow-md">
                    <a href="{{ route('blog.show', $post->slug) }}" class="block">
                        @if(!empty($post->cover_url))
                            <div class="aspect-[16/9] overflow-hidden bg-slate-100">
                                <img src="{{ $post->cover_url }}" alt="" class="h-full w-full object-cover" loading="lazy">
                            </div>
                        @else
                            <div class="flex aspect-[16/9] items-center justify-center bg-gradient-to-br from-slate-100 to-slate-200 text-xs font-medium text-slate-400">Blog</div>
                        @endif
                        <div class="p-4">
                            <h3 class="line-clamp-2 text-sm font-semibold text-slate-900">{{ $post->title }}</h3>
                            @if($post->excerpt)
                                <p class="mt-1.5 line-clamp-2 text-xs leading-relaxed text-slate-500">{{ $post->excerpt }}</p>
                            @endif
                            <p class="mt-2 text-[11px] text-slate-400">
                                {{ optional($post->published_at)->format('M j, Y') ?: $post->created_at?->format('M j, Y') }}
                            </p>
                        </div>
                    </a>
                </article>
            @endforeach
        </div>
    @else
        <div class="rounded border border-dashed border-slate-300 bg-white px-6 py-10 text-center">
            <p class="text-sm text-slate-500">No blog posts yet.</p>
            <a href="{{ route('admin.blog.index') }}" class="mt-2 inline-block text-sm font-medium text-[#82b440] hover:underline">Publish a post in Admin → Blog</a>
        </div>
    @endif
</section>

@endsection
