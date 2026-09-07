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
            maxWidth: { 'cc': '1200px' },
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
      body { font-family: Inter, system-ui, sans-serif; background: var(--cc-bg); color: var(--cc-text); }
      .cc-container { width: 100%; max-width: 1200px; margin-left: auto; margin-right: auto; padding-left: 16px; padding-right: 16px; }
      @media (min-width: 640px) { .cc-container { padding-left: 20px; padding-right: 20px; } }
      @media (min-width: 1024px) { .cc-container { padding-left: 24px; padding-right: 24px; } }

      .cc-header {
        background: #fff;
        border-bottom: 1px solid var(--cc-border);
        box-shadow: 0 1px 0 rgba(0,0,0,.04);
      }
      .cc-logo { font-weight: 800; letter-spacing: -0.02em; }
      .cc-search {
        border: 1px solid #d1d5db;
        border-radius: 6px;
        background: #fff;
        height: 42px;
        padding: 0 14px;
        font-size: 15px;
        width: 100%;
      }
      .cc-search:focus { outline: 2px solid rgba(130,180,64,.35); border-color: var(--cc-green); }
      .cc-btn-primary {
        background: var(--cc-green);
        color: #fff;
        font-weight: 600;
        border-radius: 6px;
        padding: 10px 18px;
        font-size: 14px;
        white-space: nowrap;
      }
      .cc-btn-primary:hover { background: var(--cc-green-hover); }
      .cc-nav-link { color: #444; font-size: 14px; font-weight: 500; padding: 6px 4px; }
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
      .cc-footer a:hover { color: #fff; }

      @media (min-width: 1024px) {
        .cc-buy-box { position: sticky; top: 88px; }
      }

      /* Mobile: stack header cleanly */
      @media (max-width: 767px) {
        .cc-desktop-nav { display: none; }
        .cc-header .cc-container {
          display: grid;
          grid-template-columns: 1fr auto;
          gap: 10px 12px;
          align-items: center;
        }
        .cc-header .cc-logo { grid-column: 1; }
        .cc-header .cc-mobile-actions { grid-column: 2; display: flex; align-items: center; gap: 10px; }
        .cc-header .cc-mobile-search-row {
          grid-column: 1 / -1;
          width: 100%;
        }
      }
      @media (min-width: 768px) {
        .cc-mobile-actions { display: none; }
        .cc-mobile-search-row { display: none; }
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
    <div class="cc-container flex flex-wrap items-center justify-between gap-3 py-3 md:flex-nowrap">
        <a href="{{ route('home') }}" class="cc-logo flex items-center gap-2 text-lg text-slate-900">
            <span class="inline-flex h-8 w-8 items-center justify-center rounded bg-[var(--cc-green)] text-sm text-white">◆</span>
            CodeBazaar
        </a>

        {{-- Desktop search --}}
        <form action="{{ route('search') }}" method="get" class="hidden flex-1 md:block md:max-w-md lg:max-w-lg">
            <input type="search" name="q" value="{{ request('q') }}" placeholder="Search items…" class="cc-search">
        </form>

        {{-- Desktop nav --}}
        <nav class="cc-desktop-nav flex flex-wrap items-center gap-3 md:gap-4">
            @foreach($nav as $link)
              @php $href = $link['url'] ?? '#'; @endphp
              <a href="{{ $href }}" class="cc-nav-link" @if(!empty($link['open_new'])) target="_blank" rel="noopener" @endif>{{ $link['label'] ?? '' }}</a>
            @endforeach
            <a href="{{ route('cart.index') }}" class="cc-nav-link">Cart</a>
            @auth
                <a href="{{ route('account.collections') }}" class="cc-nav-link hidden sm:inline">Collections</a>
                @if(auth()->user()->isAdmin())
                    <a href="{{ route('admin.dashboard') }}" class="cc-btn-primary">Admin</a>
                @endif
                <a href="{{ route('account.index') }}" class="cc-nav-link">{{ auth()->user()->name ?: 'Account' }}</a>
                <form method="post" action="{{ route('logout') }}">@csrf
                    <button type="submit" class="cc-nav-link text-slate-400">Logout</button>
                </form>
            @else
                <a href="{{ route('login') }}" class="cc-nav-link">Sign in</a>
                <a href="{{ route('register') }}" class="cc-btn-primary">Join free</a>
            @endauth
        </nav>

        {{-- Mobile: Cart + Sign in / Account --}}
        <div class="cc-mobile-actions md:hidden">
            <a href="{{ route('cart.index') }}" class="cc-nav-link text-sm font-medium">Cart</a>
            @auth
                @if(auth()->user()->isAdmin())
                    <a href="{{ route('admin.dashboard') }}" class="cc-btn-primary !px-3 !py-1.5 text-xs">Admin</a>
                @else
                    <a href="{{ route('account.index') }}" class="cc-nav-link text-sm font-medium">Account</a>
                @endif
            @else
                <a href="{{ route('login') }}" class="cc-btn-primary !px-3 !py-1.5 text-xs">Sign in</a>
            @endauth
        </div>

        {{-- Mobile search full width --}}
        <form action="{{ route('search') }}" method="get" class="cc-mobile-search-row w-full md:hidden">
            <input type="search" name="q" value="{{ request('q') }}" placeholder="Search items…" class="cc-search">
        </form>
    </div>
</header>
@if(session('success'))
    <div class="cc-container pt-4"><div class="rounded border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm text-emerald-900">{{ session('success') }}</div></div>
@endif
@if(session('error'))
    <div class="cc-container pt-4"><div class="rounded border border-red-200 bg-red-50 px-4 py-2 text-sm text-red-900">{{ session('error') }}</div></div>
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
@stack('scripts')
</body>
</html>
