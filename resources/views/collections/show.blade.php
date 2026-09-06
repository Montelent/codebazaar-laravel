@extends('layouts.app')
@section('title', $collection->name)
@section('content')
<p class="text-sm text-slate-500">Collection by <a href="{{ route('author.show', $user->username) }}" class="text-emerald-700">{{ $user->name }}</a></p>
<h1 class="mt-1 text-2xl font-bold">{{ $collection->name }}</h1>
@if($collection->description)<p class="mt-2 text-slate-600">{{ $collection->description }}</p>@endif
<div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
@foreach($items as $item)
  @include('components.item-card', ['item' => $item])
@endforeach
</div>
{{ $items->links() }}
@endsection
