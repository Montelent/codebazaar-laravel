@extends('layouts.app')
@section('title', $post->seo_title ?: $post->title)
@section('meta_description', $post->seo_description)
@section('content')
<article class="mx-auto max-w-3xl">
    <h1 class="text-3xl font-bold">{{ $post->title }}</h1>
    <div class="prose prose-slate mt-8 max-w-none">{!! $post->content !!}</div>
</article>
@endsection
