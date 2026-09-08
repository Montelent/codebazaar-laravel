@extends('layouts.admin')
@section('title', $pageTitle ?? 'Users')
@section('content')
@php
  $scope = $scope ?? 'all';
  $filterAction = match ($scope) {
      'members' => route('admin.members.index'),
      'staff' => route('admin.staff.index'),
      default => route('admin.users.index'),
  };
@endphp
<div class="mb-4 flex flex-wrap items-center justify-between gap-3">
  <div>
    <h1 class="text-xl font-bold">{{ $pageTitle ?? 'Users' }}</h1>
    <div class="mt-1 flex flex-wrap gap-2 text-xs">
      <a href="{{ route('admin.members.index') }}" class="rounded-full px-2.5 py-1 {{ $scope === 'members' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">Users (buyers)</a>
      <a href="{{ route('admin.staff.index') }}" class="rounded-full px-2.5 py-1 {{ $scope === 'staff' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">Admins / Authors</a>
      <a href="{{ route('admin.users.index') }}" class="rounded-full px-2.5 py-1 {{ $scope === 'all' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">All</a>
    </div>
  </div>
  <a href="{{ route('admin.users.create') }}" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white">Add user</a>
</div>

<form method="get" action="{{ $filterAction }}" class="mb-4 rounded-xl border bg-white p-4 shadow-sm">
  <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
    <div class="sm:col-span-2">
      <label class="text-xs font-medium text-slate-500">Search</label>
      <input type="search" name="q" value="{{ request('q') }}" placeholder="Name, email, username…" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm">
    </div>
    @if($scope !== 'members')
    <div>
      <label class="text-xs font-medium text-slate-500">Role</label>
      <select name="role" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm">
        <option value="">All roles</option>
        @if($scope === 'staff' || $scope === 'all')
          <option value="admin" @selected(request('role') === 'admin')>Admin</option>
          <option value="author" @selected(request('role') === 'author')>Author</option>
        @endif
        @if($scope === 'all')
          <option value="buyer" @selected(request('role') === 'buyer')>Buyer</option>
        @endif
      </select>
    </div>
    @endif
    <div>
      <label class="text-xs font-medium text-slate-500">Newsletter</label>
      <select name="newsletter" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm">
        <option value="">Any</option>
        <option value="1" @selected(request('newsletter') === '1')>Subscribed</option>
        <option value="0" @selected(request('newsletter') === '0')>Not subscribed</option>
      </select>
    </div>
    <div>
      <label class="text-xs font-medium text-slate-500">Joined from</label>
      <input type="date" name="from" value="{{ request('from') }}" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm">
    </div>
    <div>
      <label class="text-xs font-medium text-slate-500">Joined to</label>
      <input type="date" name="to" value="{{ request('to') }}" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm">
    </div>
  </div>
  <div class="mt-3 flex flex-wrap gap-2">
    <button class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white">Filter</button>
    <a href="{{ $filterAction }}" class="rounded-lg border px-4 py-2 text-sm text-slate-600 hover:bg-slate-50">Reset</a>
  </div>
</form>

<div class="space-y-3 md:hidden">
  @forelse($users as $u)
    <div class="rounded-xl border bg-white p-4 shadow-sm">
      <div class="flex items-start justify-between gap-3">
        <div class="min-w-0">
          <p class="font-semibold text-slate-900">{{ $u->name }}</p>
          <p class="mt-0.5 truncate text-sm text-slate-600">{{ $u->email }}</p>
          <p class="mt-1 text-xs text-slate-500">
            <span class="rounded bg-slate-100 px-2 py-0.5">{{ $u->role }}</span>
            · joined {{ $u->created_at?->format('Y-m-d') }}
          </p>
        </div>
        <a href="{{ route('admin.users.edit', $u) }}" class="shrink-0 text-sm font-medium text-emerald-700">Edit</a>
      </div>
    </div>
  @empty
    <p class="rounded-xl border border-dashed bg-white p-6 text-center text-sm text-slate-500">No users match your filters.</p>
  @endforelse
</div>

<div class="hidden overflow-x-auto rounded-xl border bg-white md:block">
  <table class="w-full min-w-[560px] text-left text-sm">
    <thead class="border-b bg-slate-50">
      <tr>
        <th class="px-4 py-3">Name</th>
        <th class="px-3 py-3">Email</th>
        <th class="px-3 py-3">Role</th>
        <th class="px-3 py-3">Joined</th>
        <th class="px-4 py-3"></th>
      </tr>
    </thead>
    <tbody>
      @forelse($users as $u)
        <tr class="border-b last:border-0">
          <td class="px-4 py-3">{{ $u->name }}</td>
          <td class="px-3 py-3">{{ $u->email }}</td>
          <td class="px-3 py-3"><span class="rounded bg-slate-100 px-2 py-0.5 text-xs">{{ $u->role }}</span></td>
          <td class="px-3 py-3 whitespace-nowrap">{{ $u->created_at?->format('Y-m-d') }}</td>
          <td class="px-4 py-3 text-right"><a href="{{ route('admin.users.edit', $u) }}" class="text-emerald-700">Edit</a></td>
        </tr>
      @empty
        <tr><td colspan="5" class="px-4 py-8 text-center text-slate-500">No users match your filters.</td></tr>
      @endforelse
    </tbody>
  </table>
</div>

<div class="mt-4">{{ $users->links() }}</div>
@endsection
