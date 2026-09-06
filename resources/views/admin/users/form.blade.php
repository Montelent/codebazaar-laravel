@extends('layouts.admin')
@section('title', $user->exists ? 'Edit user' : 'Add user')
@section('content')
<form method="post" action="{{ $user->exists ? route('admin.users.update', $user) : route('admin.users.store') }}" class="max-w-lg space-y-4 rounded-xl border bg-white p-6">
@csrf @if($user->exists) @method('PUT') @endif
<div><label class="text-sm font-medium">Name</label><input name="name" value="{{ old('name', $user->name) }}" required class="mt-1 w-full rounded-lg border px-3 py-2 text-sm"></div>
<div><label class="text-sm font-medium">Email</label><input type="email" name="email" value="{{ old('email', $user->email) }}" required class="mt-1 w-full rounded-lg border px-3 py-2 text-sm"></div>
<div><label class="text-sm font-medium">Username</label><input name="username" value="{{ old('username', $user->username) }}" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm"></div>
<div><label class="text-sm font-medium">Password {{ $user->exists ? '(leave blank to keep)' : '' }}</label><input type="password" name="password" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm" {{ $user->exists ? '' : 'required' }}></div>
<div><label class="text-sm font-medium">Role</label>
<select name="role" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm">
@foreach(['buyer','author','admin'] as $r)
<option value="{{ $r }}" @selected(old('role', $user->role ?: 'buyer')===$r)>{{ $r }}</option>
@endforeach
</select></div>
<label class="flex items-center gap-2 text-sm"><input type="checkbox" name="newsletter" value="1" @checked(old('newsletter', $user->newsletter))> Newsletter</label>
<button class="rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white">Save</button>
</form>
@endsection
