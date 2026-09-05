<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Install') · CodeBazaar</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-100 text-slate-900">
<div class="mx-auto max-w-2xl px-4 py-10">
    <div class="mb-8 text-center">
        <div class="text-2xl font-bold text-emerald-700">CodeBazaar</div>
        <p class="text-sm text-slate-500">Installation wizard</p>
    </div>
    @if(session('error'))
        <div class="mb-4 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-800">{{ session('error') }}</div>
    @endif
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        @yield('content')
    </div>
    <p class="mt-6 text-center text-xs text-slate-400">CodeBazaar Laravel · MIT</p>
</div>
</body>
</html>
