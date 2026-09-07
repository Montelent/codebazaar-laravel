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
            colors: {
              envato: { green: '#82b440', dark: '#262626', muted: '#7a7a7a' }
            },
            fontFamily: {
              sans: ['Inter', 'system-ui', '-apple-system', 'Segoe UI', 'Roboto', 'sans-serif']
            }
          }
        }
      }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
      :root {
        --cc-green: #82b440;
        --cc-green-hover: #6f9a36;
        --cc-border: #e5e7eb;
        --cc-bg: #f5f7fa;
        --cc-text: #1e1e1e;
      }
      * { box-sizing: border-box; }
      body { font-family: Inter, system-ui, sans-serif; background: var(--cc-bg); color: var(--cc-text); margin: 0; }
      .cc-container { width: 100%; max-width: 1200px; margin-left: auto; margin-right: auto; padding-left: 16px; padding-right: 16px; }
      @media (min-width: 640px) { .cc-container { padding-left: 20px; padding-right: 20px; } }
      @media (min-width: 1024px) { .cc-container { padding-left: 24px; padding-right: 24px; } }

      .cc-header {
        background: #fff;
        border-bottom: 1px solid var(--cc-border);
        box-shadow: 0 1px 0 rgba(0,0,0,.04);
      }
      .cc-logo {
        font-weight: 800;
        letter-spacing: -0.02em;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        text-decoration: none;
        color: #0f172a;
        font-size: 1.125rem;
        white-space: nowrap;
      }
      .cc-logo-mark {
        display: inline-flex;
        height: 32px;
        width: 32px;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
        background: var(--cc-green);
        color: #fff;
        font-size: 13px;
        flex-shrink: 0;
      }

      .cc-search-wrap { position: relative; width: 100%; }
      .cc-search {
        display: block;
        width: 100%;
        height: 44px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        background: #fff;
        padding: 0 14px 0 40px;
        font-size: 15px;
        color: #111;
        appearance: none;
        -webkit-appearance: none;
      }
      .cc-search::placeholder { color: #9ca3af; }
      .cc-search:focus {
        outline: none;
        border-color: var(--cc-green);
        box-shadow: 0 0 0 3px rgba(130,180,64,.25);
      }
      .cc-search-icon {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        width: 18px;
        height: 18px;
        color: #9ca3af;
        pointer-events: none;
      }

      .cc-btn-primary {
        background: var(--cc-green);
        color: #fff;
        font-weight: 600;
        border-radius: 8px;
        padding: 10px 16px;
        font-size: 14px;
        white-space: nowrap;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: none;
        cursor: pointer;
      }
      .cc-btn-primary:hover { background: var(--cc-green-hover); }
      .cc-nav-link {
        color: #444;
        font-size: 14px;
        font-weight: 500;
        text-decoration: none;
        padding: 6px 4px;
      }
      .cc-nav-link:hover { color: var(--cc-green); }

      .cc-announcement {
        background: #2c3e50;
        color: #fff;
        font-size: 13px;
        text-align: center;
        padding: 8px 12px;
      }

      .cc-footer {
        background: #1a1a1a;
        color: #b0b0b0;
        margin-top: 64px;
      }
      .cc-footer a { color: inherit; text-decoration: none; }
      .cc-footer a:hover { color: #fff; }

      .cc-header-inner {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding-top: 12px;
        padding-bottom: 12px;
      }
      .cc-header-search-desktop {
        display: none;
        flex: 1;
        max-width: 28rem;
        margin: 0 12px;
      }
      .cc-desktop-nav {
        display: none;
        flex-wrap: wrap;
        align-items: center;
        gap: 12px 16px;
      }
      .cc-mobile-actions {
        display: flex;
        align-items: center;
        gap: 8px;
      }
      .cc-mobile-search {
        display: block;
        width: 100%;
        order: 3;
      }
      .cc-menu-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        background: #fff;
        color: #334155;
        cursor: pointer;
        padding: 0;
      }

      /* Mobile drawer */
      .cc-drawer {
        position: fixed;
        inset: 0;
        z-index: 60;
        display: none;
      }
      .cc-drawer.is-open { display: block; }
      .cc-drawer-backdrop {
        position: absolute;
        inset: 0;
        background: rgba(0,0,0,.45);
      }
      .cc-drawer-panel {
        position: absolute;
        top: 0;
        right: 0;
        bottom: 0;
        width: min(300px, 88vw);
        background: #fff;
        box-shadow: -8px 0 24px rgba(0,0,0,.12);
        display: flex;
        flex-direction: column;
        padding: 16px;
        overflow-y: auto;
      }
      .cc-drawer-panel a {
        display: block;
        padding: 12px 8px;
        color: #1e293b;
        text-decoration: none;
        font-weight: 500;
        border-bottom: 1px solid #f1f5f9;
      }
      .cc-drawer-panel a:hover { color: var(--cc-green); }

      @media (min-width: 768px) {
        .cc-header-inner { flex-wrap: nowrap; gap: 16px; }
        .cc-header-search-desktop { display: block; }
        .cc-desktop-nav { display: flex; }
        .cc-mobile-actions { display: none; }
        .cc-mobile-search { display: none; }
        .cc-menu-btn { display: none; }
      }

      @media (min-width: 1024px) {
        .cc-buy-box { position: sticky; top: 88px; }
        .cc-header-search-desktop { max-width: 32rem; }
      }

      main.cc-main { padding-top: 20px; padding-bottom: 48px; min-height: 50vh; }
      @media (min-width: 1024px) { main.cc-main { padding-top: 32px; } }

      .cc-grid {
        display: grid;
        gap: 16px;
        grid-template-columns: repeat(1, minmax(0, 1fr));
      }
      @media (min-width: 480px) { .cc-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
      @media (min-width: 768px) { .cc-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 20px; } }
      @media (min-width: 1100px) { .cc-grid { grid-template-columns: repeat(4, minmax(0, 1fr)); } }
    </style>
    @stack('head')
</head>
<body class="min-h-screen antialiased">
@php
    $ann = \App\Models\SiteSetting::getValue('announcement', ['enabled' => false, 'text' => '']);
    $nav = \App\Models\SiteSetting::getValue('nav_header', [
        ['label' => 'Browse', 'url' => '/search', 'open_new' => false],
        ['label' => 'Blog', 'url' => '/blog', 'open_new' => false],
        ['label' => 'Licenses', 'url' => '/pricing/licenses', 'open_new' => false],
    ]);
    $footer = \App\Models\SiteSetting::getValue('footer', ['about' => 'The marketplace for high-quality code, scripts, plugins, and digital assets.', 'columns' => [], 'social' => []]);
@endphp
@if(!empty($ann['enabled']) && !empty($ann['text']))
<div class="cc-announcement">{{ $ann['text'] }}</div>
@endif
<header class="cc-header sticky top-0 z-40">
    <div class="cc-container cc-header-inner">
        <a href="{{ route('home') }}" class="cc-logo">
            <span class="cc-logo-mark">◆</span>
            CodeBazaar
        </a>

        <form action="{{ route('search') }}" method="get" class="cc-header-search-desktop">
            <div class="cc-search-wrap">
                <svg class="cc-search-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M11 18a7 7 0 100-14 7 7 0 000 14z"/></svg>
                <input type="search" name="q" value="{{ request('q') }}" placeholder="Search items…" class="cc-search" autocomplete="off">
            </div>
        </form>

        <nav class="cc-desktop-nav">
            @foreach($nav as $link)
              @php $href = $link['url'] ?? '#'; @endphp
              <a href="{{ $href }}" class="cc-nav-link" @if(!empty($link['open_new'])) target="_blank" rel="noopener" @endif>{{ $link['label'] ?? '' }}</a>
            @endforeach
            <a href="{{ route('cart.index') }}" class="cc-nav-link">Cart</a>
            @auth
                <a href="{{ route('account.collections') }}" class="cc-nav-link">Collections</a>
                @if(auth()->user()->isAdmin())
                    <a href="{{ route('admin.dashboard') }}" class="cc-btn-primary">Admin</a>
                @endif
                <a href="{{ route('account.index') }}" class="cc-nav-link">{{ auth()->user()->name ?: 'Account' }}</a>
                <form method="post" action="{{ route('logout') }}" style="display:inline">@csrf
                    <button type="submit" class="cc-nav-link" style="background:none;border:0;cursor:pointer">Logout</button>
                </form>
            @else
                <a href="{{ route('login') }}" class="cc-nav-link">Sign in</a>
                <a href="{{ route('register') }}" class="cc-btn-primary">Join free</a>
            @endauth
        </nav>

        <div class="cc-mobile-actions">
            <a href="{{ route('cart.index') }}" class="cc-nav-link">Cart</a>
            <button type="button" class="cc-menu-btn" id="cc-menu-open" aria-label="Open menu">
                <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
        </div>

        <form action="{{ route('search') }}" method="get" class="cc-mobile-search">
            <div class="cc-search-wrap">
                <svg class="cc-search-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M11 18a7 7 0 100-14 7 7 0 000 14z"/></svg>
                <input type="search" name="q" value="{{ request('q') }}" placeholder="Search items…" class="cc-search" autocomplete="off">
            </div>
        </form>
    </div>
</header>

{{-- Mobile navigation drawer --}}
<div class="cc-drawer" id="cc-drawer" aria-hidden="true">
    <div class="cc-drawer-backdrop" id="cc-drawer-backdrop"></div>
    <div class="cc-drawer-panel">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px">
            <strong>Menu</strong>
            <button type="button" class="cc-menu-btn" id="cc-menu-close" aria-label="Close menu">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        @foreach($nav as $link)
          @php $href = $link['url'] ?? '#'; @endphp
          <a href="{{ $href }}" @if(!empty($link['open_new'])) target="_blank" rel="noopener" @endif>{{ $link['label'] ?? '' }}</a>
        @endforeach
        <a href="{{ route('search') }}">Browse all</a>
        <a href="{{ route('cart.index') }}">Cart</a>
        @auth
            <a href="{{ route('account.index') }}">Account</a>
            <a href="{{ route('account.collections') }}">Collections</a>
            @if(auth()->user()->isAdmin())
                <a href="{{ route('admin.dashboard') }}">Admin</a>
            @endif
            <form method="post" action="{{ route('logout') }}">@csrf
                <button type="submit" style="width:100%;text-align:left;padding:12px 8px;background:none;border:0;border-bottom:1px solid #f1f5f9;font-weight:500;cursor:pointer">Logout</button>
            </form>
        @else
            <a href="{{ route('login') }}">Sign in</a>
            <a href="{{ route('register') }}">Join free</a>
        @endauth
    </div>
</div>

@if(session('success'))
    <div class="cc-container" style="padding-top:16px"><div class="rounded border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm text-emerald-900">{{ session('success') }}</div></div>
@endif
@if(session('error'))
    <div class="cc-container" style="padding-top:16px"><div class="rounded border border-red-200 bg-red-50 px-4 py-2 text-sm text-red-900">{{ session('error') }}</div></div>
@endif
<main class="cc-main cc-container">@yield('content')</main>
<footer class="cc-footer">
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
    <div class="border-t border-[#2a2a2a] py-4 text-center text-xs text-[#777]">&copy; {{ date('Y') }} CodeBazaar</div>
</footer>
<script>
(function () {
  var drawer = document.getElementById('cc-drawer');
  var openBtn = document.getElementById('cc-menu-open');
  var closeBtn = document.getElementById('cc-menu-close');
  var backdrop = document.getElementById('cc-drawer-backdrop');
  function open() {
    if (!drawer) return;
    drawer.classList.add('is-open');
    drawer.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
  }
  function close() {
    if (!drawer) return;
    drawer.classList.remove('is-open');
    drawer.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
  }
  if (openBtn) openBtn.addEventListener('click', open);
  if (closeBtn) closeBtn.addEventListener('click', close);
  if (backdrop) backdrop.addEventListener('click', close);
})();
</script>
@stack('scripts')
</body>
</html>
