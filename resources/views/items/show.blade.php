@extends('layouts.app')
@section('title', $item->title . ' · CodeBazaar')
@section('content')
<nav class="mb-3 text-xs text-slate-500">
    <a href="{{ route('home') }}" class="hover:text-emerald-600">Home</a> ›
    @if($item->category)
        <a href="{{ route('category', $item->category->slug) }}" class="hover:text-emerald-600">{{ $item->category->name }}</a> ›
    @endif
    <span class="text-slate-800">{{ $item->title }}</span>
</nav>
<div class="grid gap-8 lg:grid-cols-12">
    <div class="lg:col-span-8">
        <h1 class="text-2xl font-bold">{{ $item->title }}</h1>
        <p class="mt-1 text-sm text-slate-500">By {{ $item->author->name ?? $item->author->username ?? 'CodeBazaar' }} · {{ number_format($item->sales_count) }} sales</p>
        <div class="mt-4 overflow-hidden rounded-xl border bg-slate-100">
            @if($item->thumbnail_url)<img src="{{ $item->thumbnail_url }}" alt="" class="aspect-video w-full object-cover">@endif
        </div>
        <div class="prose prose-slate mt-6 max-w-none text-sm">{!! $item->description !!}</div>
        @if(is_array($item->features) && count($item->features))
            <ul class="mt-4 list-disc space-y-1 pl-5 text-sm">@foreach($item->features as $f)<li>{{ $f }}</li>@endforeach</ul>
        @endif
    </div>
    <aside class="lg:col-span-4">
        <div class="sticky top-6 rounded-xl border bg-white p-4 shadow-sm">
            @php $isFree = $item->is_free || $item->regular_price <= 0; @endphp
            <div class="text-2xl font-bold">{{ $isFree ? 'Free' : '$' . number_format($item->effectiveRegularPrice(), 2) }}</div>
            @if(!$isFree)<p class="text-xs text-slate-500">Extended: ${{ number_format($item->effectiveExtendedPrice(), 2) }}</p>@endif
            <form method="post" action="{{ route('cart.add') }}" class="mt-4 space-y-3">
                @csrf
                <input type="hidden" name="item_id" value="{{ $item->id }}">
                @if(!$isFree)
                    <select name="license_type" class="w-full rounded-lg border px-3 py-2 text-sm">
                        <option value="regular">Regular — ${{ number_format($item->effectiveRegularPrice(), 2) }}</option>
                        <option value="extended">Extended — ${{ number_format($item->effectiveExtendedPrice(), 2) }}</option>
                    </select>
                @else
                    <input type="hidden" name="license_type" value="regular">
                @endif
                <button class="w-full rounded-lg bg-emerald-600 py-3 font-semibold text-white hover:bg-emerald-700">{{ $isFree ? 'Get free download' : 'Add to Cart' }}</button>
            </form>
            @if($item->demo_url)<a href="{{ $item->demo_url }}" target="_blank" class="mt-3 block text-center text-sm text-emerald-700">Live Preview</a>@endif
            <dl class="mt-4 space-y-1 border-t pt-3 text-xs text-slate-600">
                <div class="flex justify-between"><dt>Created</dt><dd>{{ $item->created_at->format('j F Y') }}</dd></div>
                <div class="flex justify-between"><dt>Last update</dt><dd>{{ $item->updated_at->format('j F Y') }}</dd></div>
            </dl>
        </div>
    </aside>
</div>
@endsection
