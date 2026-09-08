@extends('layouts.app')
@php
  $hasAttr = !empty($attrKey) && !empty($attrVal);
  $hasTag = !empty($tag);
  $view = $view ?? request('view', 'list');
  $sort = $sort ?? request('sort', 'newest');
  if ($q) { $seoTitle = 'Search: '.$q; }
  elseif ($hasAttr) { $seoTitle = $attrKey.': '.$attrVal; }
  elseif ($hasTag) { $seoTitle = 'Tag: '.$tag; }
  else { $seoTitle = 'Browse items'; }
  $seo = \App\Support\Seo::make([
    'title' => $seoTitle,
    'description' => 'Search and filter digital code items on '.\App\Support\Seo::siteName().'.',
    'canonical' => url('/search'),
    'robots' => 'index, follow',
  ]);
@endphp
@section('content')

{!! \App\Support\AdSlots::render('search_top') !!}

<nav class="mb-3 text-[13px] text-slate-500">
  <a href="{{ route('home') }}" class="hover:text-[var(--cc-green)]">Home</a>
  <span class="mx-1.5 text-slate-300">/</span>
  <span class="text-slate-700">Search</span>
</nav>

<div class="flex flex-wrap items-end justify-between gap-3">
  <h1 class="text-xl font-bold text-slate-900 sm:text-2xl">{{ $seoTitle }}</h1>
  <p class="text-[13px] text-slate-500">{{ number_format($items->total()) }} {{ \Illuminate\Support\Str::plural('item', $items->total()) }}</p>
</div>

@if($hasAttr || $hasTag || $q)
  <div class="mt-3 flex flex-wrap items-center gap-2 text-sm">
    @if($hasAttr)
      <span class="inline-flex items-center gap-1 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-emerald-800">
        <span class="font-medium">{{ $attrKey }}:</span> {{ $attrVal }}
        <a href="{{ route('search', array_filter(['q' => $q ?: null, 'tag' => $tag ?: null])) }}" class="ml-1" title="Clear">×</a>
      </span>
    @endif
    @if($hasTag)
      <span class="inline-flex items-center gap-1 rounded-full border border-sky-200 bg-sky-50 px-3 py-1 text-sky-800">
        Tag: {{ $tag }}
        <a href="{{ route('search', array_filter(['q' => $q ?: null, 'attr' => $attrKey ?: null, 'val' => $attrVal ?: null])) }}" class="ml-1" title="Clear">×</a>
      </span>
    @endif
    <a href="{{ route('search') }}" class="text-slate-500 hover:text-slate-800">Clear all</a>
  </div>
@endif

<details class="mt-4 rounded border border-slate-200 bg-white lg:hidden">
  <summary class="cursor-pointer px-4 py-3 text-[13px] font-semibold text-slate-800">Filter &amp; Refine</summary>
  <div class="border-t border-slate-100 p-3">
    @include('components.catalog-sidebar', [
      'sidebarCategories' => $sidebarCategories ?? collect(),
      'sort' => $sort,
      'priceMin' => $priceMin ?? null,
      'priceMax' => $priceMax ?? null,
      'view' => $view,
      'action' => route('search'),
    ])
  </div>
</details>

<div class="mt-5 grid gap-6 lg:grid-cols-12">
  <div class="hidden lg:col-span-3 lg:block">
    @include('components.catalog-sidebar', [
      'sidebarCategories' => $sidebarCategories ?? collect(),
      'sort' => $sort,
      'priceMin' => $priceMin ?? null,
      'priceMax' => $priceMax ?? null,
      'view' => $view,
      'action' => route('search'),
    ])
  </div>

  <div class="lg:col-span-9">
    <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
      <div class="flex items-center gap-2 text-[13px]">
        <span class="text-slate-500">View:</span>
        <a href="{{ request()->fullUrlWithQuery(['view' => 'list']) }}" class="rounded px-2 py-1 {{ $view === 'list' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600' }}">List</a>
        <a href="{{ request()->fullUrlWithQuery(['view' => 'grid']) }}" class="rounded px-2 py-1 {{ $view === 'grid' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600' }}">Grid</a>
      </div>
      <form method="get" class="flex items-center gap-2 text-[13px]">
        @foreach(request()->except(['sort','page']) as $k => $v)
          @if(is_scalar($v))<input type="hidden" name="{{ $k }}" value="{{ $v }}">@endif
        @endforeach
        <label class="text-slate-500">Sort</label>
        <select name="sort" onchange="this.form.submit()" class="rounded border border-slate-200 px-2 py-1.5">
          <option value="newest" @selected($sort==='newest')>Newest</option>
          <option value="popular" @selected($sort==='popular')>Best sellers</option>
          <option value="rating" @selected($sort==='rating')>Best rated</option>
          <option value="price_asc" @selected($sort==='price_asc')>Price ↑</option>
          <option value="price_desc" @selected($sort==='price_desc')>Price ↓</option>
        </select>
      </form>
    </div>

    @if($view === 'grid')
      <div class="cc-grid">
        @forelse($items as $item)
          @include('components.item-card', ['item' => $item])
        @empty
          <p class="col-span-full text-sm text-slate-500">No items match this filter.</p>
        @endforelse
      </div>
    @else
      <div class="overflow-hidden rounded border border-slate-200 bg-white">
        @forelse($items as $item)
          @include('components.item-list-row', ['item' => $item])
        @empty
          <p class="p-8 text-center text-sm text-slate-500">No items match this filter.</p>
        @endforelse
      </div>
    @endif

    <div class="mt-6">{{ $items->withQueryString()->links() }}</div>
  </div>
</div>
@endsection
