@php $changelog = $item->changelogList(); @endphp
@if(count($changelog))
<button type="button" class="cc-tab border-b-2 border-transparent px-4 py-3 text-slate-500 hover:text-slate-800" data-tab="changelog">Changelog{{ $item->version ? ' · v'.$item->version : '' }}</button>
@endif
