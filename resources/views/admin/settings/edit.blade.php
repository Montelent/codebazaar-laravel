@extends('layouts.admin')
@section('title', 'General settings')
@section('content')
<a href="{{ route('admin.settings.hub') }}" class="text-sm text-emerald-700">← Settings hub</a>
<form method="post" action="{{ route('admin.settings.general.update') }}" class="mx-auto mt-4 max-w-3xl space-y-8">
@csrf @method('PUT')

<section class="rounded-xl border bg-white p-6">
    <h2 class="font-semibold">Hero</h2>
    <div class="mt-4 space-y-3">
        <div><label class="text-sm">Title</label><input name="hero_title" value="{{ $hero['title'] ?? '' }}" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm"></div>
        <div><label class="text-sm">Subtitle</label><input name="hero_subtitle" value="{{ $hero['subtitle'] ?? '' }}" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm"></div>
        <div><label class="text-sm">CTA label</label><input name="hero_cta" value="{{ $hero['cta'] ?? '' }}" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm"></div>
        <div><label class="text-sm">Image URL</label><input name="hero_image" value="{{ $hero['image'] ?? '' }}" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm"></div>
    </div>
</section>

<section class="rounded-xl border bg-white p-6">
    <h2 class="font-semibold">Announcement bar</h2>
    <label class="mt-3 flex items-center gap-2 text-sm"><input type="checkbox" name="announcement_enabled" value="1" @checked(!empty($announcement['enabled']))> Enabled</label>
    <input name="announcement_text" value="{{ $announcement['text'] ?? '' }}" class="mt-2 w-full rounded-lg border px-3 py-2 text-sm" placeholder="Sale ends Friday…">
</section>

<section class="rounded-xl border bg-white p-6">
    <h2 class="font-semibold">Footer about (short)</h2>
    <p class="text-xs text-slate-500">Full footer columns: Settings → Header / footer</p>
    <textarea name="footer_about" rows="3" class="mt-3 w-full rounded-lg border px-3 py-2 text-sm">{{ $footer['about'] ?? '' }}</textarea>
</section>

<section class="rounded-xl border bg-white p-6">
    <h2 class="font-semibold">Colors</h2>
    <div class="mt-3 grid gap-3 sm:grid-cols-2">
        <div><label class="text-sm">Primary</label><input type="color" name="color_primary" value="{{ $colors['primary'] ?? '#059669' }}" class="mt-1 h-10 w-full"></div>
        <div><label class="text-sm">Secondary</label><input type="color" name="color_secondary" value="{{ $colors['secondary'] ?? '#0f172a' }}" class="mt-1 h-10 w-full"></div>
    </div>
</section>

<section class="rounded-xl border bg-white p-6">
    <h2 class="font-semibold">Default SEO</h2>
    <div class="mt-3 space-y-3">
        <input name="seo_title" value="{{ $seo['title'] ?? '' }}" class="w-full rounded-lg border px-3 py-2 text-sm" placeholder="Site title">
        <input name="seo_description" value="{{ $seo['description'] ?? '' }}" class="w-full rounded-lg border px-3 py-2 text-sm" placeholder="Meta description">
    </div>
</section>

<button class="rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white">Save general settings</button>
</form>
@endsection
