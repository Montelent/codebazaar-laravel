@extends('layouts.admin')
@section('title', 'Tags')
@section('content')
<h1 class="text-xl font-bold">Tags manager</h1>
<p class="mt-1 text-sm text-slate-500">Master tag list used when editing products. One tag per line (or comma-separated).</p>

<form method="post" action="{{ route('admin.tags.update') }}" class="mt-6 max-w-xl space-y-4 rounded-xl border bg-white p-6">
  @csrf @method('PUT')
  <textarea name="tags_text" rows="12" class="w-full rounded-lg border px-3 py-2 font-mono text-sm">{{ implode("\n", $tags) }}</textarea>
  <button class="rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white">Save tags</button>
</form>

@if(!empty($used))
<section class="mt-10">
  <h2 class="font-semibold">Tags currently on products</h2>
  <div class="mt-3 flex flex-wrap gap-2">
    @foreach($used as $tag => $count)
      <span class="rounded-full bg-slate-100 px-3 py-1 text-xs">{{ $tag }} <strong>{{ $count }}</strong></span>
    @endforeach
  </div>
</section>
@endif
@endsection
