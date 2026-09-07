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
