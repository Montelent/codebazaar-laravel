@extends('layouts.admin')
@section('title', 'Ad placements')
@section('content')
<a href="{{ route('admin.settings.hub') }}" class="text-sm text-emerald-700">← Settings</a>
<h1 class="mt-2 text-xl font-bold">Ad placements</h1>
<p class="mt-1 text-sm text-slate-500">Paste AdSense / banner / affiliate HTML into any slot. Leave disabled slots empty. Codes are output unescaped — only paste trusted scripts.</p>

<form method="post" action="{{ route('admin.settings.ads.update') }}" class="mt-6 space-y-6">
  @csrf
  @method('PUT')

  <div class="rounded-xl border bg-white p-5 shadow-sm space-y-3">
    <label class="flex items-center gap-2 text-sm font-medium">
      <input type="checkbox" name="enabled" value="1" @checked(!empty($config['enabled']))>
      Enable all ad placements sitewide
    </label>
    <div class="max-w-xs">
      <label class="text-xs font-medium text-slate-600">Middle insert after paragraph #</label>
      <input type="number" min="1" name="middle_paragraph" value="{{ (int)($config['middle_paragraph'] ?? 3) }}" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm">
      <p class="mt-1 text-xs text-slate-400">Used for blog “middle” and product “middle of description” slots.</p>
    </div>
  </div>

  @php $groups = collect($definitions)->groupBy('group'); @endphp
  @foreach($groups as $group => $defs)
    <section class="rounded-xl border bg-white p-5 shadow-sm space-y-4">
      <h2 class="font-semibold text-slate-900">{{ $group }}</h2>
      @foreach($defs as $key => $meta)
        @php $slot = $config['slots'][$key] ?? ['enabled' => false, 'html' => '']; @endphp
        <div class="rounded-lg border border-slate-100 p-4">
          <label class="flex items-start gap-2 text-sm font-medium">
            <input type="checkbox" name="slots[{{ $key }}][enabled]" value="1" class="mt-0.5" @checked(!empty($slot['enabled']))>
            <span>
              {{ $meta['label'] }}
              <span class="block text-xs font-normal text-slate-500">{{ $meta['help'] }} · <code class="text-[10px]">{{ $key }}</code></span>
            </span>
          </label>
          <textarea name="slots[{{ $key }}][html]" rows="3" class="mt-2 w-full rounded-lg border px-3 py-2 font-mono text-xs" placeholder="&lt;script&gt;…&lt;/script&gt; or HTML banner">{{ $slot['html'] ?? '' }}</textarea>
        </div>
      @endforeach
    </section>
  @endforeach

  <button class="rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white">Save ad placements</button>
</form>
@endsection
