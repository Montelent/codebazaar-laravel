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

{{-- Full-bleed hero (breaks out of .cc-container) --}}
</div>
<section class="cc-hero-v2">
  <div class="cc-container cc-hero-v2-inner">
    <p class="cc-hero-eyebrow">{{ $hero['eyebrow'] ?? 'CodeBazaar' }}</p>
    <h1 class="cc-hero-title">
      <span class="cc-hero-title-main">{{ $hero['title'] ?? 'Code that powers' }}</span>
      @if(!empty($hero['title_highlight']))
        <span class="cc-hero-title-accent">{{ $hero['title_highlight'] }}</span>
      @endif
    </h1>
    <p class="cc-hero-sub">
      {{ $hero['subtitle'] ?? 'Discover premium scripts, themes, plugins, and templates from world-class independent creator.' }}
    </p>
    <form action="{{ route('search') }}" method="get" class="cc-hero-search" role="search">
      <div class="cc-hero-search-field">
        <svg class="cc-hero-search-icon" width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M11 18a7 7 0 100-14 7 7 0 000 14z"/>
        </svg>
        <input type="search" name="q" placeholder="Search scripts, themes, plugins…" autocomplete="off">
      </div>
      <button type="submit" class="cc-hero-search-btn">{{ $hero['cta'] ?? 'Search' }}</button>
    </form>
  </div>
</section>
<div class="cc-container">

@if(!empty($browseCategories))
<section class="cc-browse mt-12">
  <h2 class="cc-section-title">Browse by category</h2>
  <div class="cc-browse-grid">
    @foreach($browseCategories as $card)
      <a href="{{ $card['url'] ?? '#' }}" class="cc-browse-card">
        <span class="cc-browse-icon" aria-hidden="true">
          @if(!empty($card['icon']) && str_starts_with($card['icon'], 'http'))
            <img src="{{ $card['icon'] }}" alt="" width="36" height="36">
          @else
            {{ $card['icon'] ?? '📦' }}
          @endif
        </span>
        <span class="cc-browse-name">{{ $card['title'] ?? '' }}</span>
        @if(!empty($card['subtitle']))
          <span class="cc-browse-desc">{{ $card['subtitle'] }}</span>
        @endif
      </a>
    @endforeach
  </div>
</section>
@endif

@if(isset($featured) && $featured->count())
<section class="mt-12">
  <div class="mb-5 flex items-end justify-between gap-3">
    <h2 class="cc-section-title mb-0">Featured items</h2>
    <a href="{{ route('search') }}" class="text-sm font-semibold text-[var(--cc-green)] hover:underline">Browse all</a>
  </div>
  <div class="cc-grid">
    @foreach($featured as $item)
      @include('components.item-card', ['item' => $item])
    @endforeach
  </div>
</section>
@endif

@if(isset($latest) && $latest->count())
<section class="mt-12">
  <h2 class="cc-section-title">Newest items</h2>
  <div class="cc-grid">
    @foreach($latest as $item)
      @include('components.item-card', ['item' => $item])
    @endforeach
  </div>
</section>
@endif

@if(isset($popular) && $popular->count())
<section class="mt-12">
  <h2 class="cc-section-title">Popular items</h2>
  <div class="cc-grid">
    @foreach($popular as $item)
      @include('components.item-card', ['item' => $item])
    @endforeach
  </div>
</section>
@endif

@if(isset($blog) && $blog->count())
<section class="mt-12">
  <div class="mb-5 flex items-end justify-between">
    <h2 class="cc-section-title mb-0">From the blog</h2>
    <a href="{{ route('blog.index') }}" class="text-sm font-semibold text-[var(--cc-green)] hover:underline">View all</a>
  </div>
  <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
    @foreach($blog as $post)
      <a href="{{ route('blog.show', $post->slug) }}" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-[var(--cc-green)] hover:shadow-md">
        <p class="font-semibold text-slate-900">{{ $post->title }}</p>
        <p class="mt-2 line-clamp-2 text-sm text-slate-500">{{ $post->excerpt ?: \Illuminate\Support\Str::limit(strip_tags($post->content), 100) }}</p>
      </a>
    @endforeach
  </div>
</section>
@endif

@endsection
