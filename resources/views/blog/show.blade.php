@extends('layouts.app')
@php $seo = \App\Support\Seo::make($post->seoPayload()); @endphp
@section('content')
@php
  $html = (string) ($post->content ?? '');
  if ($html !== '' && str_contains($html, '&lt;') && ! str_contains($html, '<p') && ! str_contains($html, '<div')) {
      $html = html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8');
  }
  $html = \App\Support\AdSlots::injectAfterParagraphs($html, 'blog_post_middle');
@endphp
<article class="mx-auto max-w-3xl">
    {!! \App\Support\AdSlots::render('blog_post_before') !!}
    <nav class="mb-4 text-sm text-slate-500">
        <a href="{{ route('home') }}" class="hover:text-[#82b440]">Home</a>
        <span class="mx-1.5">/</span>
        <a href="{{ route('blog.index') }}" class="hover:text-[#82b440]">Blog</a>
        <span class="mx-1.5">/</span>
        <span class="text-slate-700">{{ $post->title }}</span>
    </nav>
    <h1 class="text-3xl font-bold text-slate-900">{{ $post->title }}</h1>
    @if($post->published_at)
      <p class="mt-2 text-sm text-slate-500">{{ $post->published_at->format('M j, Y') }}</p>
    @endif
    @if($post->cover_url)
      <img src="{{ $post->cover_url }}" alt="{{ $post->title }}" class="mt-6 w-full rounded-xl border object-cover">
    @endif
    <div class="item-body prose prose-slate mt-8 max-w-none">{!! $html !!}</div>
    {!! \App\Support\AdSlots::render('blog_post_after') !!}
</article>
@endsection
