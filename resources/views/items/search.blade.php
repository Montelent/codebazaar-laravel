@extends('layouts.app')
@section('title', 'Search · CodeBazaar')
@section('content')
<h1 class="text-2xl font-bold">Search @if($q) for “{{ $q }}” @endif</h1>
<div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
    @forelse($items as $item)
        @include('components.item-card', ['item' => $item])
    @empty
        <p class="text-slate-500">No results.</p>
    @endforelse
</div>
<div class="mt-6">{{ $items->links() }}</div>
@endsection
