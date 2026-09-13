@extends('layouts.admin')
@section('title', 'Support tickets')
@section('content')
<div class="mb-4 flex flex-wrap items-center justify-between gap-3">
  <div>
    <h1 class="text-xl font-bold tracking-tight">Support tickets</h1>
    <p class="text-sm text-slate-500">{{ $tickets->total() }} ticket(s)</p>
  </div>
</div>

<form method="get" class="mb-4 flex flex-wrap gap-2 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
  <select name="status" class="rounded-lg border border-slate-200 px-3 py-2 text-sm">
    <option value="">All statuses</option>
    @foreach(['open','pending','closed'] as $s)
      <option value="{{ $s }}" @selected(request('status')===$s)>{{ ucfirst($s) }}</option>
    @endforeach
  </select>
  <button class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white">Filter</button>
</form>

<div class="space-y-3 md:hidden">
  @forelse($tickets as $t)
    @php
      $statusCls = match($t->status) {
        'open' => 'admin-status-ok',
        'pending' => 'admin-status-warn',
        'closed' => 'admin-status-muted',
        default => 'admin-status-muted',
      };
      $actions = [
        ['label' => 'View / reply', 'href' => route('admin.tickets.show', $t)],
        ['label' => 'Mark open', 'href' => route('admin.tickets.status', $t), 'method' => 'POST', 'confirm' => null],
      ];
      // Build status actions cleanly
      $actions = [['label' => 'View / reply', 'href' => route('admin.tickets.show', $t)]];
      if ($t->status !== 'open') {
        $actions[] = ['label' => 'Open ticket', 'href' => route('admin.tickets.status', $t).'?_status=open', 'method' => 'POST'];
      }
    @endphp
    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
      <div class="flex items-start justify-between gap-3">
        <div class="min-w-0 flex-1">
          <p class="font-semibold text-slate-900 line-clamp-2">#{{ $t->id }} — {{ $t->subject }}</p>
          <p class="mt-1 text-xs text-slate-500">{{ $t->user->email ?? $t->guest_email ?? '—' }}</p>
          <div class="mt-2 flex flex-wrap items-center gap-2">
            <span class="admin-status {{ $statusCls }}">{{ $t->status }}</span>
            <span class="text-xs text-slate-400">{{ optional($t->last_reply_at ?? $t->updated_at)->diffForHumans() }}</span>
          </div>
        </div>
        <div class="flex flex-col items-end gap-1">
          <x-admin-kebab :actions="[
            ['label' => 'View / reply', 'href' => route('admin.tickets.show', $t)],
          ]" />
        </div>
      </div>
      <div class="mt-3 flex flex-wrap gap-1 border-t border-slate-100 pt-3">
        @if($t->status !== 'open')
        <form method="post" action="{{ route('admin.tickets.status', $t) }}">@csrf
          <input type="hidden" name="status" value="open">
          <button class="rounded-lg border border-emerald-200 px-2.5 py-1 text-xs text-emerald-700 hover:bg-emerald-50">Open</button>
        </form>
        @endif
        @if($t->status !== 'pending')
        <form method="post" action="{{ route('admin.tickets.status', $t) }}">@csrf
          <input type="hidden" name="status" value="pending">
          <button class="rounded-lg border border-amber-200 px-2.5 py-1 text-xs text-amber-700 hover:bg-amber-50">Pending</button>
        </form>
        @endif
        @if($t->status !== 'closed')
        <form method="post" action="{{ route('admin.tickets.status', $t) }}">@csrf
          <input type="hidden" name="status" value="closed">
          <button class="rounded-lg border border-slate-200 px-2.5 py-1 text-xs text-slate-600 hover:bg-slate-50">Close</button>
        </form>
        @endif
      </div>
    </div>
  @empty
    <p class="rounded-2xl border border-dashed border-slate-200 bg-white p-8 text-center text-sm text-slate-500">No tickets found.</p>
  @endforelse
</div>

<div class="hidden overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm md:block">
  <div class="overflow-x-auto">
  <table class="w-full min-w-[720px] text-sm">
    <thead class="border-b border-slate-100 bg-slate-50/80 text-xs uppercase tracking-wide text-slate-500">
      <tr>
        <th class="px-4 py-3 text-left font-medium">ID</th>
        <th class="px-3 py-3 text-left font-medium">Subject</th>
        <th class="px-3 py-3 text-left font-medium">User</th>
        <th class="px-3 py-3 text-left font-medium">Status</th>
        <th class="px-3 py-3 text-left font-medium">Updated</th>
        <th class="px-4 py-3 text-right font-medium">Actions</th>
      </tr>
    </thead>
    <tbody class="divide-y divide-slate-100">
      @forelse($tickets as $t)
        @php
          $statusCls = match($t->status) {
            'open' => 'admin-status-ok',
            'pending' => 'admin-status-warn',
            'closed' => 'admin-status-muted',
            default => 'admin-status-muted',
          };
        @endphp
        <tr class="hover:bg-slate-50/60">
          <td class="px-4 py-3 font-medium">#{{ $t->id }}</td>
          <td class="px-3 py-3 font-medium text-slate-900">
            <a href="{{ route('admin.tickets.show', $t) }}" class="hover:text-emerald-700">{{ $t->subject }}</a>
          </td>
          <td class="px-3 py-3 text-slate-600">{{ $t->user->email ?? $t->guest_email ?? '—' }}</td>
          <td class="px-3 py-3"><span class="admin-status {{ $statusCls }}">{{ $t->status }}</span></td>
          <td class="px-3 py-3 text-slate-500">{{ optional($t->last_reply_at ?? $t->updated_at)->diffForHumans() }}</td>
          <td class="px-4 py-3">
            <div class="flex flex-wrap items-center justify-end gap-1">
              <a href="{{ route('admin.tickets.show', $t) }}" class="rounded-lg border border-slate-200 px-2.5 py-1 text-xs hover:bg-slate-50">View</a>
              @if($t->status !== 'open')
              <form method="post" action="{{ route('admin.tickets.status', $t) }}">@csrf
                <input type="hidden" name="status" value="open">
                <button class="rounded-lg border border-emerald-200 px-2.5 py-1 text-xs text-emerald-700 hover:bg-emerald-50">Open</button>
              </form>
              @endif
              @if($t->status !== 'closed')
              <form method="post" action="{{ route('admin.tickets.status', $t) }}">@csrf
                <input type="hidden" name="status" value="closed">
                <button class="rounded-lg border border-slate-200 px-2.5 py-1 text-xs text-slate-600 hover:bg-slate-50">Close</button>
              </form>
              @endif
              <x-admin-kebab :actions="[
                ['label' => 'View / reply', 'href' => route('admin.tickets.show', $t)],
                ['label' => 'Mark pending', 'href' => route('admin.tickets.status', $t), 'method' => 'POST'],
              ]" />
            </div>
          </td>
        </tr>
      @empty
        <tr><td colspan="6" class="px-4 py-12 text-center text-slate-500">No tickets found.</td></tr>
      @endforelse
    </tbody>
  </table>
  </div>
</div>

<div class="mt-4">{{ $tickets->links() }}</div>
@endsection
