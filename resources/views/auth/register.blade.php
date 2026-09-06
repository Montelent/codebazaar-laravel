@extends('layouts.app')
@section('title', 'Create account')
@section('content')
<div class="mx-auto max-w-md rounded-2xl border bg-white p-8 shadow-sm">
  <h1 class="text-2xl font-bold">Join CodeBazaar</h1>
  <p class="mt-1 text-sm text-slate-500">Create a free buyer account. We’ll send an email verification link.</p>
  <form method="post" action="{{ route('register') }}" class="mt-6 space-y-4">
    @csrf
    <div>
      <label class="text-sm font-medium">Name</label>
      <input name="name" value="{{ old('name') }}" required class="mt-1 w-full rounded-lg border px-3 py-2 text-sm">
      @error('name')<p class="text-xs text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
      <label class="text-sm font-medium">Username</label>
      <input name="username" value="{{ old('username') }}" required class="mt-1 w-full rounded-lg border px-3 py-2 text-sm">
      @error('username')<p class="text-xs text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
      <label class="text-sm font-medium">Email</label>
      <input type="email" name="email" value="{{ old('email') }}" required class="mt-1 w-full rounded-lg border px-3 py-2 text-sm">
      @error('email')<p class="text-xs text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
      <label class="text-sm font-medium">Password</label>
      <input type="password" name="password" required class="mt-1 w-full rounded-lg border px-3 py-2 text-sm">
    </div>
    <div>
      <label class="text-sm font-medium">Confirm password</label>
      <input type="password" name="password_confirmation" required class="mt-1 w-full rounded-lg border px-3 py-2 text-sm">
    </div>
    <label class="flex items-center gap-2 text-sm">
      <input type="checkbox" name="newsletter" value="1" @checked(old('newsletter'))>
      Subscribe to product & news updates
    </label>
    <button class="w-full rounded-lg bg-emerald-600 py-2.5 text-sm font-semibold text-white">Create account</button>
  </form>
  <p class="mt-4 text-center text-sm text-slate-500">Already have an account? <a href="{{ route('login') }}" class="text-emerald-700">Sign in</a></p>
</div>
@endsection
