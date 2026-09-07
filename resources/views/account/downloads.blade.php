@extends('layouts.app')
@section('title', 'Downloads · CodeBazaar')
@section('content')
<h1 class="text-2xl font-bold">Downloads</h1>
<ul class="mt-6 space-y-4">
    @forelse($items as $oi)
        @php
            $product = $oi->item;
            $files = $product ? $product->downloadFilesList() : [];
        @endphp
        <li class="rounded-xl border bg-white p-4">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <span class="font-medium text-slate-900">{{ $oi->title }}</span>
                @if($product)
                    <a href="{{ route('item.show', [$product->slug, $product->id]) }}" class="text-sm text-[#82b440] hover:underline">View product</a>
                @endif
            </div>
            @if(count($files))
                <ul class="mt-3 space-y-2 border-t border-slate-100 pt-3">
                    @foreach($files as $i => $file)
                        <li class="flex flex-wrap items-center justify-between gap-2 text-sm">
                            <span class="text-slate-700">
                                <span class="rounded bg-slate-100 px-1.5 py-0.5 text-[10px] font-semibold uppercase text-slate-500">{{ $file['type'] }}</span>
                                {{ $file['label'] }}
                            </span>
                            <a href="{{ route('account.download', ['itemId' => $oi->item_id, 'file' => $i]) }}"
                               class="rounded-lg bg-emerald-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-emerald-700">
                                Download
                            </a>
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="mt-2 text-sm text-slate-500">No files available for this product.</p>
            @endif
        </li>
    @empty
        <p class="text-slate-500">No downloads yet.</p>
    @endforelse
</ul>
@endsection
