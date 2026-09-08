@extends('layouts.app')
@php $seo = \App\Support\Seo::make($page->seoPayload()); @endphp
@section('content')
<article class="mx-auto max-w-3xl">
    <h1 class="text-3xl font-bold">{{ $page->title }}</h1>
    <div class="prose prose-slate mt-8 max-w-none">{!! $page->content !!}</div>
</article>
@endsection
