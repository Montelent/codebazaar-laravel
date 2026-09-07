<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('app.name', 'CodeBazaar'))</title>
    <meta name="description" content="@yield('meta_description', 'Digital marketplace for code, scripts, themes and plugins.')">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
      tailwind.config = {
        theme: {
          extend: {
            colors: { envato: { green: '#82b440', dark: '#262626', muted: '#7a7a7a' } },
            fontFamily: { sans: ['Inter', 'system-ui', '-apple-system', 'Segoe UI', 'Roboto', 'sans-serif'] }
          }
        }
      }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
      :root { --cc-green: #82b440; --cc-green-hover: #6f9a36; --cc-border: #e5e7eb; --cc-bg: #f5f7fa; --cc-text: #333; --cc-header-h: 56px; }
      * { box-sizing: border-box; }
      body { font-family: Inter, system-ui, sans-serif; background: var(--cc-bg); color: var(--cc-text); margin: 0; }
      a { color: inherit; }
      .cc-container { width: 100%; max-width: 1200px; margin: 0 auto; padding: 0 16px; }
      @media (min-width: 640px) { .cc-container { padding: 0 20px; } }
      @media (min-width: 1024px) { .cc-container { padding: 0 24px; } }
      .cc-topbar { background: #fff; border-bottom: 1px solid #e8e8e8; }
      .cc-topbar-inner { display: flex; align-items: center; gap: 16px; min-height: var(--cc-header-h); padding-top: 10px; padding-bottom: 10px; }
      .cc-logo { display: inline-flex; align-items: center; gap: 8px; text-decoration: none; font-weight: 800; font-size: 1.15rem; color: #1a1a1a; white-space: nowrap; flex-shrink: 0; }
      .cc-logo-mark { width: 28px; height: 28px; border-radius: 4px; background: var(--cc-green); color: #fff; display: inline-flex; align-items: center; justify-content: center; font-size: 12px; }
      .cc-header-search { flex: 1; max-width: 520px; position: relative; }
      .cc-header-search input { width: 100%; height: 40px; border: 1px solid #d0d0d0; border-radius: 4px; padding: 0 40px 0 14px; font-size: 14px; background: #fff; }
      .cc-header-search input:focus { outline: none; border-color: var(--cc-green); box-shadow: 0 0 0 2px rgba(130,180,64,.2); }
      .cc-header-search button { position: absolute; right: 0; top: 0; bottom: 0; width: 42px; border: 0; background: transparent; color: #666; cursor: pointer; }
      .cc-header-actions { display: flex; align-items: center; gap: 14px; margin-left: auto; flex-shrink: 0; }
      .cc-header-actions a, .cc-header-actions button { font-size: 13px; font-weight: 500; color: #444; text-decoration: none; background: none; border: 0; cursor: pointer; padding: 0; }
      .cc-header-actions a:hover { color: var(--cc-green); }
      .cc-btn-cart { display: inline-flex; align-items: center; gap: 6px; border: 1px solid #ddd !important; border-radius: 4px; padding: 7px 12px !important; font-weight: 600 !important; }
      .cc-btn-green { background: var(--cc-green) !important; color: #fff !important; border-radius: 4px; padding: 8px 14px !important; font-weight: 600 !important; font-size: 13px !important; text-decoration: none; }
      .cc-btn-green:hover { background: var(--cc-green-hover) !important; color: #fff !important; }
      .cc-subnav { background: #fff; border-bottom: 1px solid #e8e8e8; }
      .cc-subnav-inner { display: flex; align-items: center; gap: 4px; overflow-x: auto; min-height: 42px; scrollbar-width: none; }
      .cc-subnav-inner::-webkit-scrollbar { display: none; }
      .cc-subnav a { flex-shrink: 0; padding: 10px 12px; font-size: 13px; font-weight: 500; color: #555; text-decoration: none; white-space: nowrap; }
      .cc-subnav a:hover { color: var(--cc-green); }
      .cc-menu-btn { display: none; width: 40px; height: 40px; border: 1px solid #e5e7eb; border-radius: 4px; background: #fff; align-items: center; justify-content: center; cursor: pointer; }
      @media (max-width: 767px) {
        .cc-header-search { order: 3; max-width: none; width: 100%; }
        .cc-topbar-inner { flex-wrap: wrap; }
        .cc-menu-btn { display: inline-flex; }
        .cc-hide-mobile { display: none !important; }
      }
      .cc-drawer { position: fixed; inset: 0; z-index: 60; display: none; }
      .cc-drawer.is-open { display: block; }
      .cc-drawer-backdrop { position: absolute; inset: 0; background: rgba(0,0,0,.45); }
      .cc-drawer-panel { position: absolute; top: 0; right: 0; bottom: 0; width: min(300px, 88vw); background: #fff; box-shadow: -8px 0 24px rgba(0,0,0,.12); padding: 16px; overflow-y: auto; }
      .cc-drawer-panel a { display: block; padding: 12px 8px; color: #1e293b; text-decoration: none; font-weight: 500; border-bottom: 1px solid #f1f5f9; font-size: 14px; }
      .cc-footer { background: #1a1a1a; color: #b0b0b0; margin-top: 56px; }
      .cc-footer a { color: inherit; text-decoration: none; }
      .cc-footer a:hover { color: #fff; }
      main.cc-main { padding: 24px 0 48px; min-height: 50vh; }
      .cc-grid { display: grid; gap: 16px; grid-template-columns: repeat(1, minmax(0, 1fr)); }
      @media (min-width: 480px) { .cc-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
      @media (min-width: 768px) { .cc-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 18px; } }
      @media (min-width: 1100px) { .cc-grid { grid-template-columns: repeat(4, minmax(0, 1fr)); } }
      @media (min-width: 1024px) { .cc-buy-box { position: sticky; top: 72px; } }
      .item-body { font-size: 15px; line-height: 1.7; color: #333; }
      .item-body h1, .item-body h2, .item-body h3, .item-body h4 { font-weight: 700; color: #111; margin: 1.25em 0 0.5em; line-height: 1.3; }
      .item-body h1 { font-size: 1.75rem; } .item-body h2 { font-size: 1.4rem; } .item-body h3 { font-size: 1.2rem; }
      .item-body p { margin: 0 0 1em; }
      .item-body ul, .item-body ol { margin: 0 0 1em; padding-left: 1.5em; }
      .item-body ul { list-style: disc; } .item-body ol { list-style: decimal; }
      .item-body a { color: var(--cc-green); text-decoration: underline; }
      .item-body strong, .item-body b { font-weight: 700; }
      .item-body img { max-width: 100%; height: auto; border-radius: 4px; margin: 1em 0; }
      .item-body blockquote { border-left: 3px solid var(--cc-green); margin: 1em 0; padding: 0.5em 1em; color: #555; background: #f8faf8; }
      .item-body table { width: 100%; border-collapse: collapse; margin: 1em 0; font-size: 14px; }
      .item-body th, .item-body td { border: 1px solid #e5e7eb; padding: 8px 10px; text-align: left; }
      .item-body th { background: #f5f5f5; font-weight: 600; }
      .item-body pre { background: #1e1e1e; color: #f1f1f1; padding: 12px 14px; border-radius: 6px; overflow-x: auto; margin: 1em 0; font-family: ui-monospace, monospace; font-size: 13px; }
      .item-body code { background: #f3f4f6; padding: 1px 5px; border-radius: 3px; font-family: ui-monospace, monospace; font-size: 13px; }
      .item-body pre code { background: transparent; padding: 0; color: inherit; }
      .cc-ad-slot { text-align: center; overflow: hidden; }
    </style>
    @stack('head')
    @php $siteCodes = \App\Models\SiteSetting::getValue('site_codes', ['head' => '', 'body_start' => '', 'body_end' => '']); @endphp
    {!! $siteCodes['head'] ?? '' !!}
</head>
<body>
{!! $siteCodes['body_start'] ?? '' !!}
@php
    $ann = \App\Models\SiteSetting::getValue('announcement', ['enabled' => false, 'text' => '']);
    $nav = \App\Models\SiteSetting::getValue('nav_header', [
        ['label' => 'All items', 'url' => '/search', 'open_new' => false],
        ['label' => 'Blog', 'url' => '/blog', 'open_new' => false],
        ['label' => 'Licenses', 'url' => '/pricing/licenses', 'open_new' => false],
    ]);
    $footer = \App\Models\SiteSetting::getValue('footer', ['about' => 'The marketplace for high-quality code, scripts, plugins, and digital assets.', 'columns' => [], 'social' => []]);
    try {
        $navCategories = \App\Models\Category::query()->whereNull('parent_id')->orderBy('name')->take(12)->get();
    } catch (\Throwable $e) {
        $navCategories = collect();
    }
@endphp
@if(!empty($ann['enabled']) && !empty($ann['text']))
<div style="background:#2c3e50;color:#fff;font-size:13px;text-align:center;padding:8px 12px">{{ $ann['text'] }}</div>
@endif

<header class="cc-topbar sticky top-0 z-40">
    <div class="cc-container cc-topbar-inner">
        <a href="{{ route('home') }}" class="cc-logo"><span class="cc-logo-mark">◆</span> CodeBazaar</a>
        <form action="{{ route('search') }}" method="get" class="cc-header-search" role="search">
            <input type="search" name="q" value="{{ request('q') }}" placeholder="Search for items…" autocomplete="off">
            <button type="submit" aria-label="Search"><svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M11 18a7 7 0 100-14 7 7 0 000 14z"/></svg></button>
        </form>
        <div class="cc-header-actions">
            <a href="{{ route('cart.index') }}" class="cc-btn-cart">Cart</a>
            @auth
                @if(auth()->user()->isAdmin())<a href="{{ route('admin.dashboard') }}" class="cc-hide-mobile">Admin</a>@endif
                <a href="{{ route('account.index') }}" class="cc-hide-mobile">{{ auth()->user()->name ?: 'Account' }}</a>
                <form method="post" action="{{ route('logout') }}" class="cc-hide-mobile">@csrf<button type="submit">Logout</button></form>
            @else
                <a href="{{ route('login') }}" class="cc-hide-mobile">Sign in</a>
                <a href="{{ route('register') }}" class="cc-btn-green cc-hide-mobile">Create account</a>
            @endauth
            <button type="button" class="cc-menu-btn" id="cc-menu-open" aria-label="Menu"><svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg></button>
        </div>
    </div>
    <div class="cc-subnav">
        <div class="cc-container cc-subnav-inner">
            <a href="{{ route('search') }}">All Items</a>
            @foreach($navCategories as $cat)
                <a href="{{ route('category', $cat->slug) }}">{{ $cat->name }}</a>
            @endforeach
            @foreach($nav as $link)
                @php $href = $link['url'] ?? '#'; @endphp
                <a href="{{ $href }}" @if(!empty($link['open_new'])) target="_blank" rel="noopener" @endif>{{ $link['label'] ?? '' }}</a>
            @endforeach
        </div>
    </div>
</header>

{!! \App\Support\AdSlots::render('sitewide_after_header') !!}

<div class="cc-drawer" id="cc-drawer" aria-hidden="true">
    <div class="cc-drawer-backdrop" id="cc-drawer-backdrop"></div>
    <div class="cc-drawer-panel">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">
            <strong>Menu</strong>
            <button type="button" class="cc-menu-btn" id="cc-menu-close" aria-label="Close">✕</button>
        </div>
        <a href="{{ route('search') }}">All Items</a>
        @foreach($navCategories as $cat)<a href="{{ route('category', $cat->slug) }}">{{ $cat->name }}</a>@endforeach
        @foreach($nav as $link)<a href="{{ $link['url'] ?? '#' }}">{{ $link['label'] ?? '' }}</a>@endforeach
        <a href="{{ route('cart.index') }}">Cart</a>
        @auth
            <a href="{{ route('account.index') }}">Account</a>
            @if(auth()->user()->isAdmin())<a href="{{ route('admin.dashboard') }}">Admin</a>@endif
            <form method="post" action="{{ route('logout') }}">@csrf<button type="submit" style="width:100%;text-align:left;padding:12px 8px;border:0;border-bottom:1px solid #f1f5f9;background:none;font-weight:500">Logout</button></form>
        @else
            <a href="{{ route('login') }}">Sign in</a>
            <a href="{{ route('register') }}">Create account</a>
        @endauth
    </div>
</div>

@if(session('success'))
<div class="cc-container" style="padding-top:16px"><div class="rounded border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm text-emerald-900">{{ session('success') }}</div></div>
@endif
@if(session('error'))
<div class="cc-container" style="padding-top:16px"><div class="rounded border border-red-200 bg-red-50 px-4 py-2 text-sm text-red-900">{{ session('error') }}</div></div>
@endif

<main class="cc-main"><div class="cc-container">@yield('content')</div></main>

{!! \App\Support\AdSlots::render('sitewide_before_footer') !!}

<footer class="cc-footer">
    {!! \App\Support\AdSlots::render('footer_top') !!}
    <div class="cc-container grid gap-8 py-12 sm:grid-cols-2 lg:grid-cols-4">
        <div class="sm:col-span-2">
            <p class="text-lg font-semibold text-white">CodeBazaar</p>
            <p class="mt-2 max-w-md text-sm text-[#9a9a9a]">{{ $footer['about'] ?? '' }}</p>
        </div>
        <div>
            <p class="text-sm font-semibold text-white">Explore</p>
            <ul class="mt-3 space-y-2 text-sm">
                <li><a href="{{ route('search') }}">All items</a></li>
                <li><a href="{{ route('blog.index') }}">Blog</a></li>
                <li><a href="{{ route('licenses.public') }}">Licenses</a></li>
            </ul>
        </div>
        <div>
            <p class="text-sm font-semibold text-white">Account</p>
            <ul class="mt-3 space-y-2 text-sm">
                <li><a href="{{ route('login') }}">Sign in</a></li>
                <li><a href="{{ route('register') }}">Create account</a></li>
                <li><a href="{{ route('cart.index') }}">Cart</a></li>
            </ul>
        </div>
    </div>
    {!! \App\Support\AdSlots::render('footer_bottom') !!}
    <div class="border-t border-[#2a2a2a] py-4 text-center text-xs text-[#777]">&copy; {{ date('Y') }} CodeBazaar</div>
</footer>
<script>
(function () {
  var drawer = document.getElementById('cc-drawer');
  var openBtn = document.getElementById('cc-menu-open');
  var closeBtn = document.getElementById('cc-menu-close');
  var backdrop = document.getElementById('cc-drawer-backdrop');
  function open() { if (drawer) { drawer.classList.add('is-open'); document.body.style.overflow = 'hidden'; } }
  function close() { if (drawer) { drawer.classList.remove('is-open'); document.body.style.overflow = ''; } }
  if (openBtn) openBtn.addEventListener('click', open);
  if (closeBtn) closeBtn.addEventListener('click', close);
  if (backdrop) backdrop.addEventListener('click', close);
})();
</script>
@stack('scripts')
{!! $siteCodes['body_end'] ?? '' !!}
</body>
</html>
