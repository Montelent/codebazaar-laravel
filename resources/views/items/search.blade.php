@extends('layouts.app')
@section('title', 'Search · CodeBazaar')
@section('content')
@php
  $hasAttr = !empty($attrKey) && !empty($attrVal);
  $hasTag = !empty($tag);
@endphp

{!! \App\Support\AdSlots::render('search_top') !!}

<nav class="mb-3 text-sm text-slate-500">
  <a href="{{ route('home') }}" class="hover:text-[var(--cc-green)]">Home</a>
  <span class="mx-1.5 text-slate-300">/</span>
  <span class="text-slate-700">Search</span>
</nav>

<h1 class="text-2xl font-bold text-slate-900">
  @if($q)
    Results for “{{ $q }}”
  @elseif($hasAttr)
    {{ $attrKey }}: {{ $attrVal }}
  @elseif($hasTag)
    Tag: {{ $tag }}
  @else
    Browse items
  @endif
</h1>

@if($hasAttr || $hasTag || $q)
  <div class="mt-3 flex flex-wrap items-center gap-2 text-sm">
    @if($hasAttr)
      <span class="inline-flex items-center gap-1 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-emerald-800">
        <span class="font-medium">{{ $attrKey }}:</span> {{ $attrVal }}
        <a href="{{ route('search', array_filter(['q' => $q ?: null, 'tag' => $tag ?: null])) }}" class="ml-1 text-emerald-600 hover:text-emerald-900" title="Clear">×</a>
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

<p class="mt-2 text-sm text-slate-500">{{ $items->total() }} {{ \Illuminate\Support\Str::plural('item', $items->total()) }}</p>

<div class="cc-grid mt-6">
  @forelse($items as $item)
    @include('components.item-card', ['item' => $item])
  @empty
    <p class="col-span-full text-slate-500">No items match this filter.</p>
  @endforelse
</div>
<div class="mt-8">{{ $items->withQueryString()->links() }}</div>
@endsection
