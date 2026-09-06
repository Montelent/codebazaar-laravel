@extends('layouts.app')
@section('title', 'Wishlist')
@section('content')
<h1 class="text-2xl font-bold">Wishlist</h1>
<p class="mt-1 text-sm text-slate-500">Saved items for later.</p>
<div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
@forelse($items as $item)
  @include('components.item-card', ['item' => $item])
@empty
  <p class="text-slate-500">No wishlist items yet.</p>
@endforelse
</div>
@endsection
