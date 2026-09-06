@extends('layouts.app')
@section('title', 'Licenses')
@section('content')
<h1 class="text-2xl font-bold">License terms</h1>
<div class="mt-8 space-y-6">
@foreach($licenses as $lic)
<div class="rounded-xl border bg-white p-6">
    <h2 class="text-lg font-semibold">{{ $lic['name'] ?? '' }}</h2>
    <p class="text-sm text-slate-500">{{ $lic['price_label'] ?? '' }}</p>
    <div class="prose mt-3 max-w-none">{!! $lic['description'] ?? '' !!}</div>
</div>
@endforeach
</div>
@endsection
