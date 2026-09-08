@extends('layouts.app')
@php
  $seo = \App\Support\Seo::make($item->seoPayload());
  $breadcrumbs = [['name' => 'Home', 'url' => url('/')]];
  if ($item->category) {
      foreach ($item->category->breadcrumbTrail() as $c) {
          $breadcrumbs[] = ['name' => $c->name, 'url' => route('category', $c->slug)];
      }
  }
  $breadcrumbs[] = ['name' => $item->title, 'url' => route('item.show', [$item->slug, $item->id])];
@endphp
@section('content')
@php
  $gallery = is_array($item->gallery_urls) ? array_values(array_filter($item->gallery_urls)) : [];
  $features = is_array($item->features) ? $item->features : [];
  $attrs = is_array($item->attributes) ? $item->attributes : [];
  $tags = is_array($item->tags) ? $item->tags : [];
  $regular = $item->effectiveRegularPrice();
  $extended = $item->effectiveExtendedPrice();
  $isFree = $item->is_free || $regular <= 0;
  $previews = array_values(array_filter(array_merge(
    $item->thumbnail_url ? [$item->thumbnail_url] : [],
    $gallery
  )));
  $descriptionHtml = (string) ($item->description ?? '');
  if ($descriptionHtml !== '' && str_contains($descriptionHtml, '<')) {
      $once = html_entity_decode($descriptionHtml, ENT_QUOTES | ENT_HTML5, 'UTF-8');
      if (str_contains($once, '<') && ! str_contains($descriptionHtml, '<p') && ! str_contains($descriptionHtml, '<div')) {
          $descriptionHtml = $once;
      }
  }
  $descriptionHtml = \App\Support\AdSlots::injectAfterParagraphs($descriptionHtml, 'product_middle_description');
  $catTrail = $item->category ? $item->category->breadcrumbTrail() : [];
@endphp

{!! \App\Support\AdSlots::render('product_before') !!}

<nav class="mb-4 flex flex-wrap items-center gap-x-1 gap-y-1 text-[13px] text-slate-500" aria-label="Breadcrumb">
  <a href="{{ route('home') }}" class="hover:text-[#82b440]">Home</a>
  @foreach($catTrail as $crumb)
    <span class="text-slate-300">/</span>
    <a href="{{ route('category', $crumb->slug) }}" class="hover:text-[#82b440]">{{ $crumb->name }}</a>
  @endforeach
  <span class="text-slate-300">/</span>
  <span class="text-slate-700">{{ \Illuminate\Support\Str::limit($item->title, 56) }}</span>
</nav>

<div class="grid gap-8 lg:grid-cols-12">
  <div class="space-y-5 lg:col-span-8">
    <div>
      <h1 class="text-[1.55rem] font-bold tracking-tight text-slate-900 sm:text-[1.75rem]">{{ $item->title }}</h1>
      <div class="mt-2 flex flex-wrap items-center gap-x-3 gap-y-1 text-[13px] text-slate-500">
        @if($item->author)
          <span>by <a class="font-medium text-slate-800 hover:text-[#82b440]" href="{{ route('author.show', $item->author->username ?: $item->author->id) }}">{{ $item->author->name ?: $item->author->username }}</a></span>
        @endif
        @if($item->rating_count)
          <span class="text-amber-500">★ {{ number_format($item->rating_avg, 1) }}</span>
          <a href="#reviews" class="hover:text-[#82b440]">{{ $item->rating_count }} ratings</a>
        @endif
        @if($item->sales_count)<span>{{ number_format($item->sales_count) }} sales</span>@endif
      </div>
    </div>

    {!! \App\Support\AdSlots::render('product_after_title') !!}

    <div class="overflow-hidden rounded border border-slate-200 bg-white shadow-sm">
      <div class="relative bg-[#f7f8fa]">
        @if(count($previews))
          <img id="cc-main-preview" src="{{ $previews[0] }}" alt="{{ $item->title }}" class="mx-auto max-h-[460px] w-full object-contain">
        @else
          <div class="flex h-64 items-center justify-center text-slate-400">No preview image</div>
        @endif
        @if($item->demo_url)
          <a href="{{ $item->demo_url }}" target="_blank" rel="noopener" class="absolute bottom-3 right-3 rounded bg-slate-900/90 px-3 py-1.5 text-xs font-semibold text-white hover:bg-slate-900">Live Preview ↗</a>
        @endif
      </div>
      @if(count($previews) > 1)
        <div class="flex gap-2 overflow-x-auto border-t border-slate-100 bg-white p-3">
          @foreach($previews as $i => $img)
            <button type="button" class="cc-thumb shrink-0 overflow-hidden rounded border-2 {{ $i === 0 ? 'border-[#82b440]' : 'border-transparent' }}" data-src="{{ $img }}" onclick="document.getElementById('cc-main-preview').src=this.dataset.src;document.querySelectorAll('.cc-thumb').forEach(t=>t.classList.remove('border-[#82b440]'));this.classList.add('border-[#82b440]');">
              <img src="{{ $img }}" class="h-14 w-20 object-cover" alt="">
            </button>
          @endforeach
        </div>
      @endif
    </div>

    <div class="rounded border border-slate-200 bg-white shadow-sm" id="item-tabs">
      <div class="flex flex-wrap gap-0 border-b border-slate-200 text-[13px] font-semibold">
        <button type="button" class="cc-tab border-b-2 border-[#82b440] px-4 py-3 text-slate-900" data-tab="details">Item details</button>
        <button type="button" class="cc-tab border-b-2 border-transparent px-4 py-3 text-slate-500 hover:text-slate-800" data-tab="comments">Comments ({{ $reviews->count() }})</button>
        @if(count($attrs))
          <button type="button" class="cc-tab border-b-2 border-transparent px-4 py-3 text-slate-500 hover:text-slate-800" data-tab="attributes">Item attributes</button>
        @endif
      </div>

      <div class="cc-tab-panel p-5 sm:p-6" data-panel="details">
        {!! \App\Support\AdSlots::render('product_before_description') !!}
        <div class="item-body">{!! $descriptionHtml !!}</div>
        {!! \App\Support\AdSlots::render('product_after_description') !!}
        @if(count($features))
          <h3 class="mt-8 text-base font-semibold text-slate-900">Features</h3>
          <ul class="mt-3 grid gap-2 text-sm text-slate-700 sm:grid-cols-2">
            @foreach($features as $f)
              <li class="flex gap-2"><span class="text-[#82b440]">✓</span> <span>{{ $f }}</span></li>
            @endforeach
          </ul>
        @endif
        @if(count($tags))
          <div class="mt-6 flex flex-wrap gap-2">
            @foreach($tags as $t)
              <a href="{{ route('search', ['tag' => $t]) }}" class="rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-medium text-slate-600 hover:border-[#82b440] hover:text-[#82b440]">{{ $t }}</a>
            @endforeach
          </div>
        @endif
      </div>

      <div class="cc-tab-panel hidden p-5 sm:p-6" data-panel="comments" id="reviews">
        @auth
          <form method="post" action="{{ route('reviews.store', $item->id) }}" class="mb-6 space-y-3 rounded border border-slate-100 bg-slate-50 p-4">
            @csrf
            <p class="text-sm font-semibold">Your rating</p>
            <div class="flex flex-wrap gap-3 text-sm">
              @for($i=5;$i>=1;$i--)
                <label class="cursor-pointer"><input type="radio" name="rating" value="{{ $i }}" class="mr-1" @checked(old('rating', $myReview->rating ?? 5)==$i)> {{ $i }}★</label>
              @endfor
            </div>
            <textarea name="comment" rows="3" class="w-full rounded border border-slate-200 px-3 py-2 text-sm" placeholder="Share your experience…">{{ old('comment', $myReview->comment ?? '') }}</textarea>
            <button class="rounded bg-[#82b440] px-4 py-2 text-sm font-semibold text-white hover:bg-[#6f9a36]">{{ $myReview ? 'Update review' : 'Submit review' }}</button>
          </form>
        @else
          <p class="mb-4 text-sm text-slate-500"><a href="{{ route('login') }}" class="text-[#82b440]">Sign in</a> to leave a review.</p>
        @endauth
        <ul class="space-y-4">
          @forelse($reviews as $review)
            <li class="border-t border-slate-100 pt-4 first:border-0 first:pt-0">
              <div class="flex flex-wrap items-center justify-between gap-2">
                <p class="text-sm font-medium text-slate-800">{{ $review->user?->name ?: $review->user?->username ?: 'Buyer' }} <span class="ml-1 text-amber-500">{{ str_repeat('★', (int)$review->rating) }}{{ str_repeat('☆', 5-(int)$review->rating) }}</span></p>
                <span class="text-xs text-slate-400">{{ $review->created_at?->diffForHumans() }}</span>
              </div>
              @if($review->comment)<p class="mt-1 text-sm text-slate-600">{{ $review->comment }}</p>@endif
            </li>
          @empty
            <li class="text-sm text-slate-500">No reviews yet.</li>
          @endforelse
        </ul>
      </div>

      @if(count($attrs))
      <div class="cc-tab-panel hidden p-0" data-panel="attributes">
        <table class="w-full text-sm">
          <tbody>
          @foreach($attrs as $label => $vals)
            @php
              $labelStr = is_string($label) ? $label : 'Attribute';
              $valList = is_array($vals) ? $vals : [$vals];
              $valList = array_values(array_filter(array_map(fn ($v) => is_scalar($v) ? (string) $v : null, $valList)));
            @endphp
            <tr class="border-b border-slate-100 last:border-0">
              <th class="w-[38%] bg-slate-50 px-4 py-3 text-left align-top font-medium text-slate-600 sm:w-[32%]">{{ $labelStr }}</th>
              <td class="px-4 py-3 text-slate-800">
                @foreach($valList as $i => $v)
                  @if($i > 0)<span class="text-slate-300">, </span>@endif
                  <a href="{{ route('search', ['attr' => $labelStr, 'val' => $v]) }}" class="text-[#82b440] hover:underline">{{ $v }}</a>
                @endforeach
              </td>
            </tr>
          @endforeach
          </tbody>
        </table>
      </div>
      @endif
    </div>
  </div>

  <aside class="lg:col-span-4">
    <div class="cc-buy-box space-y-4">
      <div class="rounded border border-slate-200 bg-white p-5 shadow-sm">
        @if($isFree)
          <p class="text-3xl font-bold text-[#82b440]">Free</p>
        @else
          <div class="flex items-baseline gap-2">
            <p class="text-3xl font-bold text-slate-900">${{ number_format($regular, 2) }}</p>
          </div>
          <p class="mt-0.5 text-sm text-slate-500">Regular License</p>
        @endif
        <form method="post" action="{{ route('cart.add') }}" class="mt-4">
          @csrf
          <input type="hidden" name="item_id" value="{{ $item->id }}">
          <input type="hidden" name="license" value="regular">
          <button class="w-full rounded bg-[#82b440] py-3 text-sm font-bold text-white hover:bg-[#6f9a36]">{{ $isFree ? 'Download Free' : 'Add to Cart' }}</button>
        </form>
        @if(! $isFree && $extended > 0)
          <form method="post" action="{{ route('cart.add') }}" class="mt-2">
            @csrf
            <input type="hidden" name="item_id" value="{{ $item->id }}">
            <input type="hidden" name="license" value="extended">
            <button class="w-full rounded border border-slate-300 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Extended License — ${{ number_format($extended, 2) }}</button>
          </form>
        @endif
        @if($item->demo_url)
          <a href="{{ $item->demo_url }}" target="_blank" rel="noopener" class="mt-3 flex w-full items-center justify-center rounded border border-slate-200 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50">Live Preview</a>
        @endif
        <ul class="mt-4 space-y-1.5 border-t border-slate-100 pt-4 text-xs text-slate-500">
          <li class="flex justify-between"><span>Last update</span><span class="text-slate-700">{{ $item->updated_at?->format('M j, Y') }}</span></li>
          <li class="flex justify-between"><span>Published</span><span class="text-slate-700">{{ $item->created_at?->format('M j, Y') }}</span></li>
          @if($item->category)
            <li class="flex justify-between gap-2"><span>Category</span>
              <a href="{{ route('category', $item->category->slug) }}" class="text-right text-[#82b440] hover:underline">{{ $item->category->name }}</a>
            </li>
          @endif
        </ul>
      </div>
      {!! \App\Support\AdSlots::render('product_sidebar') !!}
    </div>
  </aside>
</div>

{!! \App\Support\AdSlots::render('product_after') !!}

@if($related->count())
<section class="mt-12 border-t border-slate-200 pt-10">
  <h2 class="text-lg font-bold text-slate-900">Related items</h2>
  <div class="cc-grid mt-4">
    @foreach($related as $r)
      @include('components.item-card', ['item' => $r])
    @endforeach
  </div>
</section>
@endif

@push('scripts')
<script>
(function () {
  var tabs = document.querySelectorAll('.cc-tab');
  var panels = document.querySelectorAll('.cc-tab-panel');
  tabs.forEach(function (tab) {
    tab.addEventListener('click', function () {
      tabs.forEach(function (t) {
        t.classList.remove('border-[#82b440]', 'text-slate-900');
        t.classList.add('border-transparent', 'text-slate-500');
      });
      tab.classList.add('border-[#82b440]', 'text-slate-900');
      tab.classList.remove('border-transparent', 'text-slate-500');
      panels.forEach(function (p) {
        p.classList.toggle('hidden', p.dataset.panel !== tab.dataset.tab);
      });
    });
  });
})();
</script>
@endpush
@endsection
