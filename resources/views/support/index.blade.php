@extends('layouts.app')
@section('title', 'Support · CodeBazaar')
@section('content')
<div class="flex items-center justify-between gap-4">
    <h1 class="text-2xl font-bold">Support tickets</h1>
    <a href="{{ route('support.create') }}" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white">New ticket</a>
</div>
<div class="mt-6 space-y-3">
@forelse($tickets as $t)
    <a href="{{ route('support.show', $t) }}" class="block rounded-xl border bg-white p-4 hover:border-emerald-400">
        <div class="flex justify-between gap-3">
            <span class="font-medium">{{ $t->subject }}</span>
            <span class="text-xs uppercase tracking-wide text-slate-500">{{ $t->status }}</span>
        </div>
        <p class="mt-1 text-xs text-slate-500">Updated {{ optional($t->last_reply_at ?? $t->updated_at)->diffForHumans() }}</p>
    </a>
@empty
    <p class="text-sm text-slate-500">No tickets yet. <a class="text-emerald-700 underline" href="{{ route('support.create') }}">Open one</a>.</p>
@endforelse
</div>
<div class="mt-6">{{ $tickets->links() }}</div>
@endsection
