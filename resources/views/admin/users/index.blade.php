@extends('layouts.admin')
@section('title', 'Users')
@section('content')
<div class="mb-4 flex justify-between"><h1 class="text-xl font-bold">Users</h1>
<a href="{{ route('admin.users.create') }}" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white">Add user</a></div>
<div class="overflow-hidden rounded-xl border bg-white">
<table class="w-full text-left text-sm">
<thead class="border-b bg-slate-50"><tr><th class="px-4 py-3">Name</th><th>Email</th><th>Role</th><th>Joined</th><th></th></tr></thead>
<tbody>
@foreach($users as $u)
<tr class="border-b">
    <td class="px-4 py-3">{{ $u->name }}</td>
    <td>{{ $u->email }}</td>
    <td><span class="rounded bg-slate-100 px-2 py-0.5 text-xs">{{ $u->role }}</span></td>
    <td>{{ $u->created_at?->format('Y-m-d') }}</td>
    <td class="px-4 text-right"><a href="{{ route('admin.users.edit', $u) }}" class="text-emerald-700">Edit</a></td>
</tr>
@endforeach
</tbody></table></div>
{{ $users->links() }}
@endsection
