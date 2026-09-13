@extends('layouts.admin')
@section('title', 'Ticket #'.$ticket->id)
@section('content')
<a href="{{ route('admin.tickets.index') }}" class="text-sm text-emerald-700">← Tickets</a>
<h1 class="mt-2 text-xl font-bold">#{{ $ticket->id }} — {{ $ticket->subject }}</h1>
<p class="text-sm text-slate-500">{{ $ticket->user->email ?? $ticket->guest_email }} · <span class="uppercase">{{ $ticket->status }}</span></p>

<div class="mt-6 space-y-3 rounded-xl border bg-slate-50 p-4 max-h-[28rem] overflow-y-auto">
@foreach($ticket->messages as $msg)
    <div class="flex {{ $msg->is_staff ? 'justify-end' : 'justify-start' }}">
        <div class="max-w-[85%] rounded-2xl px-4 py-2 text-sm {{ $msg->is_staff ? 'bg-emerald-600 text-white' : 'bg-white border' }}">
            <p class="whitespace-pre-wrap">{{ $msg->body }}</p>
            <p class="mt-1 text-[10px] opacity-70">{{ $msg->is_staff ? 'Staff' : 'Customer' }} · {{ $msg->created_at->format('M j, H:i') }}</p>
        </div>
    </div>
@endforeach
</div>

<form method="post" action="{{ route('admin.tickets.reply', $ticket) }}" class="mt-4 space-y-3 rounded-xl border bg-white p-4">
    @csrf
    <textarea name="body" rows="3" required class="w-full rounded-lg border px-3 py-2 text-sm" placeholder="Reply as support…"></textarea>
    <div class="flex flex-wrap items-center gap-3">
        <select name="status" class="rounded-lg border px-3 py-2 text-sm">
            <option value="pending">Mark pending</option>
            <option value="open">Keep open</option>
            <option value="closed">Close ticket</option>
        </select>
        <button class="rounded-lg bg-emerald-600 px-5 py-2 text-sm font-semibold text-white">Send reply</button>
    </div>
</form>
@endsection
