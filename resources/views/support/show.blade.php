@extends('layouts.app')
@section('title', $ticket->subject.' · Support')
@section('content')
<div class="mx-auto max-w-2xl">
    <div class="flex items-center justify-between gap-3">
        <div>
            <a href="{{ route('support.index') }}" class="text-sm text-emerald-700">← All tickets</a>
            <h1 class="text-xl font-bold mt-1">{{ $ticket->subject }}</h1>
            <p class="text-xs text-slate-500 uppercase">{{ $ticket->status }}</p>
        </div>
    </div>

    <div class="mt-6 space-y-3 rounded-xl border bg-slate-50 p-4 max-h-[28rem] overflow-y-auto">
        @foreach($ticket->messages as $msg)
            <div class="flex {{ $msg->is_staff ? 'justify-start' : 'justify-end' }}">
                <div class="max-w-[85%] rounded-2xl px-4 py-2 text-sm {{ $msg->is_staff ? 'bg-white border text-slate-800' : 'bg-emerald-600 text-white' }}">
                    <p class="whitespace-pre-wrap">{{ $msg->body }}</p>
                    <p class="mt-1 text-[10px] opacity-70">{{ $msg->is_staff ? 'Support' : 'You' }} · {{ $msg->created_at->format('M j, H:i') }}</p>
                </div>
            </div>
        @endforeach
    </div>

    @if($ticket->status !== 'closed')
    <form method="post" action="{{ route('support.reply', $ticket) }}" class="mt-4 flex gap-2">
        @csrf
        <input type="text" name="body" required placeholder="Type a message…" class="flex-1 rounded-full border px-4 py-2 text-sm">
        <button class="rounded-full bg-emerald-600 px-5 py-2 text-sm font-semibold text-white">Send</button>
    </form>
    @else
        <p class="mt-4 text-sm text-slate-500">This ticket is closed.</p>
    @endif
</div>
@endsection
