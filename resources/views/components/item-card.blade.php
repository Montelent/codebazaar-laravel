<article class="cc-card group overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-0.5 hover:border-emerald-300 hover:shadow-md">
    <a href="{{ route('item.show', [$item->slug, $item->id]) }}" class="block">
        <div class="cc-card-thumb relative aspect-[16/10] overflow-hidden bg-slate-100">
            @if($item->thumbnail_url)
                <img src="{{ $item->thumbnail_url }}" alt="{{ $item->title }}" class="h-full w-full object-cover transition duration-300 group-hover:scale-[1.03]">
            @endif
            @if($item->is_free || (float)$item->regular_price <= 0)
                <span class="absolute left-2 top-2 rounded bg-emerald-600 px-2 py-0.5 text-[10px] font-bold uppercase text-white">Free</span>
            @endif
        </div>
    </a>
    <div class="p-3 sm:p-3.5">
        <a href="{{ route('item.show', [$item->slug, $item->id]) }}" class="cc-card-title line-clamp-2 text-sm font-semibold leading-snug text-slate-900 hover:text-emerald-700">{{ $item->title }}</a>
        <div class="mt-2 flex items-center justify-between gap-2">
            <span class="text-base font-bold text-slate-900">
                @if($item->is_free || $item->regular_price <= 0) Free
                @else ${{ number_format($item->effectiveRegularPrice(), 2) }}
                @endif
            </span>
            <span class="text-[11px] text-slate-500">{{ number_format($item->sales_count) }} sales</span>
        </div>
        @if($item->rating_count)
          <p class="mt-1 text-[11px] text-amber-500">★ {{ number_format($item->rating_avg, 1) }} <span class="text-slate-400">({{ $item->rating_count }})</span></p>
        @endif
    </div>
</article>
