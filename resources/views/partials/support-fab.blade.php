@php
    $supportHref = auth()->check() ? url('/support') : url('/login');
@endphp
<a href="{{ $supportHref }}"
   class="cc-support-fab"
   style="position:fixed;bottom:20px;right:20px;z-index:9999;display:flex;align-items:center;justify-content:center;width:56px;height:56px;border-radius:9999px;background:#059669;color:#fff;box-shadow:0 8px 24px rgba(0,0,0,.2);text-decoration:none;"
   title="Support"
   aria-label="Support chat">
    <svg width="28" height="28" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
    </svg>
</a>
