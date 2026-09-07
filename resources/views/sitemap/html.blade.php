@extends('layouts.app')
@section('title', 'Sitemap · CodeBazaar')
@php
  $seo = \App\Support\Seo::make([
    'title' => 'Sitemap',
    'description' => 'Browse all public pages, products, categories and blog posts on '.\App\Support\Seo::siteName().'.',
    'canonical' => url('/sitemap'),
  ]);
@endphp
@section('content')
<nav class="mb-3 text-[13px] text-slate-500">
  <a href="{{ route('home') }}" class="hover:text-[var(--cc-green)]">Home</a>
  <span class="mx-1.5 text-slate-300">/</span>
  <span class="text-slate-700">Sitemap</span>
</nav>

<div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
  <div class="flex flex-wrap items-end justify-between gap-3">
    <div>
      <h1 class="text-2xl font-bold text-slate-900">Sitemap</h1>
      <p class="mt-1 text-sm text-slate-500">A readable map of every public page. XML version for search engines: <a class="text-[var(--cc-green)] underline" href="{{ url('/sitemap.xml') }}">/sitemap.xml</a></p>
    </div>
  </div>

  <div class="mt-8 grid gap-8 md:grid-cols-2">
    @foreach($groups as $heading => $links)
      <section>
        <h2 class="border-b border-slate-100 pb-2 text-sm font-bold uppercase tracking-wide text-slate-500">{{ $heading }} <span class="font-normal text-slate-400">({{ count($links) }})</span></h2>
        <ul class="mt-3 max-h-80 space-y-1.5 overflow-y-auto text-sm">
          @forelse($links as $link)
            <li>
              <a href="{{ $link['url'] }}" class="text-slate-700 hover:text-[var(--cc-green)] hover:underline">{{ $link['title'] }}</a>
            </li>
          @empty
            <li class="text-slate-400">Nothing here yet.</li>
          @endforelse
        </ul>
      </section>
    @endforeach
  </div>
</div>
@endsection
