@extends('layouts.app')
@section('title', 'Downloads · CodeBazaar')
@section('content')
<h1 class="text-2xl font-bold">Downloads</h1>
<ul class="mt-6 space-y-3">
    @forelse($items as $oi)
        <li class="flex items-center justify-between rounded-xl border bg-white p-4">
            <span class="font-medium">{{ $oi->title }}</span>
            @if($oi->item_id)
                <a href="{{ route('account.download', $oi->item_id) }}" class="rounded-lg bg-emerald-600 px-3 py-1.5 text-sm font-medium text-white">Download</a>
            @endif
        </li>
    @empty
        <p class="text-slate-500">No downloads yet.</p>
    @endforelse
</ul>
@endsection
