<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin') · CodeBazaar</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/7/tinymce.min.js" referrerpolicy="origin"></script>
    @stack('head')
</head>
<body class="min-h-screen bg-slate-100 text-slate-900">
<div class="flex min-h-screen">
    <aside class="hidden w-64 shrink-0 border-r border-slate-200 bg-slate-900 text-slate-200 lg:block">
        <div class="border-b border-slate-800 px-4 py-4 text-lg font-bold text-white">CodeBazaar Admin</div>
        <nav class="space-y-1 p-3 text-sm">
            <a href="{{ route('admin.dashboard') }}" class="block rounded-lg px-3 py-2 hover:bg-slate-800">Dashboard</a>
            <p class="px-3 pt-3 text-xs font-semibold uppercase tracking-wide text-slate-500">Catalog</p>
            <a href="{{ route('admin.products.index') }}" class="block rounded-lg px-3 py-2 hover:bg-slate-800">Products</a>
            <a href="{{ route('admin.products.create') }}" class="block rounded-lg px-3 py-2 hover:bg-slate-800">Add product</a>
            <a href="{{ route('admin.categories.index') }}" class="block rounded-lg px-3 py-2 hover:bg-slate-800">Categories</a>
            <a href="{{ route('admin.attributes.index') }}" class="block rounded-lg px-3 py-2 hover:bg-slate-800">Attributes</a>
            <a href="{{ route('admin.tags.index') }}" class="block rounded-lg px-3 py-2 hover:bg-slate-800">Tags</a>
            <a href="{{ route('admin.media.index') }}" class="block rounded-lg px-3 py-2 hover:bg-slate-800">Media library</a>
            <a href="{{ route('admin.licenses.index') }}" class="block rounded-lg px-3 py-2 hover:bg-slate-800">Licenses</a>
            <p class="px-3 pt-3 text-xs font-semibold uppercase tracking-wide text-slate-500">Content</p>
            <a href="{{ route('admin.blog.index') }}" class="block rounded-lg px-3 py-2 hover:bg-slate-800">Blog</a>
            <a href="{{ route('admin.pages.index') }}" class="block rounded-lg px-3 py-2 hover:bg-slate-800">Pages</a>
            <a href="{{ route('admin.newsletter.index') }}" class="block rounded-lg px-3 py-2 hover:bg-slate-800">Newsletter</a>
            <p class="px-3 pt-3 text-xs font-semibold uppercase tracking-wide text-slate-500">Commerce</p>
            <a href="{{ route('admin.orders.index') }}" class="block rounded-lg px-3 py-2 hover:bg-slate-800">Orders</a>
            <a href="{{ route('admin.users.index') }}" class="block rounded-lg px-3 py-2 hover:bg-slate-800">Users</a>
            <p class="px-3 pt-3 text-xs font-semibold uppercase tracking-wide text-slate-500">Site</p>
            <a href="{{ route('admin.settings.hub') }}" class="block rounded-lg px-3 py-2 hover:bg-slate-800">Settings hub</a>
            <a href="{{ route('admin.settings.payments') }}" class="block rounded-lg px-3 py-2 hover:bg-slate-800">Payments</a>
            <a href="{{ route('admin.settings.navigation') }}" class="block rounded-lg px-3 py-2 hover:bg-slate-800">Navigation</a>
            <a href="{{ route('admin.settings.header_footer') }}" class="block rounded-lg px-3 py-2 hover:bg-slate-800">Header / footer</a>
            <a href="{{ route('admin.settings.schema') }}" class="block rounded-lg px-3 py-2 hover:bg-slate-800">Schema SEO</a>
            <form method="post" action="{{ route('admin.migrate') }}" class="mt-4 px-3">@csrf
              <button class="text-left text-xs text-amber-300 hover:text-amber-200">Run DB migrations</button>
            </form>
            <a href="{{ route('home') }}" class="mt-2 block rounded-lg px-3 py-2 text-emerald-400 hover:bg-slate-800">← View storefront</a>
        </nav>
    </aside>
    <div class="flex min-w-0 flex-1 flex-col">
        <header class="flex items-center justify-between border-b border-slate-200 bg-white px-4 py-3">
            <div class="font-semibold">@yield('title', 'Admin')</div>
            <div class="flex gap-3 text-sm">
                <a href="{{ route('home') }}" class="text-emerald-700">Storefront</a>
                <form method="post" action="{{ route('logout') }}">@csrf<button class="text-slate-500">Logout</button></form>
            </div>
        </header>
        <div class="flex-1 p-4 lg:p-6">
            @if(session('success'))
                <div class="mb-4 whitespace-pre-wrap rounded-lg bg-emerald-50 px-4 py-2 text-sm text-emerald-800">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="mb-4 rounded-lg bg-red-50 px-4 py-2 text-sm text-red-800">{{ session('error') }}</div>
            @endif
            @yield('content')
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
  if (typeof tinymce !== 'undefined') {
    tinymce.init({
      selector: 'textarea.tinymce',
      height: 360,
      menubar: true,
      plugins: 'lists link image table code fullscreen preview searchreplace visualblocks wordcount',
      toolbar: 'undo redo | styles | bold italic underline | alignleft aligncenter alignright | bullist numlist | link image table | code fullscreen',
      branding: false,
      promotion: false
    });
  }
});
</script>
@stack('scripts')
</body>
</html>
