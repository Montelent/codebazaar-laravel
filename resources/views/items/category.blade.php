@extends('layouts.app')
@php $seo = \App\Support\Seo::make($category->seoPayload()); @endphp
@section('content')
@php
  $catTrail = $category->breadcrumbTrail();
  $view = $view ?? request('view', 'list');
  $sort = $sort ?? request('sort', 'newest');
@endphp
{!! \App\Support\AdSlots::render('category_top') !!}

<nav class="mb-3 flex flex-wrap items-center gap-x-1 gap-y-1 text-[13px] text-slate-500" aria-label="Breadcrumb">
  <a href="{{ route('home') }}" class="hover:text-[var(--cc-green)]">Home</a>
  @foreach($catTrail as $i => $crumb)
    <span class="text-slate-300">/</span>
    @if($i < count($catTrail) - 1)
      <a href="{{ route('category', $crumb->slug) }}" class="hover:text-[var(--cc-green)]">{{ $crumb->name }}</a>
    @else
      <span class="text-slate-700">{{ $crumb->name }}</span>
    @endif
  @endforeach
</nav>

<div class="flex flex-wrap items-end justify-between gap-3">
  <div>
    <h1 class="text-xl font-bold text-slate-900 sm:text-2xl">{{ $category->name }}</h1>
    @if(!empty($category->description))
      <p class="mt-1 max-w-2xl text-sm text-slate-600">{{ $category->description }}</p>
    @endif
  </div>
  <p class="text-[13px] text-slate-500">{{ number_format($items->total()) }} {{ \Illuminate\Support\Str::plural('item', $items->total()) }}</p>
</div>

<details class="mt-4 rounded border border-slate-200 bg-white lg:hidden">
  <summary class="cursor-pointer px-4 py-3 text-[13px] font-semibold text-slate-800">Filter &amp; Refine</summary>
  <div class="border-t border-slate-100 p-3">
    @include('components.catalog-sidebar', [
      'sidebarCategories' => $sidebarCategories,
      'category' => $category,
      'sub' => $sub ?? '',
      'sort' => $sort,
      'priceMin' => $priceMin ?? null,
      'priceMax' => $priceMax ?? null,
      'view' => $view,
      'action' => route('category', $category->slug),
    ])
  </div>
</details>

<div class="mt-5 grid gap-6 lg:grid-cols-12">
  <div class="hidden lg:col-span-3 lg:block">
    @include('components.catalog-sidebar', [
      'sidebarCategories' => $sidebarCategories,
      'category' => $category,
      'sub' => $sub ?? '',
      'sort' => $sort,
      'priceMin' => $priceMin ?? null,
      'priceMax' => $priceMax ?? null,
      'view' => $view,
      'action' => route('category', $category->slug),
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
          <p class="col-span-full text-sm text-slate-500">No items in this category yet.</p>
        @endforelse
      </div>
    @else
      <div class="overflow-hidden rounded border border-slate-200 bg-white">
        @forelse($items as $item)
          @include('components.item-list-row', ['item' => $item])
        @empty
          <p class="p-8 text-center text-sm text-slate-500">No items in this category yet.</p>
        @endforelse
      </div>
    @endif

    <div class="mt-6">{{ $items->withQueryString()->links() }}</div>
  </div>
</div>
@endsection
