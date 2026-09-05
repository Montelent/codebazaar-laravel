<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('app.name', 'CodeBazaar'))</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-50 text-slate-900">
<header class="border-b border-slate-200 bg-white">
    <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-3">
        <a href="{{ route('home') }}" class="flex items-center gap-2 text-lg font-bold">
            <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-600 text-white">◆</span>
            CodeBazaar
        </a>
        <form action="{{ route('search') }}" method="get" class="hidden flex-1 max-w-md md:block">
            <input type="search" name="q" value="{{ request('q') }}" placeholder="Search products…"
                   class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
        </form>
        <nav class="flex items-center gap-3 text-sm">
            <a href="{{ route('cart.index') }}" class="text-slate-600 hover:text-emerald-700">Cart</a>
            @auth
                @if(auth()->user()->isAdmin())
                    <a href="{{ route('admin.dashboard') }}" class="font-medium text-emerald-700">Admin</a>
                @endif
                <a href="{{ route('account.index') }}" class="text-slate-600 hover:text-emerald-700">Account</a>
                <form method="post" action="{{ route('logout') }}">@csrf
                    <button class="text-slate-500">Logout</button>
                </form>
            @else
                <a href="{{ route('login') }}" class="text-slate-600 hover:text-emerald-700">Sign in</a>
                <a href="{{ route('register') }}" class="rounded-lg bg-emerald-600 px-3 py-1.5 font-medium text-white">Join</a>
            @endauth
        </nav>
    </div>
</header>
@if(session('success'))
    <div class="mx-auto max-w-7xl px-4 pt-4"><div class="rounded-lg bg-emerald-50 px-4 py-2 text-sm text-emerald-800">{{ session('success') }}</div></div>
@endif
@if(session('error'))
    <div class="mx-auto max-w-7xl px-4 pt-4"><div class="rounded-lg bg-red-50 px-4 py-2 text-sm text-red-800">{{ session('error') }}</div></div>
@endif
<main class="mx-auto max-w-7xl px-4 py-8">@yield('content')</main>
<footer class="mt-16 border-t border-slate-200 bg-slate-900 text-slate-300">
    <div class="mx-auto max-w-7xl px-4 py-10">
        <p class="text-lg font-semibold text-white">CodeBazaar</p>
        <p class="mt-1 text-sm text-slate-400">The marketplace for high-quality code, scripts, plugins, and digital assets.</p>
        <p class="mt-6 text-xs text-slate-500">&copy; {{ date('Y') }} CodeBazaar. MIT License.</p>
    </div>
</footer>
</body>
</html>
