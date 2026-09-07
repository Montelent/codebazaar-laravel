@php
  $price = $item->is_free || $item->effectiveRegularPrice() <= 0
      ? 'Free'
      : '$'.number_format($item->effectiveRegularPrice(), 0);
  $attrs = is_array($item->attributes) ? $item->attributes : [];
  $tags = is_array($item->tags) ? $item->tags : [];
  $metaBits = [];
  foreach (array_slice($attrs, 0, 4, true) as $label => $vals) {
      $list = is_array($vals) ? $vals : [$vals];
      $first = collect($list)->filter(fn ($v) => is_scalar($v))->first();
      if ($first !== null) {
          $metaBits[] = (is_string($label) ? $label.': ' : '').$first;
      }
  }
@endphp
<article class="flex flex-col gap-4 border-b border-slate-200 bg-white p-4 transition hover:bg-slate-50/80 sm:flex-row sm:items-start sm:gap-5 sm:p-5">
  <a href="{{ route('item.show', [$item->slug, $item->id]) }}" class="block w-full shrink-0 overflow-hidden rounded border border-slate-200 bg-slate-100 sm:w-[200px] md:w-[220px]">
    <div class="aspect-[16/10] w-full">
      @if($item->thumbnail_url)
        <img src="{{ $item->thumbnail_url }}" alt="{{ $item->title }}" class="h-full w-full object-cover" loading="lazy">
      @else
        <div class="flex h-full items-center justify-center text-xs text-slate-400">No preview</div>
      @endif
    </div>
  </a>

  <div class="min-w-0 flex-1">
    <a href="{{ route('item.show', [$item->slug, $item->id]) }}" class="text-[15px] font-semibold leading-snug text-slate-900 hover:text-[var(--cc-green)] sm:text-base">
      {{ $item->title }}
    </a>
    <p class="mt-1 text-[12px] text-slate-500">
      @if($item->author)
        by <a href="{{ route('author.show', $item->author->username ?: $item->author->id) }}" class="font-medium text-slate-700 hover:text-[var(--cc-green)]">{{ $item->author->name ?: $item->author->username }}</a>
      @endif
      @if($item->category)
        <span class="text-slate-300">·</span>
        in <a href="{{ route('category', $item->category->slug) }}" class="hover:text-[var(--cc-green)]">{{ $item->category->name }}</a>
      @endif
    </p>

    @if(count($metaBits))
      <ul class="mt-2 space-y-0.5 text-[12px] text-slate-600">
        @foreach($metaBits as $bit)
          <li class="flex gap-1.5"><span class="text-slate-300">•</span> {{ $bit }}</li>
        @endforeach
      </ul>
    @elseif(count($tags))
      <div class="mt-2 flex flex-wrap gap-1.5">
        @foreach(array_slice($tags, 0, 5) as $t)
          <span class="rounded bg-slate-100 px-2 py-0.5 text-[11px] text-slate-600">{{ $t }}</span>
        @endforeach
      </div>
    @endif

    <div class="mt-3 flex flex-wrap items-center gap-x-4 gap-y-1 text-[12px] text-slate-500">
      @if($item->rating_count)
        <span class="text-amber-500">★ {{ number_format($item->rating_avg, 1) }} <span class="text-slate-400">({{ $item->rating_count }})</span></span>
      @endif
      <span>{{ number_format($item->sales_count) }} sales</span>
      <span>Updated {{ $item->updated_at?->format('d M y') }}</span>
      @if($item->demo_url)
        <a href="{{ $item->demo_url }}" target="_blank" rel="noopener" class="font-medium text-[var(--cc-green)] hover:underline">Live Preview</a>
      @endif
    </div>
  </div>

  <div class="flex shrink-0 flex-row items-center justify-between gap-3 sm:w-[100px] sm:flex-col sm:items-end sm:justify-start sm:pt-0.5">
    <p class="text-lg font-bold text-slate-900 sm:text-xl">{{ $price }}</p>
    <a href="{{ route('item.show', [$item->slug, $item->id]) }}" class="rounded border border-slate-200 bg-white px-3 py-1.5 text-[12px] font-semibold text-slate-700 hover:border-[var(--cc-green)] hover:text-[var(--cc-green)] sm:w-full sm:text-center">
      Details
    </a>
  </div>
</article>
