@extends('layouts.admin')
@section('title', 'Ticket #'.$ticket->id)
@section('content')
<a href="{{ route('admin.tickets.index') }}" class="text-sm text-emerald-700">← Tickets</a>
<div class="mt-2 flex flex-wrap items-start justify-between gap-3">
    <div>
        <h1 class="text-xl font-bold">#{{ $ticket->id }} — {{ $ticket->subject }}</h1>
        <p class="text-sm text-slate-500">{{ $ticket->user->email ?? $ticket->guest_email }} · <span class="uppercase">{{ $ticket->status }}</span></p>
    </div>
    <div class="flex flex-wrap gap-2">
        <form method="post" action="{{ route('admin.tickets.status', $ticket) }}">@csrf
            <input type="hidden" name="status" value="open">
            <button class="rounded-lg border border-emerald-300 bg-emerald-50 px-3 py-1.5 text-sm text-emerald-800">Open ticket</button>
        </form>
        <form method="post" action="{{ route('admin.tickets.status', $ticket) }}">@csrf
            <input type="hidden" name="status" value="pending">
            <button class="rounded-lg border border-amber-300 bg-amber-50 px-3 py-1.5 text-sm text-amber-800">Mark pending</button>
        </form>
        <form method="post" action="{{ route('admin.tickets.status', $ticket) }}">@csrf
            <input type="hidden" name="status" value="closed">
            <button class="rounded-lg border border-slate-300 bg-slate-100 px-3 py-1.5 text-sm text-slate-700">Close ticket</button>
        </form>
    </div>
</div>

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
    <p class="text-xs text-slate-500">User will be emailed this reply (if SMTP is configured) with a link to continue the chat online.</p>
    <div class="flex flex-wrap items-center gap-3">
        <select name="status" class="rounded-lg border px-3 py-2 text-sm">
            <option value="pending">Mark pending after reply</option>
            <option value="open">Keep open</option>
            <option value="closed">Close after reply</option>
        </select>
        <button class="rounded-lg bg-emerald-600 px-5 py-2 text-sm font-semibold text-white">Send reply</button>
    </div>
</form>
@endsection
