@extends('layouts.admin')
@section('title', 'Header & footer')
@section('content')
<a href="{{ route('admin.settings.hub') }}" class="text-sm text-emerald-700">← Settings</a>
<h1 class="mt-2 text-xl font-bold">Header & footer</h1>

<form method="post" action="{{ route('admin.settings.header_footer.update') }}" class="mt-6 space-y-6">
  @csrf @method('PUT')
  <input type="hidden" name="header_json" id="header_json">
  <input type="hidden" name="footer_json" id="footer_json">

  <section class="rounded-xl border bg-white p-6">
    <h2 class="font-semibold">Header</h2>
    <label class="mt-3 flex items-center gap-2 text-sm"><input type="checkbox" id="show_search" @checked(!empty($header['show_search']))> Show search</label>
    <div class="mt-3 grid gap-3 sm:grid-cols-2">
      <div><label class="text-sm">CTA label</label><input id="cta_label" value="{{ $header['cta_label'] ?? 'Join free' }}" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm"></div>
      <div><label class="text-sm">CTA URL</label><input id="cta_url" value="{{ $header['cta_url'] ?? '/register' }}" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm"></div>
    </div>
  </section>

  <section class="rounded-xl border bg-white p-6">
    <h2 class="font-semibold">Footer about</h2>
    <textarea id="footer_about" rows="3" class="mt-3 w-full rounded-lg border px-3 py-2 text-sm">{{ $footer['about'] ?? '' }}</textarea>
    <h3 class="mt-4 text-sm font-medium">Social URLs</h3>
    <div class="mt-2 grid gap-2 sm:grid-cols-3">
      <input id="soc_twitter" placeholder="Twitter/X URL" value="{{ $footer['social']['twitter'] ?? '' }}" class="rounded-lg border px-3 py-2 text-sm">
      <input id="soc_facebook" placeholder="Facebook URL" value="{{ $footer['social']['facebook'] ?? '' }}" class="rounded-lg border px-3 py-2 text-sm">
      <input id="soc_github" placeholder="GitHub URL" value="{{ $footer['social']['github'] ?? '' }}" class="rounded-lg border px-3 py-2 text-sm">
    </div>
    <h3 class="mt-4 text-sm font-medium">Footer columns JSON</h3>
    <textarea id="footer_columns" rows="10" class="mt-2 w-full rounded-lg border px-3 py-2 font-mono text-xs">{{ json_encode($footer['columns'] ?? [], JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES) }}</textarea>
    <p class="mt-1 text-xs text-slate-500">Array of { "title": "…", "links": [ {"label":"…","url":"…"} ] }</p>
  </section>

  <section class="rounded-xl border bg-white p-6 space-y-4">
    <div>
      <h2 class="font-semibold">Verification &amp; tracking codes</h2>
      <p class="mt-1 text-sm text-slate-500">Google Search Console, Bing, Facebook domain verification, Analytics, Tag Manager, AdSense site verification, etc. Output unescaped — paste only trusted code.</p>
    </div>
    <div>
      <label class="text-sm font-medium">&lt;head&gt; codes</label>
      <p class="text-xs text-slate-500">Meta tags, verification tags, GTM head snippet, AdSense account verification.</p>
      <textarea name="code_head" rows="5" class="mt-1 w-full rounded-lg border px-3 py-2 font-mono text-xs" placeholder="&lt;meta name=&quot;google-site-verification&quot; content=&quot;…&quot;&gt;">{{ $codes['head'] ?? '' }}</textarea>
    </div>
    <div>
      <label class="text-sm font-medium">After &lt;body&gt; open</label>
      <p class="text-xs text-slate-500">GTM noscript, body-start pixels.</p>
      <textarea name="code_body_start" rows="4" class="mt-1 w-full rounded-lg border px-3 py-2 font-mono text-xs">{{ $codes['body_start'] ?? '' }}</textarea>
    </div>
    <div>
      <label class="text-sm font-medium">Before &lt;/body&gt;</label>
      <p class="text-xs text-slate-500">Chat widgets, deferred analytics, footer scripts.</p>
      <textarea name="code_body_end" rows="4" class="mt-1 w-full rounded-lg border px-3 py-2 font-mono text-xs">{{ $codes['body_end'] ?? '' }}</textarea>
    </div>
    <p class="text-xs text-slate-500">For page-level ads (blog, product, footer banners), use <a href="{{ route('admin.settings.ads') }}" class="text-emerald-700">Settings → Ad placements</a>.</p>
  </section>

  <button class="rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white">Save</button>
</form>

@push('scripts')
<script>
function syncHF(){
  let cols = [];
  try { cols = JSON.parse(document.getElementById('footer_columns').value || '[]'); } catch(e) { alert('Invalid columns JSON'); throw e; }
  document.getElementById('header_json').value = JSON.stringify({
    show_search: document.getElementById('show_search').checked,
    cta_label: document.getElementById('cta_label').value,
    cta_url: document.getElementById('cta_url').value
  });
  document.getElementById('footer_json').value = JSON.stringify({
    about: document.getElementById('footer_about').value,
    social: {
      twitter: document.getElementById('soc_twitter').value,
      facebook: document.getElementById('soc_facebook').value,
      github: document.getElementById('soc_github').value
    },
    columns: cols
  });
}
document.querySelector('form').addEventListener('submit', syncHF);
</script>
@endpush
@endsection
