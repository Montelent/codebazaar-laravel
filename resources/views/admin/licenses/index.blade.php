@extends('layouts.admin')
@section('title', 'Licenses')
@section('content')
<div class="mb-4 flex justify-between"><h1 class="text-xl font-bold">License types</h1>
<a href="{{ route('admin.licenses.create') }}" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white">Add license</a></div>
<div class="space-y-3">
@foreach($licenses as $lic)
<div class="rounded-xl border bg-white p-4">
    <div class="flex justify-between gap-4">
        <div>
            <h2 class="font-semibold">{{ $lic['name'] ?? $lic['id'] }}</h2>
            <p class="text-xs text-slate-500">ID: {{ $lic['id'] }} · {{ $lic['price_label'] ?? '' }}</p>
            <div class="prose prose-sm mt-2 max-w-none">{!! $lic['description'] ?? '' !!}</div>
        </div>
        <div class="shrink-0 text-sm">
            <a href="{{ route('admin.licenses.edit', $lic['id']) }}" class="text-emerald-700">Edit</a>
            <form class="inline" method="post" action="{{ route('admin.licenses.destroy', $lic['id']) }}">@csrf @method('DELETE')
                <button class="ml-2 text-red-600">Delete</button>
            </form>
        </div>
    </div>
</div>
@endforeach
</div>
@endsection
