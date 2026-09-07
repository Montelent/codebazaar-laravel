@extends('layouts.app')
@section('title', $category->name . ' · CodeBazaar')
@section('content')
@php $catTrail = $category->breadcrumbTrail(); @endphp
{!! \App\Support\AdSlots::render('category_top') !!}

<nav class="mb-4 flex flex-wrap items-center gap-x-1 gap-y-1 text-[13px] text-slate-500" aria-label="Breadcrumb">
  <a href="{{ route('home') }}" class="hover:text-[#82b440]">Home</a>
  @foreach($catTrail as $i => $crumb)
    <span class="text-slate-300">/</span>
    @if($i < count($catTrail) - 1)
      <a href="{{ route('category', $crumb->slug) }}" class="hover:text-[#82b440]">{{ $crumb->name }}</a>
    @else
      <span class="text-slate-700">{{ $crumb->name }}</span>
    @endif
  @endforeach
</nav>

<h1 class="text-2xl font-bold text-slate-900">{{ $category->name }}</h1>
@if(!empty($category->description))
  <p class="mt-2 max-w-3xl text-sm text-slate-600">{{ $category->description }}</p>
@endif

<div class="cc-grid mt-6">
  @forelse($items as $item)
    @include('components.item-card', ['item' => $item])
  @empty
    <p class="col-span-full text-sm text-slate-500">No items in this category yet.</p>
  @endforelse
</div>
<div class="mt-6">{{ $items->links() }}</div>
@endsection
