@extends('layouts.admin')
@section('title', 'Users')
@section('content')
<div class="mb-4 flex flex-wrap items-center justify-between gap-3">
  <h1 class="text-xl font-bold">Users</h1>
  <a href="{{ route('admin.users.create') }}" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white">Add user</a>
</div>

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
    <p class="rounded-xl border border-dashed bg-white p-6 text-center text-sm text-slate-500">No users yet.</p>
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
      @foreach($users as $u)
        <tr class="border-b last:border-0">
          <td class="px-4 py-3">{{ $u->name }}</td>
          <td class="px-3 py-3">{{ $u->email }}</td>
          <td class="px-3 py-3"><span class="rounded bg-slate-100 px-2 py-0.5 text-xs">{{ $u->role }}</span></td>
          <td class="px-3 py-3 whitespace-nowrap">{{ $u->created_at?->format('Y-m-d') }}</td>
          <td class="px-4 py-3 text-right"><a href="{{ route('admin.users.edit', $u) }}" class="text-emerald-700">Edit</a></td>
        </tr>
      @endforeach
    </tbody>
  </table>
</div>

<div class="mt-4">{{ $users->links() }}</div>
@endsection
