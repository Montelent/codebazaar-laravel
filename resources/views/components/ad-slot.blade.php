{{-- Usage: <x-ad-slot slot="blog_post_before" /> --}}
@php
  $html = \App\Support\AdSlots::render($slot ?? '');
@endphp
@if($html !== '')
{!! $html !!}
@endif
