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
    <h1 class="text-xl font-bold tracking-tight">{{ $pageTitle ?? 'Users' }}</h1>
    <div class="mt-2 flex flex-wrap gap-2 text-xs">
      <a href="{{ route('admin.members.index') }}" class="rounded-full px-3 py-1 {{ $scope === 'members' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">Users (buyers)</a>
      <a href="{{ route('admin.staff.index') }}" class="rounded-full px-3 py-1 {{ $scope === 'staff' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">Admins / Authors</a>
      <a href="{{ route('admin.users.index') }}" class="rounded-full px-3 py-1 {{ $scope === 'all' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">All</a>
    </div>
  </div>
  <a href="{{ route('admin.users.create') }}" class="inline-flex items-center rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700">+ Add user</a>
</div>

<form method="get" action="{{ $filterAction }}" class="mb-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
  <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
    <div class="sm:col-span-2">
      <label class="text-xs font-medium text-slate-500">Search</label>
      <input type="search" name="q" value="{{ request('q') }}" placeholder="Name, email, username…" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
    </div>
    @if($scope !== 'members')
    <div>
      <label class="text-xs font-medium text-slate-500">Role</label>
      <select name="role" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
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
      <select name="newsletter" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
        <option value="">Any</option>
        <option value="1" @selected(request('newsletter') === '1')>Subscribed</option>
        <option value="0" @selected(request('newsletter') === '0')>Not subscribed</option>
      </select>
    </div>
  </div>
  <div class="mt-3 flex flex-wrap gap-2">
    <button class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white">Filter</button>
    <a href="{{ $filterAction }}" class="rounded-lg border border-slate-200 px-4 py-2 text-sm text-slate-600 hover:bg-slate-50">Reset</a>
  </div>
</form>

<div class="space-y-3 md:hidden">
  @forelse($users as $u)
    @php
      $actions = [
        ['label' => 'Edit / funds / email', 'href' => route('admin.users.edit', $u)],
        ['label' => 'Delete', 'href' => route('admin.users.destroy', $u), 'method' => 'DELETE', 'danger' => true, 'confirm' => 'Delete this user?'],
      ];
    @endphp
    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
      <div class="flex items-start justify-between gap-3">
        <div class="min-w-0 flex-1">
          <p class="font-semibold text-slate-900">{{ $u->name }}</p>
          <p class="mt-0.5 truncate text-sm text-slate-600">{{ $u->email }}</p>
          <div class="mt-2 flex flex-wrap items-center gap-2">
            <span class="admin-status admin-status-muted">{{ $u->role }}</span>
            <span class="text-xs text-slate-400">joined {{ $u->created_at?->format('Y-m-d') }}</span>
          </div>
        </div>
        <x-admin-kebab :actions="$actions" />
      </div>
    </div>
  @empty
    <p class="rounded-2xl border border-dashed border-slate-200 bg-white p-8 text-center text-sm text-slate-500">No users match your filters.</p>
  @endforelse
</div>

<div class="hidden overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm md:block">
  <div class="overflow-x-auto">
  <table class="w-full min-w-[560px] text-left text-sm">
    <thead class="border-b border-slate-100 bg-slate-50/80 text-xs uppercase tracking-wide text-slate-500">
      <tr>
        <th class="px-4 py-3 font-medium">Name</th>
        <th class="px-3 py-3 font-medium">Email</th>
        <th class="px-3 py-3 font-medium">Role</th>
        <th class="px-3 py-3 font-medium">Joined</th>
        <th class="px-4 py-3 text-right font-medium">Actions</th>
      </tr>
    </thead>
    <tbody class="divide-y divide-slate-100">
      @forelse($users as $u)
        @php
          $actions = [
            ['label' => 'Edit / funds / email', 'href' => route('admin.users.edit', $u)],
            ['label' => 'Delete', 'href' => route('admin.users.destroy', $u), 'method' => 'DELETE', 'danger' => true, 'confirm' => 'Delete this user?'],
          ];
        @endphp
        <tr class="hover:bg-slate-50/60">
          <td class="px-4 py-3 font-medium text-slate-900">{{ $u->name }}</td>
          <td class="px-3 py-3 text-slate-600">{{ $u->email }}</td>
          <td class="px-3 py-3"><span class="admin-status admin-status-muted">{{ $u->role }}</span></td>
          <td class="px-3 py-3 whitespace-nowrap text-slate-500">{{ $u->created_at?->format('Y-m-d') }}</td>
          <td class="px-4 py-3 text-right"><x-admin-kebab :actions="$actions" /></td>
        </tr>
      @empty
        <tr><td colspan="5" class="px-4 py-12 text-center text-slate-500">No users match your filters.</td></tr>
      @endforelse
    </tbody>
  </table>
  </div>
</div>

<div class="mt-4">{{ $users->links() }}</div>
@endsection
