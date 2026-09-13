@props(['actions' => []])
{{--
  actions: list of [
    'label' => string,
    'href' => optional URL (link),
    'route' => optional (unused if href set),
    'method' => 'GET'|'POST'|'DELETE' (default GET),
    'confirm' => optional confirm message,
    'danger' => bool,
    'target' => '_blank'|null,
  ]
--}}
<div class="admin-kebab relative inline-block text-left" data-kebab>
  <button type="button"
          class="admin-kebab-btn inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 shadow-sm hover:bg-slate-50 hover:text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500/40"
          data-kebab-toggle
          aria-haspopup="true"
          aria-expanded="false"
          title="Actions">
    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
      <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"/>
    </svg>
  </button>
  <div class="admin-kebab-menu absolute right-0 z-30 mt-1 hidden min-w-[11rem] origin-top-right rounded-xl border border-slate-200 bg-white py-1 shadow-lg ring-1 ring-black/5"
       data-kebab-menu role="menu">
    @foreach($actions as $action)
      @php
        $label = $action['label'] ?? 'Action';
        $href = $action['href'] ?? '#';
        $method = strtoupper($action['method'] ?? 'GET');
        $danger = !empty($action['danger']);
        $confirm = $action['confirm'] ?? null;
        $target = $action['target'] ?? null;
        $cls = $danger
          ? 'text-red-600 hover:bg-red-50'
          : 'text-slate-700 hover:bg-slate-50';
      @endphp
      @if($method === 'GET')
        <a href="{{ $href }}"
           @if($target) target="{{ $target }}" rel="noopener" @endif
           class="block px-3 py-2 text-sm {{ $cls }}"
           role="menuitem">{{ $label }}</a>
      @else
        <form method="post" action="{{ $href }}" @if($confirm) onsubmit="return confirm(@json($confirm))" @endif>
          @csrf
          @if(in_array($method, ['PUT','PATCH','DELETE'], true))
            @method($method)
          @endif
          <button type="submit" class="block w-full px-3 py-2 text-left text-sm {{ $cls }}" role="menuitem">{{ $label }}</button>
        </form>
      @endif
    @endforeach
  </div>
</div>
