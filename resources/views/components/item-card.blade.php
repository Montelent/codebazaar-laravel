<article class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm transition hover:shadow-md">
    <a href="{{ route('item.show', [$item->slug, $item->id]) }}">
        <div class="aspect-[16/10] bg-slate-100">
            @if($item->thumbnail_url)
                <img src="{{ $item->thumbnail_url }}" alt="{{ $item->title }}" class="h-full w-full object-cover">
            @endif
        </div>
    </a>
    <div class="p-3">
        <a href="{{ route('item.show', [$item->slug, $item->id]) }}" class="line-clamp-2 text-sm font-semibold hover:text-emerald-700">{{ $item->title }}</a>
        <div class="mt-2 flex items-center justify-between">
            <span class="font-bold">
                @if($item->is_free || $item->regular_price <= 0) Free
                @else ${{ number_format($item->effectiveRegularPrice(), 2) }}
                @endif
            </span>
            <span class="text-xs text-slate-500">{{ number_format($item->sales_count) }} sales</span>
        </div>
    </div>
</article>
