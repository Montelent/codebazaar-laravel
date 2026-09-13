<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin') · CodeBazaar</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/tinymce@7/tinymce.min.js" referrerpolicy="origin"></script>
    <style>
      .admin-kebab-menu { max-height: min(70vh, 320px); overflow-y: auto; }
      .admin-status { display:inline-flex; align-items:center; border-radius:9999px; padding:2px 8px; font-size:11px; font-weight:600; text-transform:capitalize; }
      .admin-status-ok { background:#ecfdf5; color:#047857; }
      .admin-status-warn { background:#fffbeb; color:#b45309; }
      .admin-status-muted { background:#f1f5f9; color:#475569; }
      .admin-status-danger { background:#fef2f2; color:#b91c1c; }
      .tox-tinymce { max-width: 100% !important; border-radius: 0.5rem !important; }
      .tox .tox-toolbar-overlord,
      .tox .tox-toolbar__primary {
        flex-wrap: wrap !important;
        white-space: normal !important;
      }
      .tox .tox-toolbar__group {
        flex-wrap: wrap !important;
        padding: 2px 0 !important;
      }
      .tox .tox-editor-header { z-index: 1; }
      .tox-notifications-container .tox-notification { max-width: min(100%, 360px); }
    </style>
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

  function closeAllKebabs(except) {
    document.querySelectorAll('[data-kebab]').forEach(function (k) {
      if (except && k === except) return;
      var menu = k.querySelector('[data-kebab-menu]');
      var t = k.querySelector('[data-kebab-toggle]');
      if (menu) menu.classList.add('hidden');
      if (t) t.setAttribute('aria-expanded', 'false');
    });
  }
  document.querySelectorAll('[data-kebab]').forEach(function (wrap) {
    var toggle = wrap.querySelector('[data-kebab-toggle]');
    var menu = wrap.querySelector('[data-kebab-menu]');
    if (!toggle || !menu) return;
    toggle.addEventListener('click', function (e) {
      e.preventDefault();
      e.stopPropagation();
      var open = !menu.classList.contains('hidden');
      closeAllKebabs(wrap);
      if (open) {
        menu.classList.add('hidden');
        toggle.setAttribute('aria-expanded', 'false');
      } else {
        menu.classList.remove('hidden');
        toggle.setAttribute('aria-expanded', 'true');
      }
    });
  });
  document.addEventListener('click', function () { closeAllKebabs(); });
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') closeAllKebabs();
  });

  if (typeof tinymce !== 'undefined') {
    function selectedPlainText(editor) {
      return editor.selection.getContent({ format: 'text' }) || '';
    }

    function writeClipboard(text) {
      if (navigator.clipboard && navigator.clipboard.writeText) {
        return navigator.clipboard.writeText(text);
      }
      return Promise.reject(new Error('no clipboard'));
    }

    function openPasteDialog(editor) {
      editor.windowManager.open({
        title: 'Paste content',
        body: {
          type: 'panel',
          items: [{
            type: 'textarea',
            name: 'pasted',
            label: 'Paste or type content here, then click Insert',
            maximized: true
          }]
        },
        buttons: [
          { type: 'cancel', text: 'Cancel' },
          { type: 'submit', text: 'Insert', primary: true }
        ],
        onSubmit: function (api) {
          var data = api.getData();
          var raw = (data.pasted || '').trim();
          if (raw) {
            if (/<[a-z][\s\S]*>/i.test(raw)) {
              editor.insertContent(raw);
            } else {
              editor.insertContent(editor.dom.encode(raw).replace(/\r\n|\r|\n/g, '<br>'));
            }
          }
          api.close();
        }
      });
    }

    tinymce.init({
      selector: 'textarea.tinymce',
      height: 420,
      resize: true,
      menubar: 'file edit view insert format tools table help',
      plugins: [
        'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
        'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
        'insertdatetime', 'media', 'table', 'help', 'wordcount'
      ],
      toolbar: [
        'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | forecolor backcolor',
        'alignleft aligncenter alignright alignjustify | bullist numlist outdent indent',
        'link image media table | removeformat pastetext | code fullscreen preview | cuthelper copyhelper pastehelper'
      ].join(' | '),
      toolbar_mode: 'wrap',
      toolbar_sticky: false,

      contextmenu: 'link image inserttable | cell row column deletetable',
      contextmenu_never_use_native: false,

      browser_spellcheck: true,
      branding: false,
      promotion: false,
      license_key: 'gpl',

      paste_data_images: true,
      paste_as_text: false,

      valid_elements: '*[*]',
      extended_valid_elements: '*[*]',
      verify_html: false,
      entity_encoding: 'raw',

      content_style: 'body{font-family:Inter,system-ui,sans-serif;font-size:15px;line-height:1.6;color:#222;padding:8px} p{margin:0 0 1em} h1,h2,h3{font-weight:700;margin:1em 0 .5em} ul,ol{padding-left:1.4em;margin:0 0 1em} img{max-width:100%;height:auto}',

      mobile: {
        menubar: 'file edit insert format',
        toolbar_mode: 'wrap',
        toolbar: [
          'undo redo | bold italic underline | blocks',
          'bullist numlist | link image | alignleft aligncenter',
          'removeformat pastetext | code | cuthelper copyhelper pastehelper'
        ].join(' | ')
      },

      setup: function (editor) {
        editor.on('change keyup SetContent', function () {
          editor.save();
        });

        editor.ui.registry.addButton('cuthelper', {
          text: 'Cut',
          tooltip: 'Cut selection (mobile-friendly)',
          onAction: function () {
            var text = selectedPlainText(editor);
            if (!text) {
              editor.notificationManager.open({ text: 'Select text to cut first.', type: 'warning', timeout: 2500 });
              return;
            }
            writeClipboard(text).then(function () {
              editor.selection.setContent('');
              editor.notificationManager.open({ text: 'Cut to clipboard.', type: 'success', timeout: 2000 });
            }).catch(function () {
              // Fallback: remove selection and show text so user can copy manually
              editor.windowManager.alert('Clipboard blocked. Selected text was removed from the editor. Long-press in another app to paste if it was already copied, or use Paste to re-insert.');
              editor.selection.setContent('');
            });
          }
        });

        editor.ui.registry.addButton('copyhelper', {
          text: 'Copy',
          tooltip: 'Copy selection (mobile-friendly)',
          onAction: function () {
            var text = selectedPlainText(editor);
            if (!text) {
              editor.notificationManager.open({ text: 'Select text to copy first.', type: 'warning', timeout: 2500 });
              return;
            }
            writeClipboard(text).then(function () {
              editor.notificationManager.open({ text: 'Copied to clipboard.', type: 'success', timeout: 2000 });
            }).catch(function () {
              editor.windowManager.open({
                title: 'Copy text',
                body: {
                  type: 'panel',
                  items: [{
                    type: 'textarea',
                    name: 'copied',
                    label: 'Select all and copy (long-press → Copy)',
                    maximized: true
                  }]
                },
                initialData: { copied: text },
                buttons: [{ type: 'cancel', text: 'Close' }]
              });
            });
          }
        });

        editor.ui.registry.addButton('pastehelper', {
          text: 'Paste',
          tooltip: 'Paste text (mobile-friendly)',
          onAction: function () {
            if (navigator.clipboard && navigator.clipboard.readText) {
              navigator.clipboard.readText().then(function (text) {
                if (text) {
                  editor.insertContent(editor.dom.encode(text).replace(/\n/g, '<br>'));
                } else {
                  openPasteDialog(editor);
                }
              }).catch(function () {
                openPasteDialog(editor);
              });
            } else {
              openPasteDialog(editor);
            }
          }
        });
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
