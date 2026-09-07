<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('app.name', 'CodeBazaar'))</title>
    <meta name="description" content="@yield('meta_description', 'Digital marketplace for code, scripts, themes and plugins.')">
    @php
        $theme = \App\Models\SiteSetting::getValue('colors', [
            'primary' => '#82b440',
            'primary_hover' => '#6f9a36',
            'secondary' => '#1b2838',
            'header_bg' => '#ffffff',
            'footer_bg' => '#1a1a1a',
            'footer_text' => '#b0b0b0',
            'announcement_bg' => '#2c3e50',
        ]);
        $cPrimary = $theme['primary'] ?? '#82b440';
        $cHover = $theme['primary_hover'] ?? '#6f9a36';
        $cSecondary = $theme['secondary'] ?? '#1b2838';
        $cHeaderBg = $theme['header_bg'] ?? '#ffffff';
        $cFooterBg = $theme['footer_bg'] ?? '#1a1a1a';
        $cFooterText = $theme['footer_text'] ?? '#b0b0b0';
        $cAnnBg = $theme['announcement_bg'] ?? '#2c3e50';
        $cartCount = 0;
        try {
            $cart = session('cart', []);
            $cartCount = is_array($cart) ? count($cart) : 0;
        } catch (\Throwable $e) {}
    @endphp
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
      tailwind.config = {
        theme: {
          extend: {
            colors: {
              brand: {
                DEFAULT: '{{ $cPrimary }}',
                hover: '{{ $cHover }}',
                dark: '{{ $cSecondary }}',
              },
              envato: { green: '{{ $cPrimary }}', dark: '#262626', muted: '#7a7a7a' }
            },
            fontFamily: { sans: ['Inter', 'system-ui', '-apple-system', 'Segoe UI', 'Roboto', 'sans-serif'] }
          }
        }
      }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @include('layouts.partials.storefront-css')
    @stack('head')
    @php $siteCodes = \App\Models\SiteSetting::getValue('site_codes', ['head' => '', 'body_start' => '', 'body_end' => '']); @endphp
    {!! $siteCodes['head'] ?? '' !!}
</head>
<body>
{!! $siteCodes['body_start'] ?? '' !!}
@php
    $ann = \App\Models\SiteSetting::getValue('announcement', ['enabled' => false, 'text' => '']);
    $navDefaults = [
        ['label' => 'All items', 'url' => '/search', 'open_new' => false],
        ['label' => 'Blog', 'url' => '/blog', 'open_new' => false],
        ['label' => 'Licenses', 'url' => '/pricing/licenses', 'open_new' => false],
    ];
    $legacyNav = \App\Models\SiteSetting::getValue('nav_header', $navDefaults);
    $navDesktop = \App\Models\SiteSetting::getValue('nav_desktop', $legacyNav);
    $navMobile = \App\Models\SiteSetting::getValue('nav_mobile', $legacyNav);
    if (! is_array($navDesktop)) { $navDesktop = $navDefaults; }
    if (! is_array($navMobile)) { $navMobile = $navDefaults; }
    $navOptions = \App\Models\SiteSetting::getValue('nav_options', [
        'desktop_show_categories' => true,
        'mobile_show_categories' => true,
        'mobile_show_account_links' => true,
    ]);
    $footer = \App\Models\SiteSetting::getValue('footer', ['about' => 'The marketplace for high-quality code, scripts, plugins, and digital assets.', 'columns' => [], 'social' => []]);
    try {
        $navCategories = \App\Models\Category::query()->whereNull('parent_id')->orderBy('name')->take(12)->get();
    } catch (\Throwable $e) {
        $navCategories = collect();
    }
@endphp
@if(!empty($ann['enabled']) && !empty($ann['text']))
<div style="background:var(--cc-announcement-bg);color:#fff;font-size:13px;text-align:center;padding:8px 12px">{{ $ann['text'] }}</div>
@endif

<header class="cc-topbar sticky top-0 z-40">
    <div class="cc-container cc-topbar-inner">
        <a href="{{ route('home') }}" class="cc-logo"><span class="cc-logo-mark">◆</span> CodeBazaar</a>
        <form action="{{ route('search') }}" method="get" class="cc-header-search" role="search">
            <input type="search" name="q" value="{{ request('q') }}" placeholder="Search for items…" autocomplete="off">
            <button type="submit" aria-label="Search">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M11 18a7 7 0 100-14 7 7 0 000 14z"/></svg>
            </button>
        </form>
        <div class="cc-header-actions">
            <a href="{{ route('cart.index') }}" class="cc-btn-cart" title="Cart">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.3 2.3c-.4.4-.1 1.1.4 1.1H19M17 21a1 1 0 100-2 1 1 0 000 2zM9 21a1 1 0 100-2 1 1 0 000 2z"/></svg>
                <span class="cc-icon-label">Cart</span>
                @if($cartCount > 0)<span class="cc-cart-badge">{{ $cartCount > 99 ? '99+' : $cartCount }}</span>@endif
            </a>
            @auth
                @if(auth()->user()->isAdmin())
                    <a href="{{ route('admin.dashboard') }}" class="cc-hide-mobile" title="Admin">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span class="cc-icon-label">Admin</span>
                    </a>
                @endif
                <a href="{{ route('account.index') }}" class="cc-hide-mobile" title="Account">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    <span class="cc-icon-label">{{ \Illuminate\Support\Str::limit(auth()->user()->name ?: 'Account', 12) }}</span>
                </a>
                <form method="post" action="{{ route('logout') }}" class="cc-hide-mobile">@csrf
                    <button type="submit" title="Logout">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        <span class="cc-icon-label">Logout</span>
                    </button>
                </form>
            @else
                <a href="{{ route('login') }}" class="cc-hide-mobile" title="Sign in">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                    <span class="cc-icon-label">Sign in</span>
                </a>
                <a href="{{ route('register') }}" class="cc-btn-green cc-hide-mobile" title="Create account">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                    <span class="cc-icon-label">Create account</span>
                </a>
            @endauth
            <button type="button" class="cc-menu-btn" id="cc-menu-open" aria-label="Menu">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
        </div>
    </div>
    <div class="cc-subnav">
        <div class="cc-container cc-subnav-inner">
            @if(!empty($navOptions['desktop_show_categories']))
              @foreach($navCategories as $cat)
                <a href="{{ route('category', $cat->slug) }}">{{ $cat->name }}</a>
              @endforeach
            @endif
            @foreach($navDesktop as $link)
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
        @if(!empty($navOptions['mobile_show_categories']))
          @foreach($navCategories as $cat)
            <a href="{{ route('category', $cat->slug) }}">{{ $cat->name }}</a>
          @endforeach
        @endif
        @foreach($navMobile as $link)
            <a href="{{ $link['url'] ?? '#' }}" @if(!empty($link['open_new'])) target="_blank" rel="noopener" @endif>{{ $link['label'] ?? '' }}</a>
        @endforeach
        @if(!isset($navOptions['mobile_show_account_links']) || !empty($navOptions['mobile_show_account_links']))
        <a href="{{ route('cart.index') }}">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.3 2.3c-.4.4-.1 1.1.4 1.1H19M17 21a1 1 0 100-2 1 1 0 000 2zM9 21a1 1 0 100-2 1 1 0 000 2z"/></svg>
            Cart @if($cartCount > 0)({{ $cartCount }})@endif
        </a>
        @auth
            <a href="{{ route('account.index') }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                Account
            </a>
            @if(auth()->user()->isAdmin())
                <a href="{{ route('admin.dashboard') }}">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Admin
                </a>
            @endif
            <form method="post" action="{{ route('logout') }}">@csrf
                <button type="submit" style="width:100%;display:flex;align-items:center;gap:10px;text-align:left;padding:12px 8px;border:0;border-bottom:1px solid #f1f5f9;background:none;font-weight:500;font-size:14px;cursor:pointer">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    Logout
                </button>
            </form>
        @else
            <a href="{{ route('login') }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                Sign in
            </a>
            <a href="{{ route('register') }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                Create account
            </a>
        @endauth
        @endif
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
            <p class="mt-2 max-w-md text-sm" style="color:var(--cc-footer-text)">{{ $footer['about'] ?? '' }}</p>
            @php $social = $footer['social'] ?? []; @endphp
            @if(!empty($social['twitter']) || !empty($social['facebook']) || !empty($social['github']))
              <div class="mt-4 flex flex-wrap gap-3 text-sm">
                @if(!empty($social['twitter']))<a href="{{ $social['twitter'] }}" target="_blank" rel="noopener">Twitter / X</a>@endif
                @if(!empty($social['facebook']))<a href="{{ $social['facebook'] }}" target="_blank" rel="noopener">Facebook</a>@endif
                @if(!empty($social['github']))<a href="{{ $social['github'] }}" target="_blank" rel="noopener">GitHub</a>@endif
              </div>
            @endif
        </div>
        @php $footerCols = $footer['columns'] ?? []; @endphp
        @forelse($footerCols as $col)
          <div>
            <p class="text-sm font-semibold text-white">{{ $col['title'] ?? '' }}</p>
            <ul class="mt-3 space-y-2 text-sm">
              @foreach(($col['links'] ?? []) as $flink)
                <li><a href="{{ $flink['url'] ?? '#' }}">{{ $flink['label'] ?? '' }}</a></li>
              @endforeach
            </ul>
          </div>
        @empty
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
        @endforelse
    </div>
    {!! \App\Support\AdSlots::render('footer_bottom') !!}
    <div class="border-t py-4 text-center text-xs" style="border-color:color-mix(in srgb, var(--cc-footer-bg) 70%, #fff);color:var(--cc-footer-text)">&copy; {{ date('Y') }} CodeBazaar</div>
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
