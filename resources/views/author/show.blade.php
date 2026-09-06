@extends('layouts.app')
@section('title', $author->name ?: $author->username)
@section('content')
<div class="rounded-2xl border bg-white p-6 shadow-sm">
  <h1 class="text-2xl font-bold">{{ $author->name ?: $author->username }}</h1>
  <p class="mt-1 text-sm text-slate-500">@{{ $author->username }} · Member since {{ $author->created_at?->format('M Y') }}</p>
  @if($author->bio)<p class="mt-4 text-slate-700">{{ $author->bio }}</p>@endif
</div>
<h2 class="mt-10 text-lg font-semibold">Items</h2>
<div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
@foreach($items as $item)
  @include('components.item-card', ['item' => $item])
@endforeach
</div>
{{ $items->links() }}
@endsection
