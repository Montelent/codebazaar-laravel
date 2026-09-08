<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin') · CodeBazaar</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/tinymce@7/tinymce.min.js" referrerpolicy="origin"></script>
    @stack('head')
</head>
<body class="min-h-screen bg-slate-100 text-slate-900">
<div class="flex min-h-screen">
    <aside class="hidden w-64 shrink-0 border-r border-slate-200 bg-slate-900 text-slate-200 lg:block">
        <div class="border-b border-slate-800 px-4 py-4 text-lg font-bold text-white">CodeBazaar Admin</div>
        <nav class="space-y-1 p-3 text-sm">
            @include('layouts.partials.admin-nav')
        </nav>
    </aside>

    <div class="flex min-w-0 flex-1 flex-col">
        <header class="flex items-center justify-between border-b border-slate-200 bg-white px-4 py-3">
            <div class="flex items-center gap-3">
                <button type="button" id="admin-menu-btn" class="inline-flex items-center justify-center rounded-lg border border-slate-200 p-2 text-slate-600 hover:bg-slate-50 lg:hidden" aria-label="Open menu">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <div class="font-semibold">@yield('title', 'Admin')</div>
            </div>
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

<div id="admin-drawer" class="fixed inset-0 z-50 hidden lg:hidden" aria-hidden="true">
    <div id="admin-drawer-backdrop" class="absolute inset-0 bg-black/40"></div>
    <aside class="absolute inset-y-0 left-0 flex w-72 max-w-[85vw] flex-col bg-slate-900 text-slate-200 shadow-xl">
        <div class="flex items-center justify-between border-b border-slate-800 px-4 py-4">
            <span class="text-lg font-bold text-white">CodeBazaar Admin</span>
            <button type="button" id="admin-menu-close" class="rounded-lg p-2 text-slate-400 hover:bg-slate-800 hover:text-white" aria-label="Close menu">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <nav class="flex-1 space-y-1 overflow-y-auto p-3 text-sm">
            @include('layouts.partials.admin-nav')
        </nav>
    </aside>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  var btn = document.getElementById('admin-menu-btn');
  var closeBtn = document.getElementById('admin-menu-close');
  var drawer = document.getElementById('admin-drawer');
  var backdrop = document.getElementById('admin-drawer-backdrop');
  function openMenu() {
    if (!drawer) return;
    drawer.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
  }
  function closeMenu() {
    if (!drawer) return;
    drawer.classList.add('hidden');
    document.body.style.overflow = '';
  }
  if (btn) btn.addEventListener('click', openMenu);
  if (closeBtn) closeBtn.addEventListener('click', closeMenu);
  if (backdrop) backdrop.addEventListener('click', closeMenu);

  // Collapsible nav groups (Settings drawer)
  document.querySelectorAll('[data-nav-group]').forEach(function (group) {
    var toggle = group.querySelector('[data-nav-toggle]');
    var panel = group.querySelector('[data-nav-panel]');
    var chevron = group.querySelector('[data-nav-chevron]');
    if (!toggle || !panel) return;
    toggle.addEventListener('click', function () {
      var open = !panel.classList.contains('hidden');
      if (open) {
        panel.classList.add('hidden');
        toggle.setAttribute('aria-expanded', 'false');
        if (chevron) chevron.classList.remove('rotate-90');
      } else {
        panel.classList.remove('hidden');
        toggle.setAttribute('aria-expanded', 'true');
        if (chevron) chevron.classList.add('rotate-90');
      }
    });
  });

  if (typeof tinymce !== 'undefined') {
    tinymce.init({
      selector: 'textarea.tinymce',
      height: 420,
      menubar: 'file edit view insert format tools table help',
      plugins: [
        'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
        'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
        'insertdatetime', 'media', 'table', 'help', 'wordcount'
      ],
      toolbar: 'undo redo | blocks | bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image media table | removeformat | code fullscreen preview',
      toolbar_mode: 'sliding',
      branding: false,
      promotion: false,
      license_key: 'gpl',
      valid_elements: '*[*]',
      extended_valid_elements: '*[*]',
      verify_html: false,
      entity_encoding: 'raw',
      content_style: 'body{font-family:Inter,system-ui,sans-serif;font-size:15px;line-height:1.6;color:#222} p{margin:0 0 1em} h1,h2,h3{font-weight:700;margin:1em 0 .5em} ul,ol{padding-left:1.4em;margin:0 0 1em} img{max-width:100%;height:auto}',
      setup: function (editor) {
        editor.on('change keyup', function () { editor.save(); });
      }
    });

    document.addEventListener('submit', function () {
      if (window.tinymce) {
        window.tinymce.triggerSave();
      }
    }, true);
  }
});
</script>
@stack('scripts')
</body>
</html>
