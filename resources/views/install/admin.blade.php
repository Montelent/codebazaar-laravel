@extends('install.layout')
@section('title', 'Admin account')
@section('content')
<h1 class="text-xl font-bold">3. Admin account</h1>
<p class="mt-1 text-sm text-slate-500">This user can access /admin after install.</p>
<form method="post" action="{{ route('install.finish') }}" class="mt-6 space-y-4">
    @csrf
    <div>
        <label class="text-sm font-medium">Name</label>
        <input name="admin_name" value="{{ old('admin_name', 'Admin') }}" required class="mt-1 w-full rounded-lg border px-3 py-2 text-sm">
    </div>
    <div>
        <label class="text-sm font-medium">Email</label>
        <input type="email" name="admin_email" value="{{ old('admin_email') }}" required class="mt-1 w-full rounded-lg border px-3 py-2 text-sm">
    </div>
    <div>
        <label class="text-sm font-medium">Password</label>
        <input type="password" name="admin_password" required minlength="8" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm">
    </div>
    <div>
        <label class="text-sm font-medium">Confirm password</label>
        <input type="password" name="admin_password_confirmation" required class="mt-1 w-full rounded-lg border px-3 py-2 text-sm">
    </div>
    <div class="flex justify-between pt-2">
        <a href="{{ route('install.database') }}" class="text-sm text-slate-500 hover:underline">← Back</a>
        <button class="rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700">Install CodeBazaar</button>
    </div>
</form>
@endsection
