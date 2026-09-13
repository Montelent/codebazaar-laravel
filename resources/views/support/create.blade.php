@extends('layouts.app')
@section('title', 'New ticket · CodeBazaar')
@section('content')
<h1 class="text-2xl font-bold">New support ticket</h1>
<form method="post" action="{{ route('support.store') }}" class="mt-6 max-w-xl space-y-4 rounded-xl border bg-white p-6">
    @csrf
    <div>
        <label class="text-sm font-medium">Subject</label>
        <input name="subject" required class="mt-1 w-full rounded-lg border px-3 py-2 text-sm" placeholder="e.g. Download issue">
    </div>
    <div>
        <label class="text-sm font-medium">Message</label>
        <textarea name="body" rows="6" required class="mt-1 w-full rounded-lg border px-3 py-2 text-sm" placeholder="Describe your issue…"></textarea>
    </div>
    <button class="rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white">Submit ticket</button>
</form>
@endsection
