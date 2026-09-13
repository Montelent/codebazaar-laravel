@extends('layouts.admin')
@section('title', 'General settings')
@section('content')
<a href="{{ route('admin.settings.hub') }}" class="text-sm text-emerald-700">← Settings hub</a>
<form method="post" action="{{ route('admin.settings.general.update') }}" class="mx-auto mt-4 max-w-3xl space-y-8">
@csrf @method('PUT')

<section class="rounded-xl border bg-white p-4 sm:p-6">
    <h2 class="font-semibold">Homepage hero</h2>
    <p class="mt-1 text-xs text-slate-500">Matches the dark hero with accent headline + search bar.</p>
    <div class="mt-4 space-y-3">
        <div><label class="text-sm">Eyebrow (small brand line)</label><input name="hero_eyebrow" value="{{ $hero['eyebrow'] ?? 'CodeBazaar' }}" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm"></div>
        <div><label class="text-sm">Title (white line)</label><input name="hero_title" value="{{ $hero['title'] ?? '' }}" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm" placeholder="Code that powers"></div>
        <div><label class="text-sm">Title highlight (accent color)</label><input name="hero_title_highlight" value="{{ $hero['title_highlight'] ?? '' }}" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm" placeholder="your next development"></div>
        <div><label class="text-sm">Subtitle</label><textarea name="hero_subtitle" rows="2" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm">{{ $hero['subtitle'] ?? '' }}</textarea></div>
        <div><label class="text-sm">Search button label</label><input name="hero_cta" value="{{ $hero['cta'] ?? 'Search' }}" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm"></div>
        <div><label class="text-sm">Background image URL (optional)</label><input name="hero_image" value="{{ $hero['image'] ?? '' }}" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm"></div>
    </div>
</section>

<section class="rounded-xl border bg-white p-4 sm:p-6">
    <div class="flex flex-wrap items-center justify-between gap-2">
        <div>
            <h2 class="font-semibold">Browse by category</h2>
            <p class="mt-1 text-xs text-slate-500">Cards on the homepage. Icon can be an emoji or image URL. Leave title empty to remove a row on save.</p>
        </div>
        <button type="button" id="add-cat-row" class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-sm font-medium text-emerald-800">+ Add card</button>
    </div>
    <div id="cat-rows" class="mt-4 space-y-3">
        @php
          $rows = is_array($browseCategories ?? null) ? $browseCategories : [];
          if (count($rows) === 0) {
            $rows = [['icon'=>'','title'=>'','subtitle'=>'','url'=>'']];
          }
        @endphp
        @foreach($rows as $i => $row)
        <div class="cat-row grid gap-2 rounded-lg border border-slate-100 bg-slate-50/80 p-3 sm:grid-cols-12">
            <div class="sm:col-span-2">
                <label class="text-[11px] font-medium text-slate-500">Icon</label>
                <input name="cat_icon[]" value="{{ $row['icon'] ?? '' }}" class="mt-1 w-full rounded-lg border px-2 py-2 text-sm" placeholder="🟦 or URL">
            </div>
            <div class="sm:col-span-3">
                <label class="text-[11px] font-medium text-slate-500">Title</label>
                <input name="cat_title[]" value="{{ $row['title'] ?? '' }}" class="mt-1 w-full rounded-lg border px-2 py-2 text-sm" placeholder="WordPress">
            </div>
            <div class="sm:col-span-4">
                <label class="text-[11px] font-medium text-slate-500">Subtitle</label>
                <input name="cat_subtitle[]" value="{{ $row['subtitle'] ?? '' }}" class="mt-1 w-full rounded-lg border px-2 py-2 text-sm" placeholder="Themes, plugins…">
            </div>
            <div class="sm:col-span-3">
                <label class="text-[11px] font-medium text-slate-500">Link URL</label>
                <input name="cat_url[]" value="{{ $row['url'] ?? '' }}" class="mt-1 w-full rounded-lg border px-2 py-2 text-sm" placeholder="/category/wordpress">
            </div>
        </div>
        @endforeach
    </div>
</section>

<section class="rounded-xl border bg-white p-4 sm:p-6">
    <h2 class="font-semibold">Announcement bar</h2>
    <label class="mt-3 flex items-center gap-2 text-sm"><input type="checkbox" name="announcement_enabled" value="1" @checked(!empty($announcement['enabled']))> Enabled</label>
    <input name="announcement_text" value="{{ $announcement['text'] ?? '' }}" class="mt-2 w-full rounded-lg border px-3 py-2 text-sm" placeholder="Sale ends Friday…">
</section>

<section class="rounded-xl border bg-white p-4 sm:p-6">
    <h2 class="font-semibold">Footer about (short)</h2>
    <p class="text-xs text-slate-500">Full footer columns: Settings → Header / footer</p>
    <textarea name="footer_about" rows="3" class="mt-3 w-full rounded-lg border px-3 py-2 text-sm">{{ $footer['about'] ?? '' }}</textarea>
</section>

<section class="rounded-xl border bg-white p-4 sm:p-6">
    <h2 class="font-semibold">Storefront colors</h2>
    <p class="mt-1 text-xs text-slate-500">Primary drives the accent (hero highlight + Search button). Secondary is the dark hero background.</p>
    <div class="mt-4 grid gap-4 sm:grid-cols-2">
        <div>
            <label class="text-sm font-medium">Primary (accent)</label>
            <div class="mt-1 flex items-center gap-2">
                <input type="color" name="color_primary" value="{{ $colors['primary'] ?? '#e11d2e' }}" class="h-10 w-14 cursor-pointer rounded border p-0.5">
                <input type="text" value="{{ $colors['primary'] ?? '#e11d2e' }}" class="w-full rounded-lg border px-2 py-2 font-mono text-xs" oninput="this.previousElementSibling.value=this.value">
            </div>
        </div>
        <div>
            <label class="text-sm font-medium">Primary hover</label>
            <div class="mt-1 flex items-center gap-2">
                <input type="color" name="color_primary_hover" value="{{ $colors['primary_hover'] ?? '#c1121f' }}" class="h-10 w-14 cursor-pointer rounded border p-0.5">
                <input type="text" value="{{ $colors['primary_hover'] ?? '#c1121f' }}" class="w-full rounded-lg border px-2 py-2 font-mono text-xs" oninput="this.previousElementSibling.value=this.value">
            </div>
        </div>
        <div>
            <label class="text-sm font-medium">Secondary (hero background)</label>
            <div class="mt-1 flex items-center gap-2">
                <input type="color" name="color_secondary" value="{{ $colors['secondary'] ?? '#0b1220' }}" class="h-10 w-14 cursor-pointer rounded border p-0.5">
                <input type="text" value="{{ $colors['secondary'] ?? '#0b1220' }}" class="w-full rounded-lg border px-2 py-2 font-mono text-xs" oninput="this.previousElementSibling.value=this.value">
            </div>
        </div>
        <div>
            <label class="text-sm font-medium">Header background</label>
            <div class="mt-1 flex items-center gap-2">
                <input type="color" name="color_header_bg" value="{{ $colors['header_bg'] ?? '#ffffff' }}" class="h-10 w-14 cursor-pointer rounded border p-0.5">
                <input type="text" value="{{ $colors['header_bg'] ?? '#ffffff' }}" class="w-full rounded-lg border px-2 py-2 font-mono text-xs" oninput="this.previousElementSibling.value=this.value">
            </div>
        </div>
        <div>
            <label class="text-sm font-medium">Footer background</label>
            <div class="mt-1 flex items-center gap-2">
                <input type="color" name="color_footer_bg" value="{{ $colors['footer_bg'] ?? '#0b1220' }}" class="h-10 w-14 cursor-pointer rounded border p-0.5">
                <input type="text" value="{{ $colors['footer_text'] ?? '#0b1220' }}" class="w-full rounded-lg border px-2 py-2 font-mono text-xs" oninput="this.previousElementSibling.value=this.value">
            </div>
        </div>
        <div>
            <label class="text-sm font-medium">Footer text</label>
            <div class="mt-1 flex items-center gap-2">
                <input type="color" name="color_footer_text" value="{{ $colors['footer_text'] ?? '#94a3b8' }}" class="h-10 w-14 cursor-pointer rounded border p-0.5">
                <input type="text" value="{{ $colors['footer_text'] ?? '#94a3b8' }}" class="w-full rounded-lg border px-2 py-2 font-mono text-xs" oninput="this.previousElementSibling.value=this.value">
            </div>
        </div>
        <div>
            <label class="text-sm font-medium">Announcement bar background</label>
            <div class="mt-1 flex items-center gap-2">
                <input type="color" name="color_announcement_bg" value="{{ $colors['announcement_bg'] ?? '#0b1220' }}" class="h-10 w-14 cursor-pointer rounded border p-0.5">
                <input type="text" value="{{ $colors['announcement_bg'] ?? '#0b1220' }}" class="w-full rounded-lg border px-2 py-2 font-mono text-xs" oninput="this.previousElementSibling.value=this.value">
            </div>
        </div>
    </div>
</section>

<section class="rounded-xl border bg-white p-4 sm:p-6">
    <h2 class="font-semibold">Default SEO</h2>
    <div class="mt-3 space-y-3">
        <input name="seo_title" value="{{ $seo['title'] ?? '' }}" class="w-full rounded-lg border px-3 py-2 text-sm" placeholder="Site title">
        <input name="seo_description" value="{{ $seo['description'] ?? '' }}" class="w-full rounded-lg border px-3 py-2 text-sm" placeholder="Meta description">
    </div>
</section>

<button class="rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white">Save general settings</button>
</form>

@push('scripts')
<script>
(function () {
  var wrap = document.getElementById('cat-rows');
  var btn = document.getElementById('add-cat-row');
  if (!wrap || !btn) return;
  btn.addEventListener('click', function () {
    var div = document.createElement('div');
    div.className = 'cat-row grid gap-2 rounded-lg border border-slate-100 bg-slate-50/80 p-3 sm:grid-cols-12';
    div.innerHTML =
      '<div class="sm:col-span-2"><label class="text-[11px] font-medium text-slate-500">Icon</label><input name="cat_icon[]" class="mt-1 w-full rounded-lg border px-2 py-2 text-sm" placeholder="icon"></div>' +
      '<div class="sm:col-span-3"><label class="text-[11px] font-medium text-slate-500">Title</label><input name="cat_title[]" class="mt-1 w-full rounded-lg border px-2 py-2 text-sm" placeholder="WordPress"></div>' +
      '<div class="sm:col-span-4"><label class="text-[11px] font-medium text-slate-500">Subtitle</label><input name="cat_subtitle[]" class="mt-1 w-full rounded-lg border px-2 py-2 text-sm" placeholder="Themes, plugins"></div>' +
      '<div class="sm:col-span-3"><label class="text-[11px] font-medium text-slate-500">Link URL</label><input name="cat_url[]" class="mt-1 w-full rounded-lg border px-2 py-2 text-sm" placeholder="/category/wordpress"></div>';
    wrap.appendChild(div);
  });
})();
</script>
@endpush
@endsection
