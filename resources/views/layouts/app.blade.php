<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('app.name', 'CodeBazaar'))</title>
    <meta name="description" content="@yield('meta_description', 'Digital marketplace for code, scripts, themes and plugins.')">
    <script src="https://cdn.tailwindcss.com"></script>
    @stack('head')
</head>
<body class="min-h-screen bg-slate-50 text-slate-900">
@php
    $ann = \App\Models\SiteSetting::getValue('announcement', ['enabled' => false, 'text' => '']);
@endphp
@if(!empty($ann['enabled']) && !empty($ann['text']))
<div class="bg-emerald-700 px-4 py-2 text-center text-sm text-white">{{ $ann['text'] }}</div>
@endif
<header class="sticky top-0 z-40 border-b border-slate-200 bg-white/95 backdrop-blur">
    <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-3 px-4 py-3">
        <a href="{{ route('home') }}" class="flex items-center gap-2 text-lg font-bold text-slate-900">
            <span class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-emerald-600 text-white shadow">◆</span>
            <span>CodeBazaar</span>
        </a>
        <form action="{{ route('search') }}" method="get" class="order-3 w-full md:order-none md:max-w-md md:flex-1">
            <input type="search" name="q" value="{{ request('q') }}" placeholder="Search items…"
                   class="w-full rounded-full border border-slate-300 bg-slate-50 px-4 py-2 text-sm focus:border-emerald-500 focus:outline-none">
        </form>
        <nav class="flex flex-wrap items-center gap-3 text-sm font-medium">
            <a href="{{ route('search') }}" class="text-slate-600 hover:text-emerald-700">Browse</a>
            <a href="{{ route('cart.index') }}" class="text-slate-600 hover:text-emerald-700">Cart</a>
            @auth
                <a href="{{ route('account.purchases') }}" class="text-slate-600 hover:text-emerald-700">Purchases</a>
                <a href="{{ route('account.downloads') }}" class="text-slate-600 hover:text-emerald-700">Downloads</a>
                @if(auth()->user()->isAdmin())
                    <a href="{{ route('admin.dashboard') }}" class="rounded-lg bg-slate-900 px-3 py-1.5 text-white">Admin</a>
                @endif
                <a href="{{ route('account.index') }}" class="text-slate-600 hover:text-emerald-700">{{ auth()->user()->name ?: 'Account' }}</a>
                <form method="post" action="{{ route('logout') }}">@csrf
                    <button type="submit" class="text-slate-500 hover:text-red-600">Logout</button>
                </form>
            @else
                <a href="{{ route('login') }}" class="text-slate-600 hover:text-emerald-700">Sign in</a>
                <a href="{{ route('register') }}" class="rounded-lg bg-emerald-600 px-3 py-1.5 text-white hover:bg-emerald-700">Join free</a>
            @endauth
        </nav>
    </div>
</header>
@if(session('success'))
    <div class="mx-auto max-w-7xl px-4 pt-4"><div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm text-emerald-900">{{ session('success') }}</div></div>
@endif
@if(session('error'))
    <div class="mx-auto max-w-7xl px-4 pt-4"><div class="rounded-lg border border-red-200 bg-red-50 px-4 py-2 text-sm text-red-900">{{ session('error') }}</div></div>
@endif
<main class="mx-auto max-w-7xl px-4 py-8">@yield('content')</main>
@php
    $footer = \App\Models\SiteSetting::getValue('footer', ['links' => [], 'about' => 'The marketplace for high-quality code, scripts, plugins, and digital assets.']);
@endphp
<footer class="mt-16 border-t border-slate-800 bg-slate-950 text-slate-300">
    <div class="mx-auto grid max-w-7xl gap-8 px-4 py-12 sm:grid-cols-2 lg:grid-cols-4">
        <div class="sm:col-span-2">
            <p class="text-lg font-semibold text-white">CodeBazaar</p>
            <p class="mt-2 max-w-md text-sm text-slate-400">{{ $footer['about'] ?? '' }}</p>
        </div>
        <div>
            <p class="text-sm font-semibold text-white">Explore</p>
            <ul class="mt-3 space-y-2 text-sm">
                <li><a class="hover:text-white" href="{{ route('search') }}">All items</a></li>
                <li><a class="hover:text-white" href="{{ route('home') }}">Home</a></li>
                <li><a class="hover:text-white" href="{{ route('blog.index') }}">Blog</a></li>
            </ul>
        </div>
        <div>
            <p class="text-sm font-semibold text-white">Account</p>
            <ul class="mt-3 space-y-2 text-sm">
                <li><a class="hover:text-white" href="{{ route('login') }}">Sign in</a></li>
                <li><a class="hover:text-white" href="{{ route('register') }}">Create account</a></li>
                <li><a class="hover:text-white" href="{{ route('cart.index') }}">Cart</a></li>
            </ul>
        </div>
    </div>
    <div class="border-t border-slate-800 py-4 text-center text-xs text-slate-500">&copy; {{ date('Y') }} CodeBazaar</div>
</footer>
@stack('scripts')
</body>
</html>
