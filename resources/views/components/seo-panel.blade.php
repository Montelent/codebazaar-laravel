@php
  $m = $model ?? null;
  $prefix = $prefix ?? '';
  $val = function (string $key, $default = '') use ($m) {
      if (! $m) return old($key, $default);
      return old($key, $m->{$key} ?? $default);
  };
@endphp
<section class="rounded-xl border border-emerald-100 bg-gradient-to-b from-emerald-50/40 to-white p-5 shadow-sm" id="seo-panel">
  <div class="flex flex-wrap items-center justify-between gap-2">
    <div>
      <h2 class="text-base font-bold text-slate-900">SEO</h2>
      <p class="text-xs text-slate-500">Rank Math–style fields · title, meta, canonical, social &amp; robots</p>
    </div>
    <span class="rounded-full bg-emerald-100 px-2.5 py-0.5 text-[11px] font-semibold text-emerald-800">On-page SEO</span>
  </div>

  <div class="mt-4 grid gap-4 sm:grid-cols-2">
    <div class="sm:col-span-2">
      <label class="text-sm font-medium text-slate-700">Focus keyword</label>
      <input name="focus_keyword" value="{{ $val('focus_keyword') }}" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm" placeholder="Main keyword for this page">
    </div>

    <div class="sm:col-span-2">
      <div class="flex items-center justify-between">
        <label class="text-sm font-medium text-slate-700">SEO title</label>
        <span class="text-[11px] text-slate-400"><span id="seo-title-count">0</span>/60</span>
      </div>
      <input name="seo_title" id="seo_title" value="{{ $val('seo_title') }}" maxlength="200" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm" placeholder="Defaults to the content title">
      <p class="mt-1 text-[11px] text-slate-400">Ideal ~50–60 characters. Shown in Google results.</p>
    </div>

    <div class="sm:col-span-2">
      <div class="flex items-center justify-between">
        <label class="text-sm font-medium text-slate-700">Meta description</label>
        <span class="text-[11px] text-slate-400"><span id="seo-desc-count">0</span>/160</span>
      </div>
      <textarea name="seo_description" id="seo_description" rows="3" maxlength="320" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm" placeholder="Compelling summary for search results">{{ $val('seo_description') }}</textarea>
    </div>

    <div>
      <label class="text-sm font-medium text-slate-700">Keywords</label>
      <input name="seo_keywords" value="{{ $val('seo_keywords') }}" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm" placeholder="keyword1, keyword2">
    </div>

    <div>
      <label class="text-sm font-medium text-slate-700">Robots</label>
      <select name="robots" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
        @php $robots = $val('robots', ''); @endphp
        <option value="" @selected($robots==='' || $robots===null)>Default (index, follow)</option>
        <option value="index, follow" @selected($robots==='index, follow')>index, follow</option>
        <option value="noindex, follow" @selected($robots==='noindex, follow')>noindex, follow</option>
        <option value="index, nofollow" @selected($robots==='index, nofollow')>index, nofollow</option>
        <option value="noindex, nofollow" @selected($robots==='noindex, nofollow')>noindex, nofollow</option>
      </select>
    </div>

    <div class="sm:col-span-2">
      <label class="text-sm font-medium text-slate-700">Canonical URL</label>
      <input name="canonical_url" value="{{ $val('canonical_url') }}" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm" placeholder="Leave empty to use the page URL">
      <p class="mt-1 text-[11px] text-slate-400">Use only if this content should point to a different URL.</p>
    </div>
  </div>

  <div class="mt-6 border-t border-slate-100 pt-4">
    <h3 class="text-sm font-semibold text-slate-800">Social / Open Graph</h3>
    <div class="mt-3 grid gap-4 sm:grid-cols-2">
      <div class="sm:col-span-2">
        <label class="text-sm font-medium text-slate-700">OG title</label>
        <input name="og_title" value="{{ $val('og_title') }}" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm" placeholder="Defaults to SEO title">
      </div>
      <div class="sm:col-span-2">
        <label class="text-sm font-medium text-slate-700">OG description</label>
        <textarea name="og_description" rows="2" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm" placeholder="Defaults to meta description">{{ $val('og_description') }}</textarea>
      </div>
      <div class="sm:col-span-2">
        <label class="text-sm font-medium text-slate-700">OG image URL</label>
        <input name="og_image" value="{{ $val('og_image') }}" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm" placeholder="https://… (defaults to thumbnail / site logo)">
      </div>
    </div>
  </div>

  <div class="mt-6 rounded-lg border border-slate-200 bg-white p-4">
    <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">Google preview</p>
    <p class="mt-2 text-lg text-[#1a0dab]" id="seo-preview-title">Title preview</p>
    <p class="text-sm text-[#006621]" id="seo-preview-url">{{ url('/') }}</p>
    <p class="mt-1 text-sm text-slate-600" id="seo-preview-desc">Description preview</p>
  </div>
</section>

@once
@push('scripts')
<script>
(function(){
  const title = document.getElementById('seo_title');
  const desc = document.getElementById('seo_description');
  const tc = document.getElementById('seo-title-count');
  const dc = document.getElementById('seo-desc-count');
  const pt = document.getElementById('seo-preview-title');
  const pd = document.getElementById('seo-preview-desc');
  function sync(){
    if (title && tc) tc.textContent = (title.value || '').length;
    if (desc && dc) dc.textContent = (desc.value || '').length;
    if (pt) pt.textContent = (title && title.value) ? title.value : 'Title preview';
    if (pd) pd.textContent = (desc && desc.value) ? desc.value : 'Description preview';
  }
  if (title) title.addEventListener('input', sync);
  if (desc) desc.addEventListener('input', sync);
  sync();
})();
</script>
@endpush
@endonce
