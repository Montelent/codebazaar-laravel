@extends('layouts.app')
@section('title', $page->seo_title ?: $page->title)
@section('meta_description', $page->seo_description)
@section('content')
<article class="mx-auto max-w-3xl">
    <h1 class="text-3xl font-bold">{{ $page->title }}</h1>
    <div class="prose prose-slate mt-8 max-w-none">{!! $page->content !!}</div>
</article>
@endsection
