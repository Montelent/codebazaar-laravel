@extends('layouts.admin')
@section('title', 'Support tickets')
@section('content')
<h1 class="text-xl font-bold">Support tickets</h1>
<form method="get" class="mt-4 flex flex-wrap gap-2">
    <select name="status" class="rounded-lg border px-3 py-2 text-sm">
        <option value="">All statuses</option>
        @foreach(['open','pending','closed'] as $s)
            <option value="{{ $s }}" @selected(request('status')===$s)>{{ ucfirst($s) }}</option>
        @endforeach
    </select>
    <button class="rounded-lg bg-slate-800 px-4 py-2 text-sm text-white">Filter</button>
</form>
<div class="mt-4 overflow-x-auto rounded-xl border bg-white">
<table class="min-w-full text-sm">
<thead class="bg-slate-50 text-left"><tr>
<th class="px-4 py-3">ID</th>
<th class="px-4 py-3">Subject</th>
<th class="px-4 py-3">User</th>
<th class="px-4 py-3">Status</th>
<th class="px-4 py-3">Updated</th>
<th class="px-4 py-3">Actions</th>
</tr></thead>
<tbody>
@foreach($tickets as $t)
<tr class="border-t hover:bg-slate-50">
<td class="px-4 py-3">#{{ $t->id }}</td>
<td class="px-4 py-3"><a class="text-emerald-700 font-medium" href="{{ route('admin.tickets.show', $t) }}">{{ $t->subject }}</a></td>
<td class="px-4 py-3">{{ $t->user->email ?? $t->guest_email ?? '—' }}</td>
<td class="px-4 py-3">
    <span class="rounded-full px-2 py-0.5 text-xs uppercase
        {{ $t->status === 'open' ? 'bg-emerald-50 text-emerald-700' : ($t->status === 'closed' ? 'bg-slate-100 text-slate-600' : 'bg-amber-50 text-amber-700') }}">
        {{ $t->status }}
    </span>
</td>
<td class="px-4 py-3 text-slate-500">{{ optional($t->last_reply_at ?? $t->updated_at)->diffForHumans() }}</td>
<td class="px-4 py-3">
    <div class="flex flex-wrap gap-1">
        <a href="{{ route('admin.tickets.show', $t) }}" class="rounded border px-2 py-1 text-xs hover:bg-slate-50">View</a>
        @if($t->status !== 'open')
        <form method="post" action="{{ route('admin.tickets.status', $t) }}" class="inline">@csrf
            <input type="hidden" name="status" value="open">
            <button class="rounded border border-emerald-200 px-2 py-1 text-xs text-emerald-700 hover:bg-emerald-50">Open</button>
        </form>
        @endif
        @if($t->status !== 'pending')
        <form method="post" action="{{ route('admin.tickets.status', $t) }}" class="inline">@csrf
            <input type="hidden" name="status" value="pending">
            <button class="rounded border border-amber-200 px-2 py-1 text-xs text-amber-700 hover:bg-amber-50">Pending</button>
        </form>
        @endif
        @if($t->status !== 'closed')
        <form method="post" action="{{ route('admin.tickets.status', $t) }}" class="inline">@csrf
            <input type="hidden" name="status" value="closed">
            <button class="rounded border border-slate-300 px-2 py-1 text-xs text-slate-600 hover:bg-slate-100">Close</button>
        </form>
        @endif
    </div>
</td>
</tr>
@endforeach
</tbody>
</table>
</div>
<div class="mt-4">{{ $tickets->links() }}</div>
@endsection
