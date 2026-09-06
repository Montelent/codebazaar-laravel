@extends('layouts.admin')
@section('title', 'Newsletter')
@section('content')
<h1 class="text-xl font-bold">Newsletter</h1>
<p class="mt-1 text-sm text-slate-500">
  {{ $subscribers->count() }} newsletter subscribers · {{ $allUsers }} total users.
  Configure <code class="text-xs">MAIL_*</code> in <code class="text-xs">.env</code> (SMTP) for real delivery.
</p>

<form method="post" action="{{ route('admin.newsletter.send') }}" class="mt-6 max-w-2xl space-y-4 rounded-xl border bg-white p-6">
  @csrf
  <div>
    <label class="text-sm font-medium">Audience</label>
    <select name="audience" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm">
      <option value="newsletter">Newsletter opt-in only</option>
      <option value="verified">Email-verified users</option>
      <option value="all">All users</option>
    </select>
  </div>
  <div>
    <label class="text-sm font-medium">Subject</label>
    <input name="subject" required class="mt-1 w-full rounded-lg border px-3 py-2 text-sm" placeholder="New items this week">
  </div>
  <div>
    <label class="text-sm font-medium">Body (HTML allowed)</label>
    <textarea name="body" class="tinymce" required></textarea>
  </div>
  <button class="rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white" onclick="return confirm('Send to selected audience?')">Send newsletter</button>
</form>

<section class="mt-10 max-w-2xl">
  <h2 class="font-semibold">Subscribers</h2>
  <ul class="mt-3 divide-y rounded-xl border bg-white text-sm">
    @forelse($subscribers as $s)
      <li class="flex justify-between px-4 py-2">
        <span>{{ $s->email }} @if($s->name)<span class="text-slate-400">· {{ $s->name }}</span>@endif</span>
        <span class="text-xs {{ $s->email_verified_at ? 'text-emerald-600' : 'text-amber-600' }}">{{ $s->email_verified_at ? 'verified' : 'unverified' }}</span>
      </li>
    @empty
      <li class="px-4 py-3 text-slate-500">No newsletter subscribers yet.</li>
    @endforelse
  </ul>
</section>

@if(count($logs))
<section class="mt-10 max-w-2xl">
  <h2 class="font-semibold">Recent sends</h2>
  <ul class="mt-3 space-y-2 text-sm">
    @foreach($logs as $log)
      <li class="rounded-lg border bg-white px-4 py-2">{{ $log->subject }} · {{ $log->recipients }} recipients · {{ $log->created_at }}</li>
    @endforeach
  </ul>
</section>
@endif
@endsection
