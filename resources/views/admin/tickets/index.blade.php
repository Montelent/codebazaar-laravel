@extends('layouts.admin')
@section('title', 'Support tickets')
@section('content')
<h1 class="text-xl font-bold">Support tickets</h1>
<form method="get" class="mt-4 flex gap-2">
    <select name="status" class="rounded-lg border px-3 py-2 text-sm">
        <option value="">All statuses</option>
        @foreach(['open','pending','closed'] as $s)
            <option value="{{ $s }}" @selected(request('status')===$s)>{{ $s }}</option>
        @endforeach
    </select>
    <button class="rounded-lg bg-slate-800 px-4 py-2 text-sm text-white">Filter</button>
</form>
<div class="mt-4 overflow-x-auto rounded-xl border bg-white">
<table class="min-w-full text-sm">
<thead class="bg-slate-50 text-left"><tr>
<th class="px-4 py-3">ID</th><th class="px-4 py-3">Subject</th><th class="px-4 py-3">User</th><th class="px-4 py-3">Status</th><th class="px-4 py-3">Updated</th>
</tr></thead>
<tbody>
@foreach($tickets as $t)
<tr class="border-t hover:bg-slate-50">
<td class="px-4 py-3">#{{ $t->id }}</td>
<td class="px-4 py-3"><a class="text-emerald-700 font-medium" href="{{ route('admin.tickets.show', $t) }}">{{ $t->subject }}</a></td>
<td class="px-4 py-3">{{ $t->user->email ?? $t->guest_email ?? '—' }}</td>
<td class="px-4 py-3 uppercase text-xs">{{ $t->status }}</td>
<td class="px-4 py-3 text-slate-500">{{ optional($t->last_reply_at ?? $t->updated_at)->diffForHumans() }}</td>
</tr>
@endforeach
</tbody>
</table>
</div>
<div class="mt-4">{{ $tickets->links() }}</div>
@endsection
