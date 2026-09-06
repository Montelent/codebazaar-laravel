@extends('layouts.app')
@section('title', $author->name ?: $author->username)
@section('content')
<div class="cc-author-hero rounded-2xl border bg-white p-6 shadow-sm sm:p-8">
  <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
    <div>
      <h1 class="text-2xl font-bold sm:text-3xl">{{ $author->name ?: $author->username }}</h1>
      <p class="mt-1 text-sm text-slate-500">@{{ $author->username }} · Member since {{ $author->created_at?->format('M Y') }}</p>
      <p class="mt-2 text-sm text-slate-600">{{ $followers }} followers · {{ $following }} following · {{ $items->total() }} items</p>
      @if($author->bio)<p class="mt-4 max-w-2xl text-slate-700">{{ $author->bio }}</p>@endif
    </div>
    @auth
      @if(auth()->id() !== $author->id)
        <form method="post" action="{{ route('follow.toggle', $author->id) }}">
          @csrf
          <button class="rounded-lg px-4 py-2 text-sm font-semibold {{ $isFollowing ? 'border border-slate-300' : 'bg-emerald-600 text-white' }}">
            {{ $isFollowing ? 'Following' : 'Follow' }}
          </button>
        </form>
      @endif
    @else
      <a href="{{ route('login') }}" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white">Sign in to follow</a>
    @endauth
  </div>
</div>

@if($collections->count())
<section class="mt-10">
  <h2 class="text-lg font-semibold">Public collections</h2>
  <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
    @foreach($collections as $c)
      <a href="{{ route('collections.show', [$author->username, $c->slug]) }}" class="rounded-xl border bg-white p-4 hover:border-emerald-400">
        <p class="font-medium">{{ $c->name }}</p>
        <p class="text-xs text-slate-500">{{ $c->items_count }} items</p>
      </a>
    @endforeach
  </div>
</section>
@endif

<h2 class="mt-10 text-lg font-semibold">Items</h2>
<div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
@foreach($items as $item)
  @include('components.item-card', ['item' => $item])
@endforeach
</div>
{{ $items->links() }}
@endsection
