@extends('layouts.app')
@section('title', $category->name . ' · CodeBazaar')
@section('content')
<h1 class="text-2xl font-bold">{{ $category->name }}</h1>
<div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
    @foreach($items as $item)
        @include('components.item-card', ['item' => $item])
    @endforeach
</div>
<div class="mt-6">{{ $items->links() }}</div>
@endsection
