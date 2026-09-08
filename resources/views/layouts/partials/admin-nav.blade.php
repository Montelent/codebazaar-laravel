@php
  $catalogOpen = request()->routeIs('admin.products.*')
      || request()->routeIs('admin.categories.*')
      || request()->routeIs('admin.attributes.*')
      || request()->routeIs('admin.tags.*')
      || request()->routeIs('admin.media.*')
      || request()->routeIs('admin.licenses.*');
  $contentOpen = request()->routeIs('admin.blog.*')
      || request()->routeIs('admin.pages.*')
      || request()->routeIs('admin.newsletter.*');
  $commerceOpen = request()->routeIs('admin.orders.*');
  $membersOpen = request()->routeIs('admin.users.*')
      || request()->routeIs('admin.members.*')
      || request()->routeIs('admin.staff.*');
  $settingsOpen = request()->routeIs('admin.settings.*');
  $maintOpen = request()->routeIs('admin.tools.*') || request()->routeIs('admin.migrate');
@endphp

<a href="{{ route('admin.dashboard') }}" class="block rounded-lg px-3 py-2 hover:bg-slate-800 {{ request()->routeIs('admin.dashboard') ? 'bg-slate-800 text-white' : '' }}">Dashboard</a>

{{-- Catalog --}}
<div class="admin-nav-group" data-nav-group>
  <button type="button" class="flex w-full items-center justify-between rounded-lg px-3 py-2 text-left hover:bg-slate-800 {{ $catalogOpen ? 'bg-slate-800 text-white' : '' }}" data-nav-toggle aria-expanded="{{ $catalogOpen ? 'true' : 'false' }}">
    <span>Catalog</span>
    <svg class="h-4 w-4 shrink-0 text-slate-400 transition-transform duration-200 {{ $catalogOpen ? 'rotate-90' : '' }}" data-nav-chevron fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
  </button>
  <div class="ml-2 space-y-0.5 overflow-hidden border-l border-slate-700 pl-2 {{ $catalogOpen ? '' : 'hidden' }}" data-nav-panel>
    <a href="{{ route('admin.products.index') }}" class="block rounded-lg px-3 py-1.5 text-[13px] text-slate-300 hover:bg-slate-800 hover:text-white">Products</a>
    <a href="{{ route('admin.products.create') }}" class="block rounded-lg px-3 py-1.5 text-[13px] text-slate-300 hover:bg-slate-800 hover:text-white">Add product</a>
    <a href="{{ route('admin.categories.index') }}" class="block rounded-lg px-3 py-1.5 text-[13px] text-slate-300 hover:bg-slate-800 hover:text-white">Categories</a>
    <a href="{{ route('admin.attributes.index') }}" class="block rounded-lg px-3 py-1.5 text-[13px] text-slate-300 hover:bg-slate-800 hover:text-white">Attributes</a>
    <a href="{{ route('admin.tags.index') }}" class="block rounded-lg px-3 py-1.5 text-[13px] text-slate-300 hover:bg-slate-800 hover:text-white">Tags</a>
    <a href="{{ route('admin.media.index') }}" class="block rounded-lg px-3 py-1.5 text-[13px] text-slate-300 hover:bg-slate-800 hover:text-white">Media library</a>
    <a href="{{ route('admin.licenses.index') }}" class="block rounded-lg px-3 py-1.5 text-[13px] text-slate-300 hover:bg-slate-800 hover:text-white">Licenses</a>
  </div>
</div>

{{-- Content --}}
<div class="admin-nav-group" data-nav-group>
  <button type="button" class="flex w-full items-center justify-between rounded-lg px-3 py-2 text-left hover:bg-slate-800 {{ $contentOpen ? 'bg-slate-800 text-white' : '' }}" data-nav-toggle aria-expanded="{{ $contentOpen ? 'true' : 'false' }}">
    <span>Content</span>
    <svg class="h-4 w-4 shrink-0 text-slate-400 transition-transform duration-200 {{ $contentOpen ? 'rotate-90' : '' }}" data-nav-chevron fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
  </button>
  <div class="ml-2 space-y-0.5 overflow-hidden border-l border-slate-700 pl-2 {{ $contentOpen ? '' : 'hidden' }}" data-nav-panel>
    <a href="{{ route('admin.blog.index') }}" class="block rounded-lg px-3 py-1.5 text-[13px] text-slate-300 hover:bg-slate-800 hover:text-white">Blog</a>
    <a href="{{ route('admin.pages.index') }}" class="block rounded-lg px-3 py-1.5 text-[13px] text-slate-300 hover:bg-slate-800 hover:text-white">Pages</a>
    <a href="{{ route('admin.newsletter.index') }}" class="block rounded-lg px-3 py-1.5 text-[13px] text-slate-300 hover:bg-slate-800 hover:text-white">Newsletter</a>
  </div>
</div>

{{-- Commerce --}}
<div class="admin-nav-group" data-nav-group>
  <button type="button" class="flex w-full items-center justify-between rounded-lg px-3 py-2 text-left hover:bg-slate-800 {{ $commerceOpen ? 'bg-slate-800 text-white' : '' }}" data-nav-toggle aria-expanded="{{ $commerceOpen ? 'true' : 'false' }}">
    <span>Commerce</span>
    <svg class="h-4 w-4 shrink-0 text-slate-400 transition-transform duration-200 {{ $commerceOpen ? 'rotate-90' : '' }}" data-nav-chevron fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
  </button>
  <div class="ml-2 space-y-0.5 overflow-hidden border-l border-slate-700 pl-2 {{ $commerceOpen ? '' : 'hidden' }}" data-nav-panel>
    <a href="{{ route('admin.orders.index') }}" class="block rounded-lg px-3 py-1.5 text-[13px] text-slate-300 hover:bg-slate-800 hover:text-white">Orders</a>
  </div>
</div>

{{-- Members --}}
<div class="admin-nav-group" data-nav-group>
  <button type="button" class="flex w-full items-center justify-between rounded-lg px-3 py-2 text-left hover:bg-slate-800 {{ $membersOpen ? 'bg-slate-800 text-white' : '' }}" data-nav-toggle aria-expanded="{{ $membersOpen ? 'true' : 'false' }}">
    <span>Members</span>
    <svg class="h-4 w-4 shrink-0 text-slate-400 transition-transform duration-200 {{ $membersOpen ? 'rotate-90' : '' }}" data-nav-chevron fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
  </button>
  <div class="ml-2 space-y-0.5 overflow-hidden border-l border-slate-700 pl-2 {{ $membersOpen ? '' : 'hidden' }}" data-nav-panel>
    <a href="{{ route('admin.members.index') }}" class="block rounded-lg px-3 py-1.5 text-[13px] text-slate-300 hover:bg-slate-800 hover:text-white">Users</a>
    <a href="{{ route('admin.staff.index') }}" class="block rounded-lg px-3 py-1.5 text-[13px] text-slate-300 hover:bg-slate-800 hover:text-white">Admins / Authors</a>
    <a href="{{ route('admin.users.create') }}" class="block rounded-lg px-3 py-1.5 text-[13px] text-slate-300 hover:bg-slate-800 hover:text-white">Add user</a>
  </div>
</div>

{{-- Settings --}}
<div class="admin-nav-group" data-nav-group>
  <button type="button" class="flex w-full items-center justify-between rounded-lg px-3 py-2 text-left hover:bg-slate-800 {{ $settingsOpen ? 'bg-slate-800 text-white' : '' }}" data-nav-toggle aria-expanded="{{ $settingsOpen ? 'true' : 'false' }}">
    <span>Settings</span>
    <svg class="h-4 w-4 shrink-0 text-slate-400 transition-transform duration-200 {{ $settingsOpen ? 'rotate-90' : '' }}" data-nav-chevron fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
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

{{-- Maintenance --}}
<div class="admin-nav-group" data-nav-group>
  <button type="button" class="flex w-full items-center justify-between rounded-lg px-3 py-2 text-left hover:bg-slate-800 {{ $maintOpen ? 'bg-slate-800 text-white' : '' }}" data-nav-toggle aria-expanded="{{ $maintOpen ? 'true' : 'false' }}">
    <span>Maintenance</span>
    <svg class="h-4 w-4 shrink-0 text-slate-400 transition-transform duration-200 {{ $maintOpen ? 'rotate-90' : '' }}" data-nav-chevron fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
  </button>
  <div class="ml-2 space-y-0.5 overflow-hidden border-l border-slate-700 pl-2 {{ $maintOpen ? '' : 'hidden' }}" data-nav-panel>
    <a href="{{ route('admin.tools.index') }}" class="block rounded-lg px-3 py-1.5 text-[13px] text-slate-300 hover:bg-slate-800 hover:text-white">System tools</a>
  </div>
</div>

<a href="{{ route('home') }}" class="mt-2 block rounded-lg px-3 py-2 text-emerald-400 hover:bg-slate-800">← View storefront</a>
