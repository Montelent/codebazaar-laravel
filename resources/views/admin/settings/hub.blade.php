@extends('layouts.admin')
@section('title', 'Settings')
@section('content')
<h1 class="text-xl font-bold">Settings</h1>
<p class="mt-1 text-sm text-slate-500">Split screens for payments, menus, schema, and site appearance.</p>
<div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
@foreach($cards as $card)
  <a href="{{ route($card['route']) }}" class="rounded-xl border bg-white p-5 shadow-sm transition hover:border-emerald-400 hover:shadow-md">
    <h2 class="font-semibold text-slate-900">{{ $card['title'] }}</h2>
    <p class="mt-1 text-sm text-slate-500">{{ $card['desc'] }}</p>
  </a>
@endforeach
</div>
@endsection
