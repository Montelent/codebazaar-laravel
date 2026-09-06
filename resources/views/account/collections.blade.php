@extends('layouts.app')
@section('title', 'Collections')
@section('content')
<h1 class="text-2xl font-bold">My collections</h1>
@if(!empty($needsMigrate))
  <p class="mt-4 text-amber-700">Run <strong>Admin → Run DB migrations</strong> first.</p>
@endif

<form method="post" action="{{ route('collections.store') }}" class="mt-6 max-w-lg space-y-3 rounded-2xl border bg-white p-5 shadow-sm">
  @csrf
  <h2 class="font-semibold">New collection</h2>
  <input name="name" required placeholder="Name" class="w-full rounded-lg border px-3 py-2 text-sm">
  <textarea name="description" rows="2" placeholder="Description" class="w-full rounded-lg border px-3 py-2 text-sm"></textarea>
  <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_public" value="1" checked> Public</label>
  <button class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white">Create</button>
</form>

<div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
@foreach($collections as $c)
  <div class="rounded-2xl border bg-white p-5 shadow-sm">
    <a href="{{ route('collections.show', [auth()->user()->username, $c->slug]) }}" class="text-lg font-semibold text-emerald-800 hover:underline">{{ $c->name }}</a>
    <p class="mt-1 text-sm text-slate-500">{{ $c->items_count ?? $c->items()->count() }} items · {{ $c->is_public ? 'Public' : 'Private' }}</p>
    <p class="mt-2 text-sm text-slate-600">{{ $c->description }}</p>
    <form method="post" action="{{ route('collections.destroy', $c) }}" class="mt-3" onsubmit="return confirm('Delete collection?')">@csrf @method('DELETE')
      <button class="text-xs text-red-600">Delete</button>
    </form>
  </div>
@endforeach
</div>
@endsection
