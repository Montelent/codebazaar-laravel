@extends('layouts.app')
@section('title', 'Settings · Admin')
@section('content')
<h1 class="text-2xl font-bold">Site settings</h1>
<form method="post" action="{{ route('admin.settings.update') }}" class="mt-6 max-w-xl space-y-4 rounded-xl border bg-white p-6">
    @csrf @method('PUT')
    <div><label class="text-sm font-medium">Site name</label>
        <input name="site_name" value="{{ $site['name'] ?? 'CodeBazaar' }}" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm"></div>
    <div><label class="text-sm font-medium">Tagline</label>
        <input name="site_tagline" value="{{ $site['tagline'] ?? '' }}" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm"></div>
    <div><label class="text-sm font-medium">Hero title</label>
        <input name="hero_title" value="{{ $hero['title'] ?? '' }}" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm"></div>
    <div><label class="text-sm font-medium">Hero subtitle</label>
        <textarea name="hero_subtitle" rows="2" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm">{{ $hero['subtitle'] ?? '' }}</textarea></div>
    <button class="rounded-lg bg-emerald-600 px-5 py-2 font-semibold text-white">Save settings</button>
</form>
@endsection
