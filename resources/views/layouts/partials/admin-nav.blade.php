@php
  $settingsOpen = request()->routeIs('admin.settings.*');
@endphp
<a href="{{ route('admin.dashboard') }}" class="block rounded-lg px-3 py-2 hover:bg-slate-800 {{ request()->routeIs('admin.dashboard') ? 'bg-slate-800 text-white' : '' }}">Dashboard</a>

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

{{-- Settings drawer (click chevron / row to expand) --}}
<div class="admin-nav-group" data-nav-group>
  <button type="button"
          class="flex w-full items-center justify-between rounded-lg px-3 py-2 text-left hover:bg-slate-800 {{ $settingsOpen ? 'bg-slate-800 text-white' : '' }}"
          data-nav-toggle
          aria-expanded="{{ $settingsOpen ? 'true' : 'false' }}">
    <span>Settings</span>
    <svg class="h-4 w-4 shrink-0 text-slate-400 transition-transform duration-200 {{ $settingsOpen ? 'rotate-90' : '' }}" data-nav-chevron fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
    </svg>
  </button>
  <div class="ml-2 space-y-0.5 overflow-hidden border-l border-slate-700 pl-2 {{ $settingsOpen ? '' : 'hidden' }}" data-nav-panel>
    <a href="{{ route('admin.settings.hub') }}" class="block rounded-lg px-3 py-1.5 text-[13px] text-slate-300 hover:bg-slate-800 hover:text-white">Settings hub</a>
    <a href="{{ route('admin.settings.general') }}" class="block rounded-lg px-3 py-1.5 text-[13px] text-slate-300 hover:bg-slate-800 hover:text-white">General & homepage</a>
    <a href="{{ route('admin.settings.payments') }}" class="block rounded-lg px-3 py-1.5 text-[13px] text-slate-300 hover:bg-slate-800 hover:text-white">Payments</a>
    <a href="{{ route('admin.settings.smtp') }}" class="block rounded-lg px-3 py-1.5 text-[13px] text-slate-300 hover:bg-slate-800 hover:text-white">SMTP / Email</a>
    <a href="{{ route('admin.settings.storage') }}" class="block rounded-lg px-3 py-1.5 text-[13px] text-slate-300 hover:bg-slate-800 hover:text-white">File storage</a>
    <a href="{{ route('admin.settings.navigation') }}" class="block rounded-lg px-3 py-1.5 text-[13px] text-slate-300 hover:bg-slate-800 hover:text-white">Menus</a>
    <a href="{{ route('admin.settings.header_footer') }}" class="block rounded-lg px-3 py-1.5 text-[13px] text-slate-300 hover:bg-slate-800 hover:text-white">Header / footer</a>
    <a href="{{ route('admin.settings.ads') }}" class="block rounded-lg px-3 py-1.5 text-[13px] text-slate-300 hover:bg-slate-800 hover:text-white">Ad placements</a>
    <a href="{{ route('admin.settings.schema') }}" class="block rounded-lg px-3 py-1.5 text-[13px] text-slate-300 hover:bg-slate-800 hover:text-white">Schema SEO</a>
  </div>
</div>

<p class="px-3 pt-3 text-xs font-semibold uppercase tracking-wide text-slate-500">Maintenance</p>
<a href="{{ route('admin.tools.index') }}" class="block rounded-lg px-3 py-2 hover:bg-slate-800">System tools</a>
<a href="{{ route('home') }}" class="mt-2 block rounded-lg px-3 py-2 text-emerald-400 hover:bg-slate-800">← View storefront</a>
