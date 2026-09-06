@extends('layouts.app')
@section('title', $item->title)
@section('content')
@php
  $gallery = is_array($item->gallery_urls) ? $item->gallery_urls : [];
  $features = is_array($item->features) ? $item->features : [];
  $attrs = is_array($item->attributes) ? $item->attributes : [];
  $regular = $item->effectiveRegularPrice();
  $extended = $item->effectiveExtendedPrice();
@endphp
<div class="grid gap-8 lg:grid-cols-3">
  <div class="lg:col-span-2 space-y-6">
    <div class="overflow-hidden rounded-2xl border bg-white shadow-sm">
      @if($item->thumbnail_url)
        <img src="{{ $item->thumbnail_url }}" alt="" class="max-h-[420px] w-full object-cover">
      @else
        <div class="flex h-56 items-center justify-center bg-slate-100 text-slate-400">No preview</div>
      @endif
      @if(count($gallery))
        <div class="flex gap-2 overflow-x-auto border-t p-3">
          @foreach($gallery as $img)
            <a href="{{ $img }}" target="_blank" rel="noopener" class="shrink-0">
              <img src="{{ $img }}" class="h-16 w-24 rounded-lg object-cover ring-1 ring-slate-200" alt="">
            </a>
          @endforeach
        </div>
      @endif
    </div>

    <div class="rounded-2xl border bg-white p-6 shadow-sm">
      <h1 class="text-2xl font-bold text-slate-900">{{ $item->title }}</h1>
      <p class="mt-1 text-sm text-slate-500">
        @if($item->rating_count)
          <span class="text-amber-500">★ {{ number_format($item->rating_avg, 1) }}</span>
          <span class="text-slate-400">({{ $item->rating_count }} reviews)</span> ·
        @endif
        @if($item->author)
          by <a class="text-emerald-700 hover:underline" href="{{ route('author.show', $item->author->username ?: $item->author->id) }}">{{ $item->author->name ?: $item->author->username }}</a>
        @endif
        @if($item->category)
          · in <a class="text-emerald-700 hover:underline" href="{{ route('category', $item->category->slug) }}">{{ $item->category->name }}</a>
        @endif
      </p>
      <div class="prose prose-slate mt-6 max-w-none">{!! $item->description !!}</div>
      @if(count($features))
        <h2 class="mt-8 text-lg font-semibold">Features</h2>
        <ul class="mt-3 list-disc space-y-1 pl-5 text-sm text-slate-700">
          @foreach($features as $f)<li>{{ $f }}</li>@endforeach
        </ul>
      @endif
    </div>

    @if(count($attrs))
    <div class="rounded-2xl border bg-white p-6 shadow-sm">
      <h2 class="text-lg font-semibold">Item attributes</h2>
      <dl class="mt-4 divide-y text-sm">
        @foreach($attrs as $label => $vals)
          <div class="grid grid-cols-3 gap-2 py-2">
            <dt class="font-medium text-slate-600">{{ is_string($label) ? $label : 'Attribute' }}</dt>
            <dd class="col-span-2 text-slate-800">
              @if(is_array($vals)) {{ implode(', ', $vals) }} @else {{ $vals }} @endif
            </dd>
          </div>
        @endforeach
      </dl>
    </div>
    @endif

    {{-- Reviews --}}
    <div class="rounded-2xl border bg-white p-6 shadow-sm" id="reviews">
      <h2 class="text-lg font-semibold">Reviews ({{ $reviews->count() }})</h2>

      @auth
        <form method="post" action="{{ route('reviews.store', $item->id) }}" class="mt-4 space-y-3 rounded-xl bg-slate-50 p-4">
          @csrf
          <p class="text-sm font-medium">Your rating</p>
          <div class="flex gap-2">
            @for($i=5;$i>=1;$i--)
              <label class="cursor-pointer text-sm"><input type="radio" name="rating" value="{{ $i }}" class="mr-1" @checked(old('rating', $myReview->rating ?? 5)==$i)> {{ $i }}★</label>
            @endfor
          </div>
          <textarea name="comment" rows="3" class="w-full rounded-lg border px-3 py-2 text-sm" placeholder="What did you like?">{{ old('comment', $myReview->comment ?? '') }}</textarea>
          <button class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white">{{ $myReview ? 'Update review' : 'Submit review' }}</button>
        </form>
      @else
        <p class="mt-3 text-sm text-slate-500"><a href="{{ route('login') }}" class="text-emerald-700">Sign in</a> to leave a review.</p>
      @endauth

      <ul class="mt-6 space-y-4">
        @forelse($reviews as $review)
          <li class="border-t pt-4">
            <div class="flex items-center justify-between gap-2">
              <p class="text-sm font-medium">{{ $review->user?->name ?: $review->user?->username ?: 'Buyer' }}
                <span class="text-amber-500">{{ str_repeat('★', (int)$review->rating) }}{{ str_repeat('☆', 5-(int)$review->rating) }}</span>
              </p>
              <span class="text-xs text-slate-400">{{ $review->created_at?->diffForHumans() }}</span>
            </div>
            @if($review->comment)<p class="mt-1 text-sm text-slate-700">{{ $review->comment }}</p>@endif
            @auth
              @if(auth()->id()===$review->user_id || auth()->user()->isAdmin())
                <form method="post" action="{{ route('reviews.destroy', $review) }}" class="mt-1">@csrf @method('DELETE')
                  <button class="text-xs text-red-600">Delete</button>
                </form>
              @endif
            @endauth
          </li>
        @empty
          <li class="text-sm text-slate-500">No reviews yet. Be the first.</li>
        @endforelse
      </ul>
    </div>
  </div>

  <aside class="space-y-4 lg:sticky lg:top-24 lg:self-start">
    <div class="rounded-2xl border bg-white p-5 shadow-sm">
      @if($item->is_free || $regular <= 0)
        <p class="text-3xl font-bold text-emerald-700">Free</p>
      @else
        <p class="text-3xl font-bold">${{ number_format($regular, 2) }}</p>
        <p class="text-sm text-slate-500">Regular license</p>
        @if($extended > 0)
          <p class="mt-2 text-lg font-semibold text-slate-800">${{ number_format($extended, 2) }} <span class="text-sm font-normal text-slate-500">Extended</span></p>
        @endif
      @endif

      <form method="post" action="{{ route('cart.add') }}" class="mt-4 space-y-2">
        @csrf
        <input type="hidden" name="item_id" value="{{ $item->id }}">
        <input type="hidden" name="license" value="regular">
        <button class="w-full rounded-lg bg-emerald-600 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700">
          {{ $item->is_free || $regular <= 0 ? 'Get free' : 'Add to cart — Regular' }}
        </button>
      </form>
      @if(!($item->is_free || $regular <= 0) && $extended > 0)
      <form method="post" action="{{ route('cart.add') }}" class="mt-2">
        @csrf
        <input type="hidden" name="item_id" value="{{ $item->id }}">
        <input type="hidden" name="license" value="extended">
        <button class="w-full rounded-lg border border-slate-300 py-2.5 text-sm font-semibold hover:bg-slate-50">Add Extended — ${{ number_format($extended, 2) }}</button>
      </form>
      @endif

      @if($item->demo_url)
        <a href="{{ $item->demo_url }}" target="_blank" rel="noopener" class="mt-3 block text-center text-sm text-emerald-700 hover:underline">Live preview</a>
      @endif

      @auth
      <form method="post" action="{{ route('wishlist.toggle') }}" class="mt-3">
        @csrf
        <input type="hidden" name="item_id" value="{{ $item->id }}">
        <button class="w-full text-sm text-slate-500 hover:text-emerald-700">♥ Wishlist</button>
      </form>
      @endauth
    </div>

    <div class="rounded-2xl border bg-slate-50 p-4 text-xs text-slate-600">
      <p>Created: {{ $item->created_at?->format('M j, Y') }}</p>
      <p class="mt-1">Last update: {{ $item->updated_at?->format('M j, Y') }}</p>
      @if($item->sales_count)<p class="mt-1">Sales: {{ $item->sales_count }}</p>@endif
    </div>
  </aside>
</div>

@if($related->count())
<section class="mt-12">
  <h2 class="text-lg font-semibold">Related items</h2>
  <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
    @foreach($related as $r)
      @include('components.item-card', ['item' => $r])
    @endforeach
  </div>
</section>
@endif
@endsection
