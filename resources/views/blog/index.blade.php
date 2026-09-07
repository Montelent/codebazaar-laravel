@extends('layouts.app')
@section('title', 'Blog')
@section('content')
{!! \App\Support\AdSlots::render('blog_index_top') !!}
<h1 class="text-2xl font-bold">Blog</h1>
<div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
@forelse($posts as $post)
<a href="{{ route('blog.show', $post->slug) }}" class="rounded-xl border bg-white p-5 shadow-sm hover:border-emerald-300">
    <h2 class="font-semibold text-slate-900">{{ $post->title }}</h2>
    <p class="mt-2 line-clamp-3 text-sm text-slate-600">{{ $post->excerpt }}</p>
</a>
@empty
<p class="text-slate-500">No posts yet.</p>
@endforelse
</div>
{{ $posts->links() }}
{!! \App\Support\AdSlots::render('blog_index_bottom') !!}
@endsection
