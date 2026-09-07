@php
  $sort = $sort ?? request('sort', 'newest');
  $priceMin = $priceMin ?? request('price_min');
  $priceMax = $priceMax ?? request('price_max');
  $view = $view ?? request('view', 'list');
  $action = $action ?? url()->current();
  $cats = $sidebarCategories ?? collect();
@endphp
<aside class="space-y-5">
  <form method="get" action="{{ $action }}" class="space-y-5">
    @foreach(request()->except(['price_min','price_max','sort','page','view']) as $k => $v)
      @if(is_scalar($v) && $v !== '')
        <input type="hidden" name="{{ $k }}" value="{{ $v }}">
      @endif
    @endforeach
    <input type="hidden" name="view" value="{{ $view }}">

    <div class="rounded border border-slate-200 bg-white">
      <div class="border-b border-slate-100 px-4 py-3 text-[13px] font-semibold text-slate-900">Filter & Refine</div>
      <div class="space-y-4 p-4">
        @if($cats->count())
          <div>
            <p class="text-[12px] font-semibold uppercase tracking-wide text-slate-500">Category</p>
            <ul class="mt-2 max-h-56 space-y-1 overflow-y-auto text-[13px]">
              @foreach($cats as $c)
                <li>
                  <a href="{{ isset($category) && ($category->id === $c->parent_id || $category->id === $c->id)
                        ? route('category', $category->slug).'?sub='.$c->slug
                        : route('category', $c->slug) }}"
                     class="flex items-center justify-between gap-2 rounded px-1 py-1 hover:bg-slate-50 {{ (isset($sub) && $sub === $c->slug) || (isset($category) && $category->id === $c->id) ? 'font-semibold text-[var(--cc-green)]' : 'text-slate-700' }}">
                    <span class="truncate">{{ $c->name }}</span>
                    <span class="shrink-0 text-[11px] text-slate-400">{{ $c->items_count ?? '' }}</span>
                  </a>
                </li>
              @endforeach
            </ul>
          </div>
        @endif

        <div>
          <p class="text-[12px] font-semibold uppercase tracking-wide text-slate-500">Price ($)</p>
          <div class="mt-2 flex items-center gap-2">
            <input type="number" name="price_min" value="{{ $priceMin }}" placeholder="Min" min="0" step="1"
                   class="w-full rounded border border-slate-200 px-2 py-1.5 text-[13px]">
            <span class="text-slate-400">–</span>
            <input type="number" name="price_max" value="{{ $priceMax }}" placeholder="Max" min="0" step="1"
                   class="w-full rounded border border-slate-200 px-2 py-1.5 text-[13px]">
          </div>
        </div>

        <div>
          <p class="text-[12px] font-semibold uppercase tracking-wide text-slate-500">Sort by</p>
          <select name="sort" class="mt-2 w-full rounded border border-slate-200 px-2 py-1.5 text-[13px]">
            <option value="newest" @selected($sort==='newest')>Newest</option>
            <option value="popular" @selected($sort==='popular')>Best sellers</option>
            <option value="rating" @selected($sort==='rating')>Best rated</option>
            <option value="price_asc" @selected($sort==='price_asc')>Price: low to high</option>
            <option value="price_desc" @selected($sort==='price_desc')>Price: high to low</option>
            <option value="title" @selected($sort==='title')>Title A–Z</option>
          </select>
        </div>

        <button type="submit" class="w-full rounded bg-[var(--cc-green)] py-2 text-[13px] font-semibold text-white hover:opacity-90">
          Apply filters
        </button>
        <a href="{{ $action }}" class="block text-center text-[12px] text-slate-500 hover:text-slate-800">Clear all</a>
      </div>
    </div>
  </form>
</aside>
