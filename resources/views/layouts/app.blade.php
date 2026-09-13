<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @php
        if (! isset($seo) || ! is_array($seo)) {
            $seo = [
                'title' => trim($__env->yieldContent('title')) ?: null,
                'description' => trim($__env->yieldContent('meta_description')) ?: null,
                'noindex' => (bool) trim($__env->yieldContent('noindex')),
            ];
        }
    @endphp
    @include('partials.seo-meta', ['seo' => $seo])
    @php
        $theme = \App\Models\SiteSetting::getValue('colors', [
            'primary' => '#e11d2e',
            'primary_hover' => '#c1121f',
            'secondary' => '#0b1220',
            'header_bg' => '#ffffff',
            'footer_bg' => '#0b1220',
            'footer_text' => '#94a3b8',
            'announcement_bg' => '#0b1220',
        ]);
        $cPrimary = $theme['primary'] ?? '#e11d2e';
        $cHover = $theme['primary_hover'] ?? '#c1121f';
        $cSecondary = $theme['secondary'] ?? '#0b1220';
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
        ['label' => 'Help Center', 'url' => '/support', 'open_new' => false],
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

<header class="cc-header-v2 sticky top-0 z-40">
    {{-- Utility bar --}}
    <div class="cc-util-bar">
        <div class="cc-container cc-util-inner">
            <nav class="cc-util-left">
                @foreach($navDesktop as $link)
                    <a href="{{ $link['url'] ?? '#' }}" @if(!empty($link['open_new'])) target="_blank" rel="noopener" @endif>{{ $link['label'] ?? '' }}</a>
                @endforeach
            </nav>
            <div class="cc-util-right">
                @auth
                    @if(auth()->user()->isAdmin())
                        <a href="{{ route('admin.dashboard') }}">Admin</a>
                    @endif
                    <a href="{{ route('account.index') }}">{{ \Illuminate\Support\Str::limit(auth()->user()->name ?: 'Account', 16) }}</a>
                @else
                    <a href="{{ route('login') }}">Sign In</a>
                    <a href="{{ route('register') }}" class="cc-util-cta">Create account</a>
                @endauth
            </div>
        </div>
    </div>

    {{-- Main bar --}}
    <div class="cc-main-bar">
        <div class="cc-container cc-main-inner">
            <div class="cc-main-left">
                <button type="button" class="cc-menu-btn" id="cc-menu-open" aria-label="Menu">
                    <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <a href="{{ route('home') }}" class="cc-logo-v2">
                    <span class="cc-logo-cube" aria-hidden="true">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/><path d="M3.27 6.96L12 12.01l8.73-5.05M12 22.08V12"/></svg>
                    </span>
                    <span class="cc-logo-text">CodeBazaar</span>
                </a>
            </div>
            <div class="cc-main-right">
                <a href="{{ route('cart.index') }}" class="cc-icon-btn" title="Cart">
                    <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.3 2.3c-.4.4-.1 1.1.4 1.1H19M17 21a1 1 0 100-2 1 1 0 000 2zM9 21a1 1 0 100-2 1 1 0 000 2z"/></svg>
                    @if($cartCount > 0)<span class="cc-cart-badge">{{ $cartCount > 99 ? '99+' : $cartCount }}</span>@endif
                </a>
                <div class="cc-account-wrap">
                    @auth
                        <a href="{{ route('account.index') }}" class="cc-account-btn" title="Account">
                            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="cc-account-btn" title="Sign in">
                            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </div>

    @if(!empty($navOptions['desktop_show_categories']) && $navCategories->count())
    <div class="cc-subnav-v2">
        <div class="cc-container cc-subnav-inner">
            @foreach($navCategories as $cat)
                <a href="{{ route('category', $cat->slug) }}">{{ $cat->name }}</a>
            @endforeach
        </div>
    </div>
    @endif
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
        @auth
            <a href="{{ route('account.index') }}">Account</a>
            <a href="{{ route('account.purchases') }}">Purchases</a>
            <a href="{{ route('credits.index') }}">Credits</a>
            <a href="{{ route('support.index') }}">Support</a>
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
            <p class="mt-2 max-w-md text-sm" style="color:var(--cc-footer-text)">{{ $footer['about'] ?? '' }}</p>
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
    <div class="border-t py-4 text-center text-xs" style="border-color:color-mix(in srgb, var(--cc-footer-bg) 70%, #fff);color:var(--cc-footer-text)">&copy; {{ date('Y') }} CodeBazaar · <a href="{{ url('/sitemap') }}">Sitemap</a></div>
</footer>

@include('partials.support-fab')

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
@include('partials.json-ld')
@stack('scripts')
{!! $siteCodes['body_end'] ?? '' !!}
</body>
</html>
