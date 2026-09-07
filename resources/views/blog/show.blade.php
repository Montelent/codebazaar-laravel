@extends('layouts.app')
@section('title', $post->seo_title ?: $post->title)
@section('meta_description', $post->seo_description)
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
        <span class="mx-1.5 text-slate-300">/</span>
        <a href="{{ route('blog.index') }}" class="hover:text-[#82b440]">Blog</a>
        <span class="mx-1.5 text-slate-300">/</span>
        <span class="text-slate-700">{{ \Illuminate\Support\Str::limit($post->title, 40) }}</span>
    </nav>
    <h1 class="text-3xl font-bold tracking-tight text-slate-900">{{ $post->title }}</h1>
    <p class="mt-2 text-sm text-slate-500">
        {{ optional($post->published_at)->format('F j, Y') ?: $post->created_at?->format('F j, Y') }}
    </p>
    {!! \App\Support\AdSlots::render('blog_post_after_title') !!}
    @if($post->cover_url)
        <img src="{{ $post->cover_url }}" alt="" class="mt-6 w-full rounded border border-slate-200 object-cover">
    @endif
    <div class="item-body mt-8">{!! $html !!}</div>
    {!! \App\Support\AdSlots::render('blog_post_end') !!}
</article>
{!! \App\Support\AdSlots::render('blog_post_after') !!}
@endsection
