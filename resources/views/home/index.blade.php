@extends('layouts.app')
@php
  $seo = \App\Support\Seo::make([
    'title' => \App\Support\Seo::siteName().' — Code Scripts & Plugins Marketplace',
    'description' => \App\Support\Seo::defaultDescription(),
    'canonical' => url('/'),
    'og_type' => 'website',
  ]);
@endphp
@section('content')

<section class="relative overflow-hidden rounded border border-slate-200 text-white shadow-sm" style="background:var(--cc-secondary)">
    <div class="relative z-[1] px-5 py-11 sm:px-10 sm:py-14 lg:px-14">
        <p class="text-[11px] font-semibold uppercase tracking-[0.18em]" style="color:var(--cc-green)">Code marketplace</p>
        <h1 class="mt-2 max-w-2xl text-[1.65rem] font-extrabold leading-tight tracking-tight sm:text-3xl lg:text-[2.15rem]">
            {{ $hero['title'] ?? 'Discover thousands of code scripts & plugins' }}
        </h1>
        <p class="mt-3 max-w-xl text-sm leading-relaxed text-slate-300 sm:text-[15px]">
            {{ $hero['subtitle'] ?? 'PHP scripts, JavaScript, WordPress, mobile apps and more from independent authors.' }}
        </p>
        <form action="{{ route('search') }}" method="get" class="mt-6 flex max-w-xl flex-col gap-2 sm:flex-row">
            <input type="search" name="q" placeholder="e.g. admin dashboard, Laravel, WooCommerce…"
                   class="w-full rounded border-0 px-4 py-3 text-sm text-slate-900 shadow-sm focus:ring-2 focus:ring-[var(--cc-green)]">
            <button type="submit" class="rounded px-5 py-3 text-sm font-semibold text-white" style="background:var(--cc-green)">Search</button>
        </form>
    </div>
</section>

@if(isset($featured) && $featured->count())
<section class="mt-10">
    <div class="mb-4 flex items-end justify-between gap-3">
        <h2 class="text-lg font-bold text-slate-900">Featured items</h2>
        <a href="{{ route('search') }}" class="text-sm font-medium text-[var(--cc-green)] hover:underline">Browse all</a>
    </div>
    <div class="cc-grid">
        @foreach($featured as $item)
            @include('components.item-card', ['item' => $item])
        @endforeach
    </div>
</section>
@endif

@if(isset($latest) && $latest->count())
<section class="mt-10">
    <h2 class="mb-4 text-lg font-bold text-slate-900">Newest items</h2>
    <div class="cc-grid">
        @foreach($latest as $item)
            @include('components.item-card', ['item' => $item])
        @endforeach
    </div>
</section>
@endif

@if(isset($posts) && $posts->count())
<section class="mt-12">
    <div class="mb-4 flex items-end justify-between">
        <h2 class="text-lg font-bold text-slate-900">From the blog</h2>
        <a href="{{ route('blog.index') }}" class="text-sm font-medium text-[var(--cc-green)] hover:underline">View all</a>
    </div>
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @foreach($posts as $post)
            <a href="{{ route('blog.show', $post->slug) }}" class="rounded border border-slate-200 bg-white p-4 shadow-sm hover:border-[var(--cc-green)]">
                <p class="font-semibold text-slate-900">{{ $post->title }}</p>
                <p class="mt-1 line-clamp-2 text-sm text-slate-500">{{ $post->excerpt ?: \Illuminate\Support\Str::limit(strip_tags($post->content), 100) }}</p>
            </a>
        @endforeach
    </div>
</section>
@endif

@endsection
