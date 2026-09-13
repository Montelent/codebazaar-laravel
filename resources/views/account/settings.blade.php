@extends('layouts.app')
@section('title', 'Account settings · CodeBazaar')
@section('content')
<div class="max-w-3xl">
  <h1 class="text-2xl font-bold text-slate-900">Account settings</h1>
  <p class="mt-1 text-sm text-slate-500">Update your profile details and password.</p>

  @if($errors->any())
    <div class="mt-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
      <ul class="list-disc pl-5 space-y-1">
        @foreach($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  {{-- Profile --}}
  <section class="mt-8 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
    <h2 class="text-lg font-semibold text-slate-900">Profile</h2>
    <p class="mt-1 text-sm text-slate-500">These details appear on your public author profile when applicable.</p>

    <form method="post" action="{{ route('account.settings.profile') }}" class="mt-5 space-y-4">
      @csrf
      @method('PUT')

      <div class="grid gap-4 sm:grid-cols-2">
        <div>
          <label class="block text-sm font-medium text-slate-700" for="name">Full name</label>
          <input id="name" name="name" type="text" required value="{{ old('name', $user->name) }}"
            class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700" for="username">Username</label>
          <input id="username" name="username" type="text" value="{{ old('username', $user->username) }}"
            class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
            placeholder="optional">
        </div>
      </div>

      <div>
        <label class="block text-sm font-medium text-slate-700" for="email">Email</label>
        <input id="email" name="email" type="email" required value="{{ old('email', $user->email) }}"
          class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
        @if(!$user->email_verified_at)
          <p class="mt-1 text-xs text-amber-700">Email not verified yet.</p>
        @endif
      </div>

      <div>
        <label class="block text-sm font-medium text-slate-700" for="bio">Bio</label>
        <textarea id="bio" name="bio" rows="4"
          class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
          placeholder="A short intro about you">{{ old('bio', $user->bio) }}</textarea>
      </div>

      <label class="inline-flex items-center gap-2 text-sm text-slate-700">
        <input type="checkbox" name="newsletter" value="1" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500"
          @checked(old('newsletter', $user->newsletter))>
        Receive product updates and newsletter emails
      </label>

      <div class="pt-2">
        <button type="submit" class="rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700">
          Save profile
        </button>
      </div>
    </form>
  </section>

  {{-- Password --}}
  <section class="mt-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
    <h2 class="text-lg font-semibold text-slate-900">Change password</h2>
    <p class="mt-1 text-sm text-slate-500">Use a strong password you don’t use elsewhere.</p>

    <form method="post" action="{{ route('account.settings.password') }}" class="mt-5 space-y-4">
      @csrf
      @method('PUT')

      <div>
        <label class="block text-sm font-medium text-slate-700" for="current_password">Current password</label>
        <input id="current_password" name="current_password" type="password" required autocomplete="current-password"
          class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
      </div>

      <div class="grid gap-4 sm:grid-cols-2">
        <div>
          <label class="block text-sm font-medium text-slate-700" for="password">New password</label>
          <input id="password" name="password" type="password" required autocomplete="new-password"
            class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700" for="password_confirmation">Confirm new password</label>
          <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password"
            class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
        </div>
      </div>

      <div class="pt-2">
        <button type="submit" class="rounded-xl bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white hover:bg-slate-800">
          Update password
        </button>
      </div>
    </form>
  </section>

  <p class="mt-6 text-sm text-slate-500">
    <a href="{{ route('account.index') }}" class="font-medium text-emerald-700 hover:underline">← Back to dashboard</a>
  </p>
</div>
@endsection
