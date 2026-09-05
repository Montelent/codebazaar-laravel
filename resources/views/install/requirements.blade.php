@extends('install.layout')
@section('title', 'Requirements')
@section('content')
<h1 class="text-xl font-bold">1. Server requirements</h1>
<p class="mt-1 text-sm text-slate-500">All required checks must pass before continuing.</p>
<ul class="mt-6 space-y-2">
    @foreach($checks as $c)
        <li class="flex items-start justify-between gap-3 rounded-lg border px-3 py-2 text-sm {{ $c['ok'] ? 'border-emerald-100 bg-emerald-50/50' : 'border-red-100 bg-red-50/50' }}">
            <span>{{ $c['label'] }}</span>
            <span class="shrink-0 font-medium {{ $c['ok'] ? 'text-emerald-700' : 'text-red-700' }}">{{ $c['ok'] ? 'OK' : $c['current'] }}</span>
        </li>
    @endforeach
</ul>
<div class="mt-6 flex justify-end">
    @if($passed)
        <a href="{{ route('install.database') }}" class="rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700">Continue →</a>
    @else
        <span class="rounded-lg bg-slate-200 px-5 py-2.5 text-sm text-slate-500">Fix requirements first</span>
    @endif
</div>
@endsection
